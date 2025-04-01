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
        $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
        $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
        $accident_date = mysqli_real_escape_string($conn, $_POST['accident_date']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $company_id = mysqli_real_escape_string($conn, $_POST['company_id']);
        
        // Insert accident record
        $sql = "INSERT INTO accidents (user_id, location, latitude, longitude, accident_date, description, status)
                VALUES ('$user_id', '$location', '$latitude', '$longitude', '$accident_date', '$description', 'draft')";
        if ($conn->query($sql) !== TRUE) {
            throw new Exception("Error inserting accident record: " . $conn->error);
        }
        $accident_id = $conn->insert_id;
        
        // Insert vehicle A information
        $vehicle_a_make = mysqli_real_escape_string($conn, $_POST['vehicle_a_make']);
        $vehicle_a_model = mysqli_real_escape_string($conn, $_POST['vehicle_a_model']);
        $vehicle_a_year = mysqli_real_escape_string($conn, $_POST['vehicle_a_year']);
        $vehicle_a_color = mysqli_real_escape_string($conn, $_POST['vehicle_a_color']);
        $vehicle_a_license = mysqli_real_escape_string($conn, $_POST['vehicle_a_license']);
        $vehicle_a_driver = mysqli_real_escape_string($conn, $_POST['vehicle_a_driver']);
        $vehicle_a_dl = mysqli_real_escape_string($conn, $_POST['vehicle_a_dl']);
        $vehicle_a_insurance = mysqli_real_escape_string($conn, $_POST['vehicle_a_insurance']);
        $vehicle_a_policy = mysqli_real_escape_string($conn, $_POST['vehicle_a_policy']);
        $vehicle_a_damage = mysqli_real_escape_string($conn, $_POST['vehicle_a_damage']);
        
        $vehicle_a_sql = "INSERT INTO vehicles (accident_id, vehicle_position, make, model, year, color, license_plate, 
                          driver_name, driver_license, insurance_company, policy_number, damage_description)
                          VALUES ('$accident_id', 'A', '$vehicle_a_make', '$vehicle_a_model', '$vehicle_a_year', 
                          '$vehicle_a_color', '$vehicle_a_license', '$vehicle_a_driver', '$vehicle_a_dl', 
                          '$vehicle_a_insurance', '$vehicle_a_policy', '$vehicle_a_damage')";
        if ($conn->query($vehicle_a_sql) !== TRUE) {
            throw new Exception("Error inserting vehicle A information: " . $conn->error);
        }
        
        // Insert vehicle B information
        $vehicle_b_make = mysqli_real_escape_string($conn, $_POST['vehicle_b_make']);
        $vehicle_b_model = mysqli_real_escape_string($conn, $_POST['vehicle_b_model']);
        $vehicle_b_year = mysqli_real_escape_string($conn, $_POST['vehicle_b_year']);
        $vehicle_b_color = mysqli_real_escape_string($conn, $_POST['vehicle_b_color']);
        $vehicle_b_license = mysqli_real_escape_string($conn, $_POST['vehicle_b_license']);
        $vehicle_b_driver = mysqli_real_escape_string($conn, $_POST['vehicle_b_driver']);
        $vehicle_b_dl = mysqli_real_escape_string($conn, $_POST['vehicle_b_dl']);
        $vehicle_b_insurance = mysqli_real_escape_string($conn, $_POST['vehicle_b_insurance']);
        $vehicle_b_policy = mysqli_real_escape_string($conn, $_POST['vehicle_b_policy']);
        $vehicle_b_damage = mysqli_real_escape_string($conn, $_POST['vehicle_b_damage']);
        
        $vehicle_b_sql = "INSERT INTO vehicles (accident_id, vehicle_position, make, model, year, color, license_plate, 
                          driver_name, driver_license, insurance_company, policy_number, damage_description)
                          VALUES ('$accident_id', 'B', '$vehicle_b_make', '$vehicle_b_model', '$vehicle_b_year', 
                          '$vehicle_b_color', '$vehicle_b_license', '$vehicle_b_driver', '$vehicle_b_dl', 
                          '$vehicle_b_insurance', '$vehicle_b_policy', '$vehicle_b_damage')";
        if ($conn->query($vehicle_b_sql) !== TRUE) {
            throw new Exception("Error inserting vehicle B information: " . $conn->error);
        }
        
        // Handle evidence file upload
        if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] == 0) {
            $upload_dir = '../uploads/evidence/';
            $filename = time() . '_' . basename($_FILES['evidence_file']['name']);
            $target_file = $upload_dir . $filename;
            
            // Check file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            $file_type = $_FILES['evidence_file']['type'];
            $file_size = $_FILES['evidence_file']['size'];
            
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Invalid file type. Allowed types: JPEG, PNG, GIF, PDF.");
            }
            
            if (move_uploaded_file($_FILES['evidence_file']['tmp_name'], $target_file)) {
                $file_name = basename($_FILES['evidence_file']['name']);
                
                // Insert into evidence_files table
                $evidence_sql = "INSERT INTO evidence_files (accident_id, user_id, file_name, file_path, file_type, file_size)
                                VALUES ('$accident_id', '$user_id', '$file_name', '$target_file', '$file_type', '$file_size')";
                if ($conn->query($evidence_sql) !== TRUE) {
                    throw new Exception("Error saving evidence file record: " . $conn->error);
                }
            } else {
                throw new Exception("Error uploading your evidence file. Error code: " . $_FILES['evidence_file']['error']);
            }
        }
        
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
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-4xl mx-auto">
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
                    <div class="mb-6 border-b pb-4">
                        <h2 class="text-xl font-semibold mb-3">Accident Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="location">
                                    Location
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="location" name="location" type="text" required>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="latitude">
                                        Latitude
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                        id="latitude" name="latitude" type="text" placeholder="Optional">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="longitude">
                                        Longitude
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                        id="longitude" name="longitude" type="text" placeholder="Optional">
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="accident_date">
                                Date and Time of Accident
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="accident_date" name="accident_date" type="datetime-local" required>
                        </div>
                        <div class="mt-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                                Description of Accident
                            </label>
                            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="description" name="description" rows="4" required></textarea>
                        </div>
                    </div>
                    
                    <div class="mb-6 border-b pb-4">
                        <h2 class="text-xl font-semibold mb-3">Vehicle A Information (Your Vehicle)</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_a_make">
                                    Make
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_a_make" name="vehicle_a_make" type="text" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_a_model">
                                    Model
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_a_model" name="vehicle_a_model" type="text" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_a_year">
                                    Year
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_a_year" name="vehicle_a_year" type="number" min="1900" max="<?php echo date('Y'); ?>" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_a_color">
                                    Color
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_a_color" name="vehicle_a_color" type="text" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_a_license">
                                    License Plate
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_a_license" name="vehicle_a_license" type="text" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_a_driver">
                                    Driver Name
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_a_driver" name="vehicle_a_driver" type="text" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_a_dl">
                                    Driver License Number
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_a_dl" name="vehicle_a_dl" type="text">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_a_insurance">
                                    Insurance Company
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_a_insurance" name="vehicle_a_insurance" type="text" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_a_policy">
                                    Policy Number
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_a_policy" name="vehicle_a_policy" type="text" required>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_a_damage">
                                Damage Description
                            </label>
                            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="vehicle_a_damage" name="vehicle_a_damage" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="mb-6 border-b pb-4">
                        <h2 class="text-xl font-semibold mb-3">Vehicle B Information (Other Vehicle)</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_b_make">
                                    Make
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_b_make" name="vehicle_b_make" type="text" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_b_model">
                                    Model
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_b_model" name="vehicle_b_model" type="text" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_b_year">
                                    Year
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_b_year" name="vehicle_b_year" type="number" min="1900" max="<?php echo date('Y'); ?>" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_b_color">
                                    Color
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_b_color" name="vehicle_b_color" type="text" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_b_license">
                                    License Plate
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_b_license" name="vehicle_b_license" type="text" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_b_driver">
                                    Driver Name
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_b_driver" name="vehicle_b_driver" type="text" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_b_dl">
                                    Driver License Number
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_b_dl" name="vehicle_b_dl" type="text">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_b_insurance">
                                    Insurance Company
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_b_insurance" name="vehicle_b_insurance" type="text" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_b_policy">
                                    Policy Number
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="vehicle_b_policy" name="vehicle_b_policy" type="text" required>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_b_damage">
                                Damage Description
                            </label>
                            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="vehicle_b_damage" name="vehicle_b_damage" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="mb-6 border-b pb-4">
                        <h2 class="text-xl font-semibold mb-3">Evidence Upload</h2>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="evidence_file">
                                Upload Evidence
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="evidence_file" name="evidence_file" type="file" accept="image/*,.pdf" required>
                            <p class="text-sm text-gray-500 mt-1">Upload photos of the accident, damage, or relevant documents (JPEG, PNG, GIF, PDF).</p>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="company_id">
                            Select Insurance Company to File Claim With
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
                            Submit Accident Report
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