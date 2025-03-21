<?php
session_start();
include('../includes/header.php');
include('../includes/db_connection.php');

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Get list of Indian states for the dropdown (same as in registration.php)
$states = [
    "Andhra Pradesh", "Arunachal Pradesh", "Assam", "Bihar", "Chhattisgarh", 
    "Goa", "Gujarat", "Haryana", "Himachal Pradesh", "Jharkhand", 
    "Karnataka", "Kerala", "Madhya Pradesh", "Maharashtra", "Manipur", 
    "Meghalaya", "Mizoram", "Nagaland", "Odisha", "Punjab", 
    "Rajasthan", "Sikkim", "Tamil Nadu", "Telangana", "Tripura", 
    "Uttar Pradesh", "Uttarakhand", "West Bengal",
    "Andaman and Nicobar Islands", "Chandigarh", "Dadra and Nagar Haveli and Daman and Diu",
    "Delhi", "Jammu and Kashmir", "Ladakh", "Lakshadweep", "Puducherry"
];

// Get list of provider users to associate with company
$providers = [];
$sql = "SELECT u.id, u.full_name, u.email FROM users u 
        WHERE u.role = 'provider' 
        ORDER BY u.full_name";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $providers[$row['id']] = $row['full_name'] . ' (' . $row['email'] . ')';
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);
    $user_id = isset($_POST['user_id']) ? mysqli_real_escape_string($conn, $_POST['user_id']) : 'NULL';
    
    // Process service areas
    $service_areas = [];
    if (isset($_POST['service_areas']) && is_array($_POST['service_areas'])) {
        foreach ($_POST['service_areas'] as $area) {
            $service_areas[] = mysqli_real_escape_string($conn, $area);
        }
    }
    $service_areas_str = implode(',', $service_areas);
    
    // Is active status
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Insert into database
    if ($user_id === 'NULL') {
        $sql = "INSERT INTO insurance_companies (company_name, contact_email, service_areas, is_active) 
                VALUES ('$company_name', '$contact_email', '$service_areas_str', $is_active)";
    } else {
        $sql = "INSERT INTO insurance_companies (user_id, company_name, contact_email, service_areas, is_active) 
                VALUES ($user_id, '$company_name', '$contact_email', '$service_areas_str', $is_active)";
    }
    
    if ($conn->query($sql) === TRUE) {
        $success_message = "New insurance company added successfully!";
    } else {
        $error_message = "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<div class="flex min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <div class="w-64 bg-blue-800 text-white p-4">
        <div class="text-xl font-bold mb-8">Admin Dashboard</div>
        <nav>
            <ul>
                <li class="mb-2">
                    <a href="admin_dashboard.php" class="block p-2 rounded hover:bg-blue-700">Dashboard</a>
                </li>
                <li class="mb-2">
                    <a href="admin_dashboard.php#pending-providers" class="block p-2 rounded hover:bg-blue-700">Pending Providers</a>
                </li>
                <li class="mb-2">
                    <a href="admin_dashboard.php#recent-actions" class="block p-2 rounded hover:bg-blue-700">Recent Actions</a>
                </li>
                <li class="mb-2">
                    <a href="manage_users.php" class="block p-2 rounded hover:bg-blue-700">Manage Users</a>
                </li>
                <li class="mb-2">
                    <a href="manage_companies.php" class="block p-2 rounded hover:bg-blue-700 bg-blue-700">Manage Companies</a>
                </li>
                <li class="mb-2">
                    <a href="system_logs.php" class="block p-2 rounded hover:bg-blue-700">System Logs</a>
                </li>
                <li class="mb-2 mt-8">
                    <a href="logout.php" class="block p-2 rounded bg-red-600 hover:bg-red-700 text-center">Logout</a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Add Insurance Company</h1>
            <a href="manage_companies.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Back to Companies
            </a>
        </div>

        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Company Name -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="company_name" class="block text-gray-700 text-sm font-bold mb-2">
                            Company Name *
                        </label>
                        <input type="text" id="company_name" name="company_name" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <!-- Contact Email -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="contact_email" class="block text-gray-700 text-sm font-bold mb-2">
                            Contact Email *
                        </label>
                        <input type="email" id="contact_email" name="contact_email" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <!-- Associated User (Provider) -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="user_id" class="block text-gray-700 text-sm font-bold mb-2">
                            Associated Provider (Optional)
                        </label>
                        <select id="user_id" name="user_id"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">None (No associated provider)</option>
                            <?php foreach ($providers as $id => $name): ?>
                                <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Associate a primary provider user with this company if applicable</p>
                    </div>

                    <!-- Active Status -->
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Status
                        </label>
                        <div class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" checked
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-gray-700">
                                Active company (available for selection)
                            </label>
                        </div>
                    </div>

                    <!-- Service Areas -->
                    <div class="col-span-2">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Service Areas *
                        </label>
                        <div class="bg-gray-50 p-4 rounded border h-48 overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                <?php foreach ($states as $state): ?>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="state_<?php echo str_replace(' ', '_', $state); ?>" 
                                            name="service_areas[]" value="<?php echo $state; ?>" 
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="state_<?php echo str_replace(' ', '_', $state); ?>" class="ml-2 block text-gray-700 text-sm">
                                            <?php echo $state; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="mt-2 text-sm">
                            <button type="button" id="select-all" class="text-blue-600 hover:text-blue-800">Select All</button> | 
                            <button type="button" id="deselect-all" class="text-blue-600 hover:text-blue-800">Deselect All</button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-span-2 mt-4">
                        <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Add Insurance Company
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Add JavaScript for the select/deselect all functionality
    document.getElementById('select-all').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('input[name="service_areas[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
    });

    document.getElementById('deselect-all').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('input[name="service_areas[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    });
</script>

<?php include('../includes/footer.php'); ?>