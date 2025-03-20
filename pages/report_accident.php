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
    $user_id = $_SESSION['user_id'];
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $accident_date = mysqli_real_escape_string($conn, $_POST['accident_date']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $provider_id = mysqli_real_escape_string($conn, $_POST['provider_id']);
    
    // Handle file upload
    $photo_path = '';
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed = array('jpg', 'jpeg', 'png');
        $filename = $_FILES['photo']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if(in_array(strtolower($filetype), $allowed)) {
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = '../uploads/' . $new_filename;
            
            if(move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                $photo_path = $new_filename;
            } else {
                $error = "Error uploading file";
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, and PNG files are allowed.";
        }
    }
    
    if(empty($error)) {
        // Insert accident record
        $sql = "INSERT INTO accidents (user_id, location, accident_date, description, photo_path) 
                VALUES ('$user_id', '$location', '$accident_date', '$description', '$photo_path')";
        
        if ($conn->query($sql) === TRUE) {
            $accident_id = $conn->insert_id;
            
            // Create claim record
            $claim_sql = "INSERT INTO claims (accident_id, provider_id, status) 
                          VALUES ('$accident_id', '$provider_id', 'new')";
            
            if ($conn->query($claim_sql) === TRUE) {
                $success = "Accident reported successfully!";
            } else {
                $error = "Error creating claim: " . $conn->error;
            }
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Get insurance providers
$providers_sql = "SELECT * FROM insurance_providers WHERE is_active = 1";
$providers_result = $conn->query($providers_sql);
?>

<div class="flex min-h-screen bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold mb-4">Report an Accident</h1>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
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
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="photo">
                            Photo (Optional)
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                               id="photo" name="photo" type="file" accept="image/*">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="provider_id">
                            Select Insurance Provider
                        </label>
                        <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                                id="provider_id" name="provider_id" required>
                            <?php while($provider = $providers_result->fetch_assoc()): ?>
                                <option value="<?php echo $provider['id']; ?>">
                                    <?php echo htmlspecialchars($provider['company_name']); ?>
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