<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("location: login.php");
    exit();
}

include('../includes/header.php');
include('../includes/db_connection.php');

// Get user's accidents
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM accidents WHERE user_id = $user_id ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<div class="flex min-h-screen bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">User Dashboard</h1>
            <div>
                <a href="report_accident.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                    Report New Accident
                </a>
                <a href="logout.php" class="ml-4 bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                    Logout
                </a>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Your Accident Reports</h2>
            
            <?php if ($result->num_rows > 0): ?>
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
                            <?php while($row = $result->fetch_assoc()): ?>
                                <?php
                                // Get claim status
                                $accident_id = $row['id'];
                                $claim_sql = "SELECT status FROM claims WHERE accident_id = $accident_id";
                                $claim_result = $conn->query($claim_sql);
                                $status = "Not Filed";
                                if ($claim_result->num_rows > 0) {
                                    $claim_row = $claim_result->fetch_assoc();
                                    $status = ucfirst($claim_row['status']);
                                }
                                ?>
                                <tr>
                                    <td class="py-4 px-4 border-b border-gray-200">
                                        <?php echo $row['accident_date']; ?>
                                    </td>
                                    <td class="py-4 px-4 border-b border-gray-200">
                                        <?php echo $row['location']; ?>
                                    </td>
                                    <td class="py-4 px-4 border-b border-gray-200">
                                        <?php echo $status; ?>
                                    </td>
                                    <td class="py-4 px-4 border-b border-gray-200">
                                        <a href="view_accident.php?id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-700">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500">You haven't reported any accidents yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>