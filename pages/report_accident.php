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

<div class="flex min-h-screen bg-gradient-to-b from-purple-50 to-purple-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-2xl shadow-xl p-6 max-w-2xl mx-auto border-t-4 border-purple-600 transition-all hover:shadow-2xl">
            <h1 class="text-3xl font-bold mb-4 text-purple-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Report an Accident
            </h1>
            
            <!-- Error Alert -->
            <div id="errorAlert" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                <!-- Error message will be inserted here -->
            </div>
            
            <!-- Success Alert -->
            <div id="successAlert" class="hidden bg-purple-100 border border-purple-400 text-purple-700 px-4 py-3 rounded-lg mb-4">
                <p class="font-medium">Report submitted successfully!</p>
                <p class="mt-2"><a href="user_dashboard.php" class="text-purple-700 font-bold underline hover:text-purple-900 transition-colors">Return to dashboard</a></p>
            </div>
            
            <!-- Form -->
            <form id="accidentForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
                <!-- Location Section -->
                <div class="p-4 bg-purple-50 rounded-lg border border-purple-100">
                    <h2 class="text-xl font-semibold mb-4 text-purple-800">Location Details</h2>
                    
                    <div class="mb-4">
                        <button type="button" onclick="fetchLocation()" class="w-full md:w-auto bg-purple-600 hover:bg-purple-700 focus:ring-4 focus:ring-purple-300 text-white font-medium py-2.5 px-5 rounded-lg transition-all transform hover:scale-105 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Get My Location
                        </button>
                        <div id="location-loading" class="hidden mt-2 text-center text-purple-600">
                            <div class="inline-block animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-purple-600"></div>
                            <span class="ml-2">Fetching your location...</span>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-purple-700 text-sm font-bold mb-2" for="location">Location</label>
                        <input id="location" name="location" type="text" class="w-full p-3 border border-purple-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" required placeholder="Your current location">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-purple-700 text-sm font-bold mb-2" for="latitude">Latitude</label>
                            <input id="latitude" name="latitude" type="text" class="w-full p-3 border border-purple-200 rounded-lg bg-gray-50" readonly>
                        </div>
                        <div>
                            <label class="block text-purple-700 text-sm font-bold mb-2" for="longitude">Longitude</label>
                            <input id="longitude" name="longitude" type="text" class="w-full p-3 border border-purple-200 rounded-lg bg-gray-50" readonly>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="block text-purple-700 text-sm font-bold mb-2" for="address">Address</label>
                        <input id="address" name="address" type="text" class="w-full p-3 border border-purple-200 rounded-lg bg-gray-50" readonly>
                    </div>
                </div>
                
                <!-- Accident Details Section -->
                <div class="p-4 bg-purple-50 rounded-lg border border-purple-100">
                    <h2 class="text-xl font-semibold mb-4 text-purple-800">Accident Details</h2>
                    
                    <div class="mb-4">
                        <label class="block text-purple-700 text-sm font-bold mb-2" for="accident_date">Date of Accident</label>
                        <div class="relative">
                            <input id="accident_date" name="accident_date" type="date" class="w-full p-3 border border-purple-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" required>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-purple-700 text-sm font-bold mb-2" for="description">Description</label>
                        <textarea id="description" name="description" rows="4" class="w-full p-3 border border-purple-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none" required placeholder="Please provide details about the accident..."></textarea>
                        <p class="text-xs text-purple-600 mt-1">Please include all relevant details about the accident</p>
                    </div>
                    
                    <div>
                        <label class="block text-purple-700 text-sm font-bold mb-2" for="company_id">Select Insurance Company</label>
                        <div class="relative">
                            <select id="company_id" name="company_id" class="w-full p-3 border border-purple-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 appearance-none" required>
                                <?php while($company = $companies_result->fetch_assoc()): ?>
                                    <option value="<?php echo $company['id']; ?>">
                                        <?php echo htmlspecialchars($company['company_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-purple-700">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <button type="submit" class="w-full md:w-auto bg-purple-600 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 text-white font-bold py-3 px-6 rounded-lg transition-all transform hover:scale-105 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Submit Report
                    </button>
                    <a href="user_dashboard.php" class="w-full md:w-auto text-center py-3 px-6 bg-gray-100 hover:bg-gray-200 text-purple-700 font-medium rounded-lg transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
        <p class="text-center text-purple-500 text-sm mt-6">
            Having trouble? Contact support at support@example.com
        </p>
    </div>
</div>

<script>
// Enhanced location fetching function
async function fetchLocation() {
    const loadingIndicator = document.getElementById('location-loading');
    const locationInput = document.getElementById('location');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const addressInput = document.getElementById('address');
    const errorAlert = document.getElementById('errorAlert');
    
    // Show loading indicator
    loadingIndicator.classList.remove('hidden');
    
    if (navigator.geolocation) {
        try {
            navigator.geolocation.getCurrentPosition(async (position) => {
                let lat = position.coords.latitude;
                let lon = position.coords.longitude;
                
                // Set latitude and longitude values
                latitudeInput.value = lat.toFixed(6);
                longitudeInput.value = lon.toFixed(6);
                
                try {
                    // Get address from coordinates
                    let response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
                    let data = await response.json();
                    
                    if (data && data.display_name) {
                        locationInput.value = data.display_name;
                        addressInput.value = data.display_name;
                        
                        // Add success animation
                        locationInput.classList.add('border-green-500');
                        setTimeout(() => {
                            locationInput.classList.remove('border-green-500');
                        }, 2000);
                    } else {
                        locationInput.value = "Location found, but address unavailable";
                        addressInput.value = "Address unavailable";
                    }
                } catch (error) {
                    showError("Unable to retrieve address information.");
                }
                
                // Hide loading indicator
                loadingIndicator.classList.add('hidden');
            }, (error) => {
                // Handle geolocation errors
                let errorMessage;
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = "Location access was denied. Please enable location services.";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = "Location information is unavailable.";
                        break;
                    case error.TIMEOUT:
                        errorMessage = "The request to get location timed out.";
                        break;
                    default:
                        errorMessage = "An unknown error occurred while retrieving location.";
                        break;
                }
                showError(errorMessage);
                loadingIndicator.classList.add('hidden');
            });
        } catch (error) {
            showError("An error occurred while fetching location.");
            loadingIndicator.classList.add('hidden');
        }
    } else {
        showError("Geolocation is not supported by your browser.");
        loadingIndicator.classList.add('hidden');
    }
}

// Helper function to show errors
function showError(message) {
    const errorAlert = document.getElementById('errorAlert');
    errorAlert.innerHTML = message;
    errorAlert.classList.remove('hidden');
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        errorAlert.classList.add('hidden');
    }, 5000);
}

// Form validation and submission handling
document.getElementById('accidentForm').addEventListener('submit', function(e) {
    // You can add client-side validation here if needed
    
    // For demo purposes, we can simulate success
    // Remove this block when integrating with real backend
    /*
    e.preventDefault();
    document.getElementById('accidentForm').classList.add('hidden');
    document.getElementById('successAlert').classList.remove('hidden');
    */
});

// Set today's date as the default
window.addEventListener('DOMContentLoaded', (event) => {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('accident_date').setAttribute('max', today);
    document.getElementById('accident_date').value = today;
});
</script>
<?php include('../includes/footer.php'); ?>
