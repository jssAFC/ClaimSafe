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

<div class="flex min-h-screen bg-purple-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
            <h1 class="text-3xl font-bold text-purple-800">User Dashboard</h1>
            <div class="flex gap-4">
                <a href="report_accident.php"
                   class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition">
                    🚨 Report Accident
                </a>
                <a href="logout.php"
                   class="bg-pink-500 hover:bg-pink-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition">
                    🔒 Logout
                </a>
            </div>
        </div>

        <!-- Report Section -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h2 class="text-2xl font-semibold text-purple-700 mb-4">Your Accident Reports</h2>

            <?php if ($result->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-gray-700">
                        <thead>
                            <tr class="bg-purple-100 text-purple-700 uppercase text-xs">
                                <th class="py-3 px-4 text-left">Date</th>
                                <th class="py-3 px-4 text-left">Location</th>
                                <th class="py-3 px-4 text-left">Status</th>
                                <th class="py-3 px-4 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <?php
                                $accident_id = $row['id'];
                                $claim_sql = "SELECT status FROM claims WHERE accident_id = $accident_id";
                                $claim_result = $conn->query($claim_sql);
                                $status = "Not Filed";
                                if ($claim_result->num_rows > 0) {
                                    $claim_row = $claim_result->fetch_assoc();
                                    $status = ucfirst($claim_row['status']);
                                }
                                ?>
                                <tr class="hover:bg-purple-50 transition">
                                    <td class="py-4 px-4 border-b border-gray-200"><?php echo $row['accident_date']; ?></td>
                                    <td class="py-4 px-4 border-b border-gray-200"><?php echo $row['location']; ?></td>
                                    <td class="py-4 px-4 border-b border-gray-200">
                                        <span class="inline-block px-3 py-1 text-xs rounded-full font-medium
                                            <?php echo $status === 'Approved' ? 'bg-green-100 text-green-700' : 
                                                        ($status === 'Pending' ? 'bg-yellow-100 text-yellow-700' : 
                                                        ($status === 'Rejected' ? 'bg-red-100 text-red-600' : 'bg-gray-200 text-gray-600')); ?>">
                                            <?php echo $status; ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 border-b border-gray-200">
                                        <a href="view_accident.php?id=<?php echo $row['id']; ?>"
                                           class="text-purple-600 hover:text-purple-800 font-medium transition">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center mt-6">You haven't reported any accidents yet. 📝</p>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php include('../includes/footer.php'); ?>