<?php
include('../includes/header.php');
include('../includes/db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $company_name = trim($_POST['company_name']);
    $contact_email = trim($_POST['contact_email']);
    $service_areas = trim($_POST['service_areas']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!empty($company_name) && !empty($contact_email) && filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("INSERT INTO insurance_providers (user_id, company_name, contact_email, service_areas, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $user_id, $company_name, $contact_email, $service_areas, $is_active);
        
        if ($stmt->execute()) {
            echo "<p class='text-green-500'>Insurance provider registered successfully.</p>";
        } else {
            echo "<p class='text-red-500'>Error: " . $stmt->error . "</p>";
        }
        
        $stmt->close();
    } else {
        echo "<p class='text-red-500'>Please enter valid data.</p>";
    }
}
?>

<div class="flex min-h-screen bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md mx-auto">
            <h1 class="text-2xl font-bold mb-4">Register Insurance Provider</h1>
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="user_id">User ID</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           type="number" name="user_id" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="company_name">Company Name</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           type="text" name="company_name" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="contact_email">Contact Email</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           type="email" name="contact_email" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="service_areas">Service Areas</label>
                    <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                              name="service_areas"></textarea>
                </div>
                
                <div class="mb-4 flex items-center">
                    <input type="checkbox" name="is_active" class="mr-2" checked>
                    <label class="text-gray-700 text-sm font-bold" for="is_active">Active</label>
                </div>
                
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
                            type="submit">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>

