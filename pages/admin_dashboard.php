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

// Handle agent approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['agent_id'])) {
        $agent_id = mysqli_real_escape_string($conn, $_POST['agent_id']);

        if ($_POST['action'] === 'approve') {
            // Update the agent status to approved
            $sql = "UPDATE insurance_agents SET status = 'approved' WHERE id = '$agent_id'";

            if ($conn->query($sql) === TRUE) {
                // Get agent email to send notification
                $sql = "SELECT p.email, p.full_name, u.id FROM insurance_agents p 
                        JOIN users u ON p.user_id = u.id WHERE p.id = '$agent_id'";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $user_id = $row['id'];

                    // Update user role to confirm agent status
                    $update_user = "UPDATE users SET role = 'agent' WHERE id = '$user_id'";
                    $conn->query($update_user);

                    // TODO: Send email notification (you would implement this function)
                    // sendApprovalEmail($row['email'], $row['full_name']);

                    $success_message = "Agent approved successfully.";
                }
            } else {
                $error_message = "Error approving agent: " . $conn->error;
            }
        } elseif ($_POST['action'] === 'reject') {
            // Update the agent status to rejected
            $sql = "UPDATE insurance_agents SET status = 'rejected' WHERE id = '$agent_id'";

            if ($conn->query($sql) === TRUE) {
                // Get agent email to send notification
                $sql = "SELECT email, full_name FROM insurance_agents WHERE id = '$agent_id'";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();

                    // TODO: Send email notification
                    // sendRejectionEmail($row['email'], $row['full_name']);

                    $success_message = "Agent rejected successfully.";
                }
            } else {
                $error_message = "Error rejecting agent: " . $conn->error;
            }
        }
    }
}

// Get pending agent applications
$pending_agents = [];
$sql = "SELECT p.id, p.user_id, p.full_name, u.email, p.region, p.document_path, p.created_at, 
        c.company_name FROM insurance_agents p 
        JOIN users u ON p.user_id = u.id 
        LEFT JOIN insurance_companies c ON p.company_id = c.id
        WHERE p.status = 'pending' 
        ORDER BY p.created_at DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pending_agents[] = $row;
    }
}

// Get user statistics
$user_stats = [];
$sql = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $user_stats[$row['role']] = $row['count'];
    }
}

// Get total claim statistics
$sql = "SELECT COUNT(*) as total_claims, 
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_claims,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_claims,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_claims
        FROM claims";
$result = $conn->query($sql);
$claim_stats = $result->fetch_assoc();
?>

<div class="flex flex-col lg:flex-row min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <div class="lg:w-64 w-full bg-gray-800 text-white p-4">
        <div class="text-xl font-bold mb-8 flex items-center">
            <span class="text-blue-400 mr-2">●</span> Admin Dashboard
        </div>
        <nav>
            <ul>
                <li class="mb-2">
                    <a href="#dashboard" class="block p-2 rounded hover:bg-gray-700 transition duration-300 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li class="mb-2">
                    <a href="#pending-agents" class="block p-2 rounded hover:bg-gray-700 transition duration-300 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Pending Agents
                    </a>
                </li>
                <li class="mb-2">
                    <a href="manage_users.php" class="block p-2 rounded hover:bg-gray-700 transition duration-300 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Manage Users
                    </a>
                </li>
                <li class="mb-2">
                    <a href="manage_companies.php" class="block p-2 rounded hover:bg-gray-700 transition duration-300 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Manage Companies
                    </a>
                </li>
                <li class="mb-2">
                    <a href="system_logs.php" class="block p-2 rounded hover:bg-gray-700 transition duration-300 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        System Logs
                    </a>
                </li>
                <li class="mb-2 mt-8">
                    <a href="logout.php" class="block p-2 rounded bg-red-600 hover:bg-red-700 text-center transition duration-300 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-4 md:p-8">
        <?php if ($success_message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4 shadow-sm">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4 shadow-sm">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Dashboard Overview -->
        <section id="dashboard" class="mb-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-2">Dashboard Overview</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- User Stats -->
                <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition duration-300">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-800">Users</h3>
                    </div>
                    <div class="text-3xl font-bold text-gray-800">
                        <?php echo array_sum($user_stats); ?>
                    </div>
                    <div class="text-sm text-gray-600 mt-2">
                        <div class="flex justify-between mb-1">
                            <span>Victims:</span>
                            <span class="font-medium"><?php echo isset($user_stats['user']) ? $user_stats['user'] : 0; ?></span>
                        </div>
                        <div class="flex justify-between mb-1">
                            <span>Agent:</span>
                            <span class="font-medium"><?php echo isset($user_stats['agent']) ? $user_stats['agent'] : 0; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Admins:</span>
                            <span class="font-medium"><?php echo isset($user_stats['admin']) ? $user_stats['admin'] : 0; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Agent Applications -->
                <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition duration-300">
                    <div class="flex items-center mb-4">
                        <div class="bg-yellow-100 p-3 rounded-full mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-800">Pending Agents</h3>
                    </div>
                    <div class="text-3xl font-bold text-gray-800">
                        <?php echo count($pending_agents); ?>
                    </div>
                    <div class="text-sm text-gray-600 mt-2">
                        Applications awaiting review
                    </div>
                </div>

                <!-- Claims -->
                <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition duration-300">
                    <div class="flex items-center mb-4">
                        <div class="bg-green-100 p-3 rounded-full mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-800">Claims</h3>
                    </div>
                    <div class="text-3xl font-bold text-gray-800">
                        <?php echo isset($claim_stats['total_claims']) ? $claim_stats['total_claims'] : 0; ?>
                    </div>
                    <div class="text-sm text-gray-600 mt-2">
                        <div class="flex justify-between mb-1">
                            <span>Approved:</span>
                            <span class="font-medium"><?php echo isset($claim_stats['approved_claims']) ? $claim_stats['approved_claims'] : 0; ?></span>
                        </div>
                        <div class="flex justify-between mb-1">
                            <span>Pending:</span>
                            <span class="font-medium"><?php echo isset($claim_stats['pending_claims']) ? $claim_stats['pending_claims'] : 0; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Rejected:</span>
                            <span class="font-medium"><?php echo isset($claim_stats['rejected_claims']) ? $claim_stats['rejected_claims'] : 0; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition duration-300">
                    <div class="flex items-center mb-4">
                        <div class="bg-indigo-100 p-3 rounded-full mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-800">Quick Actions</h3>
                    </div>
                    <div class="space-y-3">
                        <a href="add_company.php" class="block bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded shadow-sm transition duration-300">
                            Add Insurance Company
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pending Agent Applications -->
        <section id="pending-agents" class="mb-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-2">Pending Agent Applications</h2>

            <?php if (empty($pending_agents)): ?>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <p class="text-gray-500">No pending applications at this time.</p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider hidden md:table-cell">Company</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider hidden md:table-cell">Region</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider hidden lg:table-cell">Submitted</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Document</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($pending_agents as $agent): ?>
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-4 py-4 whitespace-nowrap text-gray-800"><?php echo htmlspecialchars($agent['full_name']); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap text-gray-800"><?php echo htmlspecialchars($agent['email']); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap text-gray-800 hidden md:table-cell"><?php echo htmlspecialchars($agent['company_name']); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap text-gray-800 hidden md:table-cell"><?php echo htmlspecialchars($agent['region']); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap text-gray-800 hidden lg:table-cell"><?php echo date('M d, Y', strtotime($agent['created_at'])); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap text-gray-800">
                                        <a href="<?php echo htmlspecialchars($agent['document_path']); ?>" target="_blank"
                                            class="text-blue-600 hover:text-blue-800 transition duration-300">View Document</a>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                                            <form method="POST" action="" class="inline">
                                                <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" onclick="return confirm('Are you sure you want to approve this agent?')"
                                                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-2 rounded shadow-sm transition duration-300 w-full sm:w-auto">
                                                    Approve
                                                </button>
                                            </form>
                                            <form method="POST" action="" class="inline">
                                                <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" onclick="return confirm('Are you sure you want to reject this agent?')"
                                                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded shadow-sm transition duration-300 w-full sm:w-auto">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Mobile Pending Applications Detail View -->
        <section class="md:hidden">
            <?php if (!empty($pending_agents)): ?>
                <h3 class="text-lg font-medium text-gray-800 mb-4">Applications Detail</h3>
                <div class="space-y-4">
                    <?php foreach ($pending_agents as $agent): ?>
                        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                            <h4 class="font-bold text-gray-800"><?php echo htmlspecialchars($agent['full_name']); ?></h4>
                            <p class="text-sm text-gray-600 mb-3"><?php echo htmlspecialchars($agent['email']); ?></p>
                            
                            <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                                <div>
                                    <span class="font-medium text-gray-700">Company:</span>
                                    <p class="text-gray-800"><?php echo htmlspecialchars($agent['company_name']); ?></p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Region:</span>
                                    <p class="text-gray-800"><?php echo htmlspecialchars($agent['region']); ?></p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Submitted:</span>
                                    <p class="text-gray-800"><?php echo date('M d, Y', strtotime($agent['created_at'])); ?></p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Document:</span>
                                    <p><a href="<?php echo htmlspecialchars($agent['document_path']); ?>" target="_blank"
                                        class="text-blue-600 hover:text-blue-800">View</a></p>
                                </div>
                            </div>
                            
                            <div class="flex space-x-3 mt-4">
                                <form method="POST" action="" class="flex-1">
                                    <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" onclick="return confirm('Are you sure you want to approve this agent?')"
                                        class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded shadow-sm transition duration-300 w-full">
                                        Approve
                                    </button>
                                </form>
                                <form method="POST" action="" class="flex-1">
                                    <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" onclick="return confirm('Are you sure you want to reject this agent?')"
                                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded shadow-sm transition duration-300 w-full">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
