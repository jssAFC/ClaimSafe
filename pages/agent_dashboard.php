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

// Get new unassigned claims & assigned claims
$claims_sql = "SELECT c.*, a.location, a.accident_date, a.description, a.photo_path 
               FROM claims c
               JOIN accidents a ON c.accident_id = a.id
               WHERE (c.company_id = ? AND c.agent_id IS NULL AND c.status = 'new') 
               OR (c.agent_id = ?)
               ORDER BY c.created_at DESC";

$stmt = $conn->prepare($claims_sql);
$stmt->bind_param("ii", $company_id, $agent_id);
$stmt->execute();
$claims_result = $stmt->get_result();
?>

<div class="flex min-h-screen bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Agent Dashboard</h1>
            <div>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                    Logout
                </a>
            </div>
        </div>

        <!-- Display Success or Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Your Assigned Claims</h2>

            <?php if ($claims_result->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Location
                                </th>
                                <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $claims_result->fetch_assoc()): ?>
                                <tr>
                                    <td class="py-4 px-4 border-b border-gray-200">
                                        <?php echo htmlspecialchars($row['accident_date']); ?>
                                    </td>
                                    <td class="py-4 px-4 border-b border-gray-200">
                                        <?php echo htmlspecialchars($row['location']); ?>
                                    </td>
                                    <td class="py-4 px-4 border-b border-gray-200">
                                        <?php if ($row['status'] == 'new' && $row['agent_id'] == NULL): ?>
                                            <form method="POST" action="agent_dashboard.php">
                                                <input type="hidden" name="claim_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="assign_claim" value="1">
                                                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded">
                                                    Review
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="agent_dashboard.php">
                                                <input type="hidden" name="claim_id" value="<?php echo $row['id']; ?>">
                                                <select name="status" class="border border-gray-300 rounded px-2 py-1">
                                                    <option value="in_progress" <?php if ($row['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
                                                    <option value="resolved" <?php if ($row['status'] == 'resolved') echo 'selected'; ?>>Resolved</option>
                                                </select>
                                                <button type="submit" class="ml-2 bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-3 rounded">
                                                    Update
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-4 border-b border-gray-200">
                                        <a href="view_claim.php?id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-700">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500">You don't have any assigned claims yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
