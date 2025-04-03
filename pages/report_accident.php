<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("location: login.php");
    exit();
}

include('../includes/header.php');
include('../includes/db_connection.php');

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Start transaction for consistent database state
    $conn->begin_transaction();
    
    try {
        $user_id = $_SESSION['user_id'];
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        $accident_date = mysqli_real_escape_string($conn, $_POST['accident_date']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $company_id = mysqli_real_escape_string($conn, $_POST['company_id']);
        
        // Handle photo upload
        $photo_path = '';
        $upload_dir = '../uploads/accidents/';
        
        // Check if file was uploaded
                $document_path = '';
                if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
                    $upload_dir = '../uploads/accidents/';

                    $filename = time() . '_' . basename($_FILES['document']['name']);
                    $target_file = $upload_dir . $filename;

                    // Check file type
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
                    $file_type = $_FILES['document']['type'];

                    if (!in_array($file_type, $allowed_types)) {
                        throw new Exception("Invalid file type. Allowed types: JPEG, PNG, GIF, PDF.");
                    }

                    if (move_uploaded_file($_FILES['document']['tmp_name'], $target_file)) {
                        $document_path = $target_file;
                    } else {
                        throw new Exception("Error uploading your document. Error code: " . $_FILES['document']['error']);
                    }
                } else {
                    throw new Exception("Document upload is required. Error code: " . ($_FILES['document']['error'] ?? 'No file uploaded'));
                }
        
        // Insert accident record
        $sql = "INSERT INTO accidents (user_id, location, accident_date, description, photo_path) 
                VALUES ('$user_id', '$location', '$accident_date', '$description', '$photo_path')";
        
        if ($conn->query($sql) !== TRUE) {
            throw new Exception("Error inserting accident record: " . $conn->error);
        }
        
        $accident_id = $conn->insert_id;
        
        // Create claim record
        $claim_sql = "INSERT INTO claims (accident_id, company_id, status) 
                    VALUES ('$accident_id', '$company_id', 'new')";
        
        if ($conn->query($claim_sql) !== TRUE) {
            throw new Exception("Error creating claim: " . $conn->error);
        }
        
        // If everything succeeded, commit the transaction
        $conn->commit();
        $success = "Accident reported successfully!";
        
    } catch (Exception $e) {
        // Rollback the transaction on any error
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Get insurance companies
$companies_sql = "SELECT * FROM insurance_companies WHERE is_active = 1";
$companies_result = $conn->query($companies_sql);
?>

<div class="flex min-h-screen bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold mb-4">Report an Accident</h1>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success; ?>
                    <p><a href="user_dashboard.php" class="text-blue-500 underline">Return to dashboard</a></p>
                </div>
            <?php else: ?>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="location">
                            Location
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                            id="location" name="location" type="text" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="accident_date">
                            Date of Accident
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                            id="accident_date" name="accident_date" type="date" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                            Description
                        </label>
                        <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                                id="description" name="description" rows="4" required></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="document">
                            ID Document
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="document" name="document" type="file" accept="image/*,.pdf" required>
                        <p class="text-sm text-gray-500 mt-1">Please upload a valid ID proof (image or PDF).</p>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="company_id">
                            Select Insurance Company
                        </label>
                        <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                                id="company_id" name="company_id" required>
                            <?php while($company = $companies_result->fetch_assoc()): ?>
                                <option value="<?php echo $company['id']; ?>">
                                    <?php echo htmlspecialchars($company['company_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
                                type="submit">
                            Submit Report
                        </button>
                        <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="user_dashboard.php">
                            Cancel
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>