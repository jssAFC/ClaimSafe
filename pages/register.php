<?php
include('../includes/header.php');
include('../includes/db_connection.php');

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    if ($role == 'victim') {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);

        // Check if username or email already exists
        $check_sql = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            $existing_user = $check_result->fetch_assoc();
            if ($existing_user['username'] == $username) {
                $error = "Username already taken.";
            } else {
                $error = "Account exists for this email.";
            }
        } else {
            // Insert into users table
            $sql = "INSERT INTO users (username, email, password, full_name, role) 
                    VALUES ('$username', '$email', '$password', '$full_name', 'user')";

            if ($conn->query($sql) === TRUE) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    } elseif ($role == 'agent') {
        $conn->begin_transaction();

        try {
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
            $region = mysqli_real_escape_string($conn, $_POST['region']);
            $company_id = mysqli_real_escape_string($conn, $_POST['company_id']);

            // Check if username or email already exists
            $check_sql = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
            $check_result = $conn->query($check_sql);

            if ($check_result->num_rows > 0) {
                $existing_user = $check_result->fetch_assoc();
                if ($existing_user['username'] == $username) {
                    throw new Exception("Username already taken.");
                } else {
                    throw new Exception("Account exists for this email.");
                }
            } else {
                $sql = "INSERT INTO users (username, email, password, full_name, role) 
                        VALUES ('$username', '$email', '$password', '$full_name', 'agent')";

                if ($conn->query($sql) === TRUE) {
                    $user_id = $conn->insert_id;

                    $document_path = '';
                    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
                        $upload_dir = '../uploads/documents/';
                        $filename = time() . '_' . basename($_FILES['document']['name']);
                        $target_file = $upload_dir . $filename;

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

                    $sql = "INSERT INTO insurance_agents (user_id, full_name, region, company_id, document_path, status) 
                            VALUES ('$user_id', '$full_name', '$region', '$company_id', '$document_path', 'pending')";

                    if ($conn->query($sql) !== TRUE) {
                        throw new Exception("Error creating agent profile: " . $conn->error);
                    }

                    $conn->commit();
                    $success = "Your registration has been submitted for review. You will be notified via email once approved.";
                } else {
                    throw new Exception("Error creating user account: " . $conn->error);
                }
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}

// Get list of Indian states for the dropdown
$states = [
    "Andhra Pradesh",
    "Arunachal Pradesh",
    "Assam",
    "Bihar",
    "Chhattisgarh",
    "Goa",
    "Gujarat",
    "Haryana",
    "Himachal Pradesh",
    "Jharkhand",
    "Karnataka",
    "Kerala",
    "Madhya Pradesh",
    "Maharashtra",
    "Manipur",
    "Meghalaya",
    "Mizoram",
    "Nagaland",
    "Odisha",
    "Punjab",
    "Rajasthan",
    "Sikkim",
    "Tamil Nadu",
    "Telangana",
    "Tripura",
    "Uttar Pradesh",
    "Uttarakhand",
    "West Bengal",
    "Andaman and Nicobar Islands",
    "Chandigarh",
    "Dadra and Nagar Haveli and Daman and Diu",
    "Delhi",
    "Jammu and Kashmir",
    "Ladakh",
    "Lakshadweep",
    "Puducherry"
];

// Get insurance companies from database
$insurance_companies = [];
$sql = "SELECT id, company_name FROM insurance_companies ORDER BY company_name";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $insurance_companies[$row['id']] = $row['company_name'];
    }
}
?>

<?php include('../includes/header.php'); ?>

<div class="flex min-h-screen items-center bg-purple-50">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md mx-auto border-t-4 border-purple-600">
            <h1 class="text-2xl font-bold mb-4 text-purple-800">Register Your Account</h1>

            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4 shadow">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4 shadow">
                    <?php echo $success; ?>
                    <?php if (strpos($success, "You can now login") !== false): ?>
                        <p class="mt-2"><a href="login.php" class="text-purple-600 font-medium hover:text-purple-800 underline transition duration-300">Login here</a></p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Initial role selection form -->
                <div id="role-selection-form" class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-4">
                        I am registering as:
                    </label>
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 mb-4">
                        <button type="button" id="victim-btn"
                            class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 transition duration-300 ease-in-out transform hover:-translate-y-1 flex-1 shadow-md">
                            <span class="block text-lg">Accident Victim</span>
                            <span class="block text-xs mt-1 text-purple-200">I need to file a claim</span>
                        </button>
                        <button type="button" id="agent-btn"
                            class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 transition duration-300 ease-in-out transform hover:-translate-y-1 flex-1 shadow-md">
                            <span class="block text-lg">Insurance Agent</span>
                            <span class="block text-xs mt-1 text-purple-200">I process claims</span>
                        </button>
                    </div>
                </div>

                <!-- Victim registration form (initially hidden) -->
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="victim-form" class="hidden" enctype="multipart/form-data">
                    <input type="hidden" name="role" value="victim">
                    
                    <div class="mb-6 relative">
                        <label class="block text-purple-700 text-sm font-bold mb-2" for="full_name">
                            Full Name
                        </label>
                        <input class="shadow appearance-none border border-purple-200 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-purple-500 focus:ring-2 focus:ring-purple-300"
                            id="full_name" name="full_name" type="text" placeholder="John Doe" required>
                    </div>

                    <div class="mb-6 relative">
                        <label class="block text-purple-700 text-sm font-bold mb-2" for="email">
                            Email
                        </label>
                        <input class="shadow appearance-none border border-purple-200 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-purple-500 focus:ring-2 focus:ring-purple-300"
                            id="email" name="email" type="email" placeholder="john@example.com" required>
                    </div>

                    <div class="mb-6 relative">
                        <label class="block text-purple-700 text-sm font-bold mb-2" for="username">
                            Username
                        </label>
                        <input class="shadow appearance-none border border-purple-200 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-purple-500 focus:ring-2 focus:ring-purple-300"
                            id="username" name="username" type="text" placeholder="johndoe" required>
                    </div>

                    <div class="mb-6 relative">
                        <label class="block text-purple-700 text-sm font-bold mb-2" for="password">
                            Password
                        </label>
                        <input class="shadow appearance-none border border-purple-200 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-purple-500 focus:ring-2 focus:ring-purple-300"
                            id="password" name="password" type="password" placeholder="••••••••" required>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                        <button class="w-full sm:w-auto bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 shadow-md hover:shadow-lg"
                            type="submit">
                            Register Now
                        </button>
                        <a class="inline-block align-baseline font-bold text-sm text-purple-600 hover:text-purple-800 transition duration-300" href="login.php">
                            Already have an account?
                        </a>
                    </div>
                </form>

                <!-- Agent registration form (initially hidden) -->
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="agent-form" class="hidden" enctype="multipart/form-data">
                    <input type="hidden" name="role" value="agent">

                    <div class="mb-6 relative">
                        <label class="block text-purple-700 text-sm font-bold mb-2" for="agent_full_name">
                            Full Name
                        </label>
                        <input class="shadow appearance-none border border-purple-200 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-purple-500 focus:ring-2 focus:ring-purple-300"
                            id="agent_full_name" name="full_name" type="text" placeholder="Jane Smith" required>
                    </div>

                    <div class="mb-6 relative">
                        <label class="block text-purple-700 text-sm font-bold mb-2" for="agent_email">
                            Email
                        </label>
                        <input class="shadow appearance-none border border-purple-200 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-purple-500 focus:ring-2 focus:ring-purple-300"
                            id="agent_email" name="email" type="email" placeholder="jane@insurance.com" required>
                    </div>

                    <div class="mb-6 relative">
                        <label class="block text-purple-700 text-sm font-bold mb-2" for="region">
                            Region
                        </label>
                        <select class="shadow appearance-none border border-purple-200 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-purple-500 focus:ring-2 focus:ring-purple-300"
                            id="region" name="region" required>
                            <option value="">Select your state</option>
                            <?php foreach ($states as $state): ?>
                                <option value="<?php echo $state; ?>"><?php echo $state; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-6 relative">
                        <label class="block text-purple-700 text-sm font-bold mb-2" for="company_id">
                            Insurance Company
                        </label>
                        <select class="shadow appearance-none border border-purple-200 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-purple-500 focus:ring-2 focus:ring-purple-300"
                            id="company_id" name="company_id" required>
                            <option value="">Select your company</option>
                            <?php foreach ($insurance_companies as $id => $name): ?>
                                <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-6 relative">
                        <label class="block text-purple-700 text-sm font-bold mb-2" for="document">
                            ID Document
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-purple-300 border-dashed rounded-lg">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-purple-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="document" class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-purple-500">
                                        <span>Upload a file</span>
                                        <input id="document" name="document" type="file" class="sr-only" accept="image/*,.pdf" required>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">Valid ID proof (image or PDF)</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6 relative">
                        <label class="block text-purple-700 text-sm font-bold mb-2" for="agent_username">
                            Username
                        </label>
                        <input class="shadow appearance-none border border-purple-200 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-purple-500 focus:ring-2 focus:ring-purple-300"
                            id="agent_username" name="username" type="text" placeholder="agentjane" required>
                    </div>

                    <div class="mb-6 relative">
                        <label class="block text-purple-700 text-sm font-bold mb-2" for="agent_password">
                            Password
                        </label>
                        <input class="shadow appearance-none border border-purple-200 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-purple-500 focus:ring-2 focus:ring-purple-300"
                            id="agent_password" name="password" type="password" placeholder="••••••••" required>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                        <button class="w-full sm:w-auto bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 shadow-md hover:shadow-lg"
                            type="submit">
                            Submit for Review
                        </button>
                        <a class="inline-block align-baseline font-bold text-sm text-purple-600 hover:text-purple-800 transition duration-300" href="login.php">
                            Already have an account?
                        </a>
                    </div>
                </form>

                <script>
                    // Show the appropriate form based on role selection
                    document.getElementById('victim-btn').addEventListener('click', function() {
                        document.getElementById('role-selection-form').classList.add('hidden');
                        document.getElementById('victim-form').classList.remove('hidden');
                    });
                    document.getElementById('agent-btn').addEventListener('click', function() {
                        document.getElementById('role-selection-form').classList.add('hidden');
                        document.getElementById('agent-form').classList.remove('hidden');
                    });

                    // Back button functionality
                    function createBackButton() {
                        const backBtn = document.createElement('button');
                        backBtn.textContent = 'Back to Selection';
                        backBtn.className = 'mt-4 text-purple-600 hover:text-purple-800 text-sm font-medium focus:outline-none transition duration-300';
                        backBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            document.getElementById('victim-form').classList.add('hidden');
                            document.getElementById('agent-form').classList.add('hidden');
                            document.getElementById('role-selection-form').classList.remove('hidden');
                        });
                        
                        // Insert back buttons
                        const victimForm = document.getElementById('victim-form');
                        const agentForm = document.getElementById('agent-form');
                        victimForm.appendChild(backBtn.cloneNode(true));
                        agentForm.appendChild(backBtn);
                    }
                    
                    // Create back buttons when the page loads
                    document.addEventListener('DOMContentLoaded', createBackButton);
                </script>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>