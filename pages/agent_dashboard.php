<?php
session_start();
// Check if agent is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'agent') {
    header("location: login.php");
    exit();
}

include('../includes/header.php');
include('../includes/db_connection.php');

// Get agent information
$user_id = $_SESSION['user_id'];
$agent_sql = "SELECT * FROM insurance_agents WHERE user_id = $user_id";
$agent_result = $conn->query($agent_sql);

if ($agent_result->num_rows > 0) {
    $agent = $agent_result->fetch_assoc();
    $agent_id = $agent['id'];
    $company_id = $agent['company_id']; // Fetch company_id
} else {
    header("location: agent_profile.php");
    exit();
}

// Handle claim assignment (Review button)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_claim'])) {
    $claim_id = intval($_POST['claim_id']);
    
    // Assign the claim to this agent only if it's still unassigned
    $stmt = $conn->prepare("UPDATE claims SET agent_id = ?, status = 'in_progress' WHERE id = ? AND agent_id IS NULL");
    $stmt->bind_param("ii", $agent_id, $claim_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $success_message = "Claim assigned to you successfully!";
    } else {
        $error_message = "Failed to assign claim. It may have been taken by another agent.";
    }
    $stmt->close();
}

// Process status update for assigned claims
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['claim_id']) && isset($_POST['status'])) {
    $claim_id = intval($_POST['claim_id']);
    $status = $_POST['status'];

    $valid_statuses = ['in_progress', 'resolved'];
    if (in_array($status, $valid_statuses)) {
        $stmt = $conn->prepare("UPDATE claims SET status = ? WHERE id = ? AND agent_id = ?");
        $stmt->bind_param("sii", $status, $claim_id, $agent_id);

        if ($stmt->execute()) {
            $success_message = "Claim status updated successfully!";
        } else {
            $error_message = "Failed to update claim status.";
        }
        $stmt->close();
    } else {
        $error_message = "Invalid status value!";
    }
}

// Get count of claims for sidebar badges
$counts_sql = "SELECT 
                SUM(CASE WHEN company_id = ? AND (agent_id IS NULL OR agent_id = 0) AND status = 'new' THEN 1 ELSE 0 END) as new_count,
                SUM(CASE WHEN agent_id = ? AND status = 'in_progress' THEN 1 ELSE 0 END) as assigned_count,
                SUM(CASE WHEN agent_id = ? AND status = 'resolved' THEN 1 ELSE 0 END) as resolved_count
               FROM claims";
$stmt = $conn->prepare($counts_sql);
$stmt->bind_param("iii", $company_id, $agent_id, $agent_id);
$stmt->execute();
$counts_result = $stmt->get_result();
$counts = $counts_result->fetch_assoc();

// Determine which tab is active
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'new';

// Get claims based on active tab
if ($active_tab == 'new') {
    $claims_sql = "SELECT c.*, a.location, a.accident_date, a.description, a.photo_path 
                  FROM claims c
                  JOIN accidents a ON c.accident_id = a.id
                  WHERE c.company_id = ? AND (c.agent_id IS NULL OR c.agent_id = 0) AND c.status = 'new'
                  ORDER BY c.created_at DESC";
    $stmt = $conn->prepare($claims_sql);
    $stmt->bind_param("i", $company_id);
} elseif ($active_tab == 'assigned') {
    $claims_sql = "SELECT c.*, a.location, a.accident_date, a.description, a.photo_path 
                  FROM claims c
                  JOIN accidents a ON c.accident_id = a.id
                  WHERE c.agent_id = ? AND c.status = 'in_progress'
                  ORDER BY c.created_at DESC";
    $stmt = $conn->prepare($claims_sql);
    $stmt->bind_param("i", $agent_id);
} else { // resolved
    $claims_sql = "SELECT c.*, a.location, a.accident_date, a.description, a.photo_path 
                  FROM claims c
                  JOIN accidents a ON c.accident_id = a.id
                  WHERE c.agent_id = ? AND c.status = 'resolved'
                  ORDER BY c.created_at DESC";
    $stmt = $conn->prepare($claims_sql);
    $stmt->bind_param("i", $agent_id);
}

$stmt->execute();
$claims_result = $stmt->get_result();
?>

<div class="flex flex-col lg:flex-row min-h-screen bg-purple-50">
    <!-- Sidebar -->
    <div class="lg:w-64 w-full bg-purple-800 text-white">
        <div class="p-4">
            <h2 class="text-xl font-bold mb-6">Agent Portal</h2>
            <nav>
                <ul>
                    <li class="mb-2">
                        <a href="agent_dashboard.php?tab=new" class="flex justify-between items-center p-3 rounded <?php echo $active_tab == 'new' ? 'bg-purple-700' : 'hover:bg-purple-700'; ?> transition duration-300">
                            <span>New Claims</span>
                            <span class="bg-yellow-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">
                                <?php echo $counts['new_count'] ? $counts['new_count'] : '0'; ?>
                            </span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="agent_dashboard.php?tab=assigned" class="flex justify-between items-center p-3 rounded <?php echo $active_tab == 'assigned' ? 'bg-purple-700' : 'hover:bg-purple-700'; ?> transition duration-300">
                            <span>Assigned Claims</span>
                            <span class="bg-purple-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">
                                <?php echo $counts['assigned_count'] ? $counts['assigned_count'] : '0'; ?>
                            </span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="agent_dashboard.php?tab=resolved" class="flex justify-between items-center p-3 rounded <?php echo $active_tab == 'resolved' ? 'bg-purple-700' : 'hover:bg-purple-700'; ?> transition duration-300">
                            <span>Resolved Claims</span>
                            <span class="bg-green-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">
                                <?php echo $counts['resolved_count'] ? $counts['resolved_count'] : '0'; ?>
                            </span>
                        </a>
                    </li>
                    <li class="mb-2 mt-8">
                        <a href="agent_profile.php" class="block p-3 rounded hover:bg-purple-700 transition duration-300">
                            My Profile
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="reports.php" class="block p-3 rounded hover:bg-purple-700 transition duration-300">
                            Reports
                        </a>
                    </li>
                    <li class="mb-2 mt-8">
                        <a href="logout.php" class="block p-3 rounded bg-purple-600 hover:bg-purple-700 text-center transition duration-300">
                            Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1">
        <div class="container mx-auto px-4 md:px-6 py-6 md:py-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                <h1 class="text-2xl font-bold text-purple-800 mb-2 md:mb-0">
                    <?php 
                    if ($active_tab == 'new') echo "New Claims";
                    elseif ($active_tab == 'assigned') echo "Assigned Claims";
                    else echo "Resolved Claims";
                    ?>
                </h1>
                <div class="text-sm text-purple-600 font-medium">
                    Welcome, <?php echo htmlspecialchars($agent['full_name']); ?>
                </div>
            </div>

            <!-- Display Success or Error Messages -->
            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow mb-4">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow mb-4">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-lg p-4 md:p-6 border-t-4 border-purple-600">
                <?php if ($claims_result->num_rows > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr>
                                    <th class="py-3 px-4 border-b border-gray-200 bg-purple-100 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">
                                        Claim ID
                                    </th>
                                    <th class="py-3 px-4 border-b border-gray-200 bg-purple-100 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="py-3 px-4 border-b border-gray-200 bg-purple-100 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">
                                        Location
                                    </th>
                                    <?php if ($active_tab == 'new'): ?>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-purple-100 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">
                                            Action
                                        </th>
                                    <?php elseif ($active_tab == 'assigned'): ?>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-purple-100 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">
                                            Status
                                        </th>
                                    <?php endif; ?>
                                    <th class="py-3 px-4 border-b border-gray-200 bg-purple-100 text-left text-xs font-medium text-purple-800 uppercase tracking-wider">
                                        Details
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $claims_result->fetch_assoc()): ?>
                                    <tr class="hover:bg-purple-50 transition duration-150">
                                        <td class="py-4 px-4 border-b border-gray-200">
                                            #<?php echo htmlspecialchars($row['id']); ?>
                                        </td>
                                        <td class="py-4 px-4 border-b border-gray-200">
                                            <?php echo htmlspecialchars($row['accident_date']); ?>
                                        </td>
                                        <td class="py-4 px-4 border-b border-gray-200">
                                            <?php echo htmlspecialchars($row['location']); ?>
                                        </td>
                                        <?php if ($active_tab == 'new'): ?>
                                            <td class="py-4 px-4 border-b border-gray-200">
                                                <form method="POST" action="agent_dashboard.php?tab=new">
                                                    <input type="hidden" name="claim_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="assign_claim" value="1">
                                                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-1 px-3 rounded shadow transition duration-300">
                                                        Take Claim
                                                    </button>
                                                </form>
                                            </td>
                                        <?php elseif ($active_tab == 'assigned'): ?>
                                            <td class="py-4 px-4 border-b border-gray-200">
                                                <form method="POST" action="agent_dashboard.php?tab=assigned" class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                                                    <input type="hidden" name="claim_id" value="<?php echo $row['id']; ?>">
                                                    <select name="status" class="border border-purple-300 rounded px-2 py-1 focus:ring-2 focus:ring-purple-300 focus:border-purple-500 focus:outline-none">
                                                        <option value="in_progress" <?php if ($row['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
                                                        <option value="resolved" <?php if ($row['status'] == 'resolved') echo 'selected'; ?>>Resolved</option>
                                                    </select>
                                                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-1 px-3 rounded shadow transition duration-300">
                                                        Update
                                                    </button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                        <td class="py-4 px-4 border-b border-gray-200">
                                            <a href="view_claim.php?id=<?php echo $row['id']; ?>" class="text-purple-600 hover:text-purple-800 font-medium transition duration-300">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-lg font-medium text-purple-900">No claims found</h3>
                        <?php if ($active_tab == 'new'): ?>
                            <p class="mt-1 text-gray-500">There are no new claims available for review.</p>
                        <?php elseif ($active_tab == 'assigned'): ?>
                            <p class="mt-1 text-gray-500">You don't have any claims in progress.</p>
                        <?php else: ?>
                            <p class="mt-1 text-gray-500">You don't have any resolved claims yet.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include('../includes/footer.php'); ?>