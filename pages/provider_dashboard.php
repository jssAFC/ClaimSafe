<?php
session_start();
// Check if provider is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'provider') {
    header("location: login.php");
    exit();
}

include('../includes/header.php');
include('../includes/db_connection.php');

// Get provider information
$user_id = $_SESSION['user_id'];
$provider_sql = "SELECT * FROM insurance_providers WHERE user_id = $user_id";
$provider_result = $conn->query($provider_sql);

if ($provider_result->num_rows > 0) {
    $provider = $provider_result->fetch_assoc();
    $provider_id = $provider['id'];
    
    // Get claims assigned to this provider
    $claims_sql = "SELECT c.*, a.location, a.accident_date, a.description, a.photo_path 
                   FROM claims c
                   JOIN accidents a ON c.accident_id = a.id
                   WHERE c.provider_id = $provider_id
                   ORDER BY c.created_at DESC";
    $claims_result = $conn->query($claims_sql);
} else {
    // Provider profile not complete
    header("location: provider_profile.php");
    exit();
}
?>

<div class="flex min-h-screen bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Provider Dashboard</h1>
            <div>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                    Logout
                </a>
            </div>
        </div>
        
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
                            <?php while($row = $claims_result->fetch_assoc()): ?>
                                <tr>
                                    <td class="py-4 px-4 border-b border-gray-200">
                                        <?php echo $row['accident_date']; ?>
                                    </td>
                                    <td class="py-4 px-4 border-b border-gray-200">
                                        <?php echo $row['location']; ?>
                                    </td>
                                    <td class="py-4 px-4 border-b border-gray-200">
                                        <?php echo ucfirst($row['status']); ?>
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