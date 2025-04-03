<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("location: login.php");
    exit();
}

include('../includes/header.php');
include('../includes/db_connection.php');

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->begin_transaction();
    try {
        $user_id = $_SESSION['user_id'];
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
        $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $accident_date = mysqli_real_escape_string($conn, $_POST['accident_date']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $company_id = mysqli_real_escape_string($conn, $_POST['company_id']);
        
        $sql = "INSERT INTO accidents (user_id, location, latitude, longitude, address, accident_date, description) 
                VALUES ('$user_id', '$location', '$latitude', '$longitude', '$address', '$accident_date', '$description')";
        if ($conn->query($sql) !== TRUE) {
            throw new Exception("Error inserting accident record: " . $conn->error);
        }

        $accident_id = $conn->insert_id;
        
        $claim_sql = "INSERT INTO claims (accident_id, company_id, status) VALUES ('$accident_id', '$company_id', 'new')";
        if ($conn->query($claim_sql) !== TRUE) {
            throw new Exception("Error creating claim: " . $conn->error);
        }

        $conn->commit();
        $success = "Accident reported successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

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
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-4">
                        <button type="button" onclick="fetchLocation()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Fetch My Location</button>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Location</label>
                        <input id="location" name="location" type="text" class="w-full p-2 border rounded" required>
                    </div>
                    <div class="flex space-x-4">
                        <div class="w-1/2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Latitude</label>
                            <input id="latitude" name="latitude" type="text" class="w-full p-2 border rounded" readonly>
                        </div>
                        <div class="w-1/2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Longitude</label>
                            <input id="longitude" name="longitude" type="text" class="w-full p-2 border rounded" readonly>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Address</label>
                        <input id="address" name="address" type="text" class="w-full p-2 border rounded" readonly>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Date of Accident</label>
                        <input id="accident_date" name="accident_date" type="date" class="w-full p-2 border rounded" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                        <textarea id="description" name="description" rows="4" class="w-full p-2 border rounded" required></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Select Insurance Company</label>
                        <select id="company_id" name="company_id" class="w-full p-2 border rounded" required>
                            <?php while($company = $companies_result->fetch_assoc()): ?>
                                <option value="<?php echo $company['id']; ?>">
                                    <?php echo htmlspecialchars($company['company_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Submit Report</button>
                        <a href="user_dashboard.php" class="text-blue-500 underline">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
async function fetchLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(async (position) => {
            let lat = position.coords.latitude;
            let lon = position.coords.longitude;
            document.getElementById("latitude").value = lat;
            document.getElementById("longitude").value = lon;

            let response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
            let data = await response.json();
            
            if (data && data.display_name) {
                document.getElementById("location").value = data.display_name;
                document.getElementById("address").value = data.display_name;
            } else {
                document.getElementById("location").value = "Location not found";
            }
        }, () => alert("Unable to retrieve location."));
    } else {
        alert("Geolocation is not supported by your browser.");
    }
}
</script>

<?php include('../includes/footer.php'); ?>
