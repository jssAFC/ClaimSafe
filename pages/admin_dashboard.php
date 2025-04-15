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

<div class="flex flex-col lg:flex-row min-h-screen bg-purple-50">
    <!-- Sidebar -->
    <div class="lg:w-64 w-full bg-purple-800 text-white p-4">
        <div class="text-xl font-bold mb-8">Admin Dashboard</div>
        <nav>
            <ul>
                <li class="mb-2">
                    <a href="#dashboard" class="block p-2 rounded hover:bg-purple-700 transition duration-300">Dashboard</a>
                </li>
                <li class="mb-2">
                    <a href="#pending-agents" class="block p-2 rounded hover:bg-purple-700 transition duration-300">Pending Agents</a>
                </li>
                <li class="mb-2">
                    <a href="manage_users.php" class="block p-2 rounded hover:bg-purple-700 transition duration-300">Manage Users</a>
                </li>
                <li class="mb-2">
                    <a href="manage_companies.php" class="block p-2 rounded hover:bg-purple-700 transition duration-300">Manage Companies</a>
                </li>
                <li class="mb-2">
                    <a href="system_logs.php" class="block p-2 rounded hover:bg-purple-700 transition duration-300">System Logs</a>
                </li>
                <li class="mb-2 mt-8">
                    <a href="logout.php" class="block p-2 rounded bg-purple-600 hover:bg-purple-700 text-center transition duration-300">Logout</a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-4 md:p-8">
        <?php if ($success_message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4 shadow">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4 shadow">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Dashboard Overview -->
        <section id="dashboard" class="mb-8">
            <h2 class="text-2xl font-bold mb-4 text-purple-800">Dashboard Overview</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- User Stats -->
                <div class="bg-white rounded-lg shadow p-4 border-t-4 border-purple-600">
                    <h3 class="font-bold text-purple-700 mb-2">Users</h3>
                    <div class="text-3xl font-bold text-purple-600">
                        <?php echo array_sum($user_stats); ?>
                    </div>
                    <div class="text-sm text-gray-500 mt-2">
                        Victims: <?php echo isset($user_stats['user']) ? $user_stats['user'] : 0; ?><br>
                        Agent: <?php echo isset($user_stats['agent']) ? $user_stats['agent'] : 0; ?><br>
                        Admins: <?php echo isset($user_stats['admin']) ? $user_stats['admin'] : 0; ?>
                    </div>
                </div>

                <!-- Agent Applications -->
                <div class="bg-white rounded-lg shadow p-4 border-t-4 border-purple-600">
                    <h3 class="font-bold text-purple-700 mb-2">Pending Agents</h3>
                    <div class="text-3xl font-bold text-purple-600">
                        <?php echo count($pending_agents); ?>
                    </div>
                    <div class="text-sm text-gray-500 mt-2">
                        Applications awaiting review
                    </div>
                </div>

                <!-- Claims -->
                <div class="bg-white rounded-lg shadow p-4 border-t-4 border-purple-600">
                    <h3 class="font-bold text-purple-700 mb-2">Claims</h3>
                    <div class="text-3xl font-bold text-purple-600">
                        <?php echo isset($claim_stats['total_claims']) ? $claim_stats['total_claims'] : 0; ?>
                    </div>
                    <div class="text-sm text-gray-500 mt-2">
                        Approved: <?php echo isset($claim_stats['approved_claims']) ? $claim_stats['approved_claims'] : 0; ?><br>
                        Pending: <?php echo isset($claim_stats['pending_claims']) ? $claim_stats['pending_claims'] : 0; ?><br>
                        Rejected: <?php echo isset($claim_stats['rejected_claims']) ? $claim_stats['rejected_claims'] : 0; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow p-4 border-t-4 border-purple-600">
                    <h3 class="font-bold text-purple-700 mb-2">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="add_company.php" class="block bg-purple-600 hover:bg-purple-700 text-white text-center py-2 px-4 rounded shadow transition duration-300">
                            Add Insurance Company
                        </a>
                        <a href="export_reports.php" class="block bg-purple-500 hover:bg-purple-600 text-white text-center py-2 px-4 rounded shadow transition duration-300">
                            Export Reports
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pending Agent Applications -->
        <section id="pending-agents" class="mb-8">
            <h2 class="text-2xl font-bold mb-4 text-purple-800">Pending Agent Applications</h2>

            <?php if (empty($pending_agents)): ?>
                <div class="bg-white rounded-lg shadow p-4 border-t-4 border-purple-600">
                    <p class="text-gray-500">No pending applications at this time.</p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow overflow-x-auto border-t-4 border-purple-600">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-purple-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-purple-800 uppercase tracking-wider hidden md:table-cell">Company</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-purple-800 uppercase tracking-wider hidden md:table-cell">Region</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-purple-800 uppercase tracking-wider hidden lg:table-cell">Submitted</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">Document</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($pending_agents as $agent): ?>
                                <tr class="hover:bg-purple-50 transition duration-150">
                                    <td class="px-4 py-4 whitespace-nowrap"><?php echo htmlspecialchars($agent['full_name']); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap"><?php echo htmlspecialchars($agent['email']); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap hidden md:table-cell"><?php echo htmlspecialchars($agent['company_name']); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap hidden md:table-cell"><?php echo htmlspecialchars($agent['region']); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap hidden lg:table-cell"><?php echo date('M d, Y', strtotime($agent['created_at'])); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <a href="<?php echo htmlspecialchars($agent['document_path']); ?>" target="_blank"
                                            class="text-purple-600 hover:text-purple-800 transition duration-300">View Document</a>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                                            <form method="POST" action="" class="inline">
                                                <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" onclick="return confirm('Are you sure you want to approve this agent?')"
                                                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-2 rounded shadow transition duration-300 w-full sm:w-auto">
                                                    Approve
                                                </button>
                                            </form>
                                            <form method="POST" action="" class="inline">
                                                <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" onclick="return confirm('Are you sure you want to reject this agent?')"
                                                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded shadow transition duration-300 w-full sm:w-auto">
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
                <h3 class="text-lg font-medium text-purple-800 mb-2">Applications Detail</h3>
                <div class="space-y-4">
                    <?php foreach ($pending_agents as $agent): ?>
                        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-600">
                            <h4 class="font-bold"><?php echo htmlspecialchars($agent['full_name']); ?></h4>
                            <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($agent['email']); ?></p>
                            
                            <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                                <div>
                                    <span class="font-medium text-purple-700">Company:</span>
                                    <p class="text-gray-700"><?php echo htmlspecialchars($agent['company_name']); ?></p>
                                </div>
                                <div>
                                    <span class="font-medium text-purple-700">Region:</span>
                                    <p class="text-gray-700"><?php echo htmlspecialchars($agent['region']); ?></p>
                                </div>
                                <div>
                                    <span class="font-medium text-purple-700">Submitted:</span>
                                    <p class="text-gray-700"><?php echo date('M d, Y', strtotime($agent['created_at'])); ?></p>
                                </div>
                                <div>
                                    <span class="font-medium text-purple-700">Document:</span>
                                    <p><a href="<?php echo htmlspecialchars($agent['document_path']); ?>" target="_blank"
                                        class="text-purple-600 hover:text-purple-800">View</a></p>
                                </div>
                            </div>
                            
                            <div class="flex space-x-2 mt-3">
                                <form method="POST" action="" class="flex-1">
                                    <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" onclick="return confirm('Are you sure you want to approve this agent?')"
                                        class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded shadow transition duration-300 w-full">
                                        Approve
                                    </button>
                                </form>
                                <form method="POST" action="" class="flex-1">
                                    <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" onclick="return confirm('Are you sure you want to reject this agent?')"
                                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded shadow transition duration-300 w-full">
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