<?php
// Start session for user management
session_start();

// Database Configuration (using environment variables)
$host = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME') ?: 'claimsafe_db';

// Database Connection
try {
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Database connection error. Please try again later.");
}

// CSRF Token Generation
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Database Functions
function getAccidentReport($reportId) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM accident_reports WHERE report_id = ?");
        $stmt->bind_param("s", $reportId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : false;
    } catch (Exception $e) {
        error_log("Error in getAccidentReport: " . $e->getMessage());
        return false;
    }
}

function getVehicleInfo($reportId) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM vehicles WHERE report_id = ?");
        $stmt->bind_param("s", $reportId);
        $stmt->execute();
        $result = $stmt->get_result();
        $vehicles = [];
        while ($row = $result->fetch_assoc()) {
            $vehicles[$row['vehicle_position']] = $row;
        }
        return $vehicles;
    } catch (Exception $e) {
        error_log("Error in getVehicleInfo: " . $e->getMessage());
        return [];
    }
}

function getEvidenceFiles($reportId) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM evidence_files WHERE report_id = ? ORDER BY upload_date DESC");
        $stmt->bind_param("s", $reportId);
        $stmt->execute();
        $result = $stmt->get_result();
        $files = [];
        while ($row = $result->fetch_assoc()) {
            $files[] = $row;
        }
        return $files;
    } catch (Exception $e) {
        error_log("Error in getEvidenceFiles: " . $e->getMessage());
        return [];
    }
}

function getTimelineEvents($reportId) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM timeline_events WHERE report_id = ? ORDER BY event_timestamp ASC");
        $stmt->bind_param("s", $reportId);
        $stmt->execute();
        $result = $stmt->get_result();
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        return $events;
    } catch (Exception $e) {
        error_log("Error in getTimelineEvents: " . $e->getMessage());
        return [];
    }
}

function getInvolvedParties($reportId) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT p.*, pt.party_type_name FROM parties p 
                              JOIN party_types pt ON p.party_type_id = pt.party_type_id 
                              WHERE p.report_id = ?");
        $stmt->bind_param("s", $reportId);
        $stmt->execute();
        $result = $stmt->get_result();
        $parties = [];
        while ($row = $result->fetch_assoc()) {
            $parties[] = $row;
        }
        return $parties;
    } catch (Exception $e) {
        error_log("Error in getInvolvedParties: " . $e->getMessage());
        return [];
    }
}

function getSettlementProposal($reportId) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM settlement_proposals WHERE report_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("s", $reportId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : false;
    } catch (Exception $e) {
        error_log("Error in getSettlementProposal: " . $e->getMessage());
        return false;
    }
}

function updateSettlementStatus($proposalId, $status, $userId) {
    global $conn;
    try {
        $stmt = $conn->prepare("UPDATE settlement_responses SET response_status = ?, response_date = NOW() WHERE proposal_id = ? AND user_id = ?");
        $stmt->bind_param("sis", $status, $proposalId, $userId);
        $result = $stmt->execute();
        
        if ($result) {
            $stmt = $conn->prepare("SELECT proposal_id FROM settlement_responses WHERE proposal_id = ? AND response_status != 'accepted'");
            $stmt->bind_param("i", $proposalId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 0) {
                $stmt = $conn->prepare("UPDATE settlement_proposals SET status = 'finalized', finalized_date = NOW() WHERE proposal_id = ?");
                $stmt->bind_param("i", $proposalId);
                $stmt->execute();
                addTimelineEvent($proposalId, 'settlement_accepted', 'Settlement accepted by all parties');
            }
            return true;
        }
        return false;
    } catch (Exception $e) {
        error_log("Error in updateSettlementStatus: " . $e->getMessage());
        return false;
    }
}

function addComment($reportId, $userId, $comment) {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO comments (report_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sis", $reportId, $userId, $comment);
        $result = $stmt->execute();
        
        if ($result) {
            addTimelineEvent($reportId, 'comment_added', 'Comment added by user');
            return true;
        }
        return false;
    } catch (Exception $e) {
        error_log("Error in addComment: " . $e->getMessage());
        return false;
    }
}

function addTimelineEvent($reportId, $eventType, $description) {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO timeline_events (report_id, event_type, description, event_timestamp) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $reportId, $eventType, $description);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error in addTimelineEvent: " . $e->getMessage());
        return false;
    }
}

function uploadEvidenceFile($reportId, $userId, $fileData) {
    global $conn;
    try {
        if ($fileData['size'] > 10485760) { // 10MB limit
            return array('success' => false, 'message' => 'File too large (max 10MB)');
        }
        
        $allowedTypes = array('image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'video/mp4');
        if (!in_array($fileData['type'], $allowedTypes) || !is_uploaded_file($fileData['tmp_name'])) {
            return array('success' => false, 'message' => 'Invalid file type or upload error');
        }
        
        $fileExtension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $newFilename = uniqid() . '.' . $fileExtension;
        $uploadPath = 'uploads/evidence/' . $newFilename;
        
        if (move_uploaded_file($fileData['tmp_name'], $uploadPath)) {
            $stmt = $conn->prepare("INSERT INTO evidence_files 
                                  (report_id, user_id, file_name, original_name, file_type, file_path, upload_date) 
                                  VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sissss", $reportId, $userId, $newFilename, $fileData['name'], $fileData['type'], $uploadPath);
            $result = $stmt->execute();
            
            if ($result) {
                addTimelineEvent($reportId, 'evidence_added', 'New evidence file uploaded');
                return array('success' => true, 'filename' => $newFilename);
            }
            return array('success' => false, 'message' => 'Database error');
        }
        return array('success' => false, 'message' => 'Failed to upload file');
    } catch (Exception $e) {
        error_log("Error in uploadEvidenceFile: " . $e->getMessage());
        return array('success' => false, 'message' => 'Upload error');
    }
}

// Process Form Submissions
$message = '';
$status = '';
$userId = $_SESSION['user_id'] ?? 1; // Should come from actual authentication

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'accept_settlement':
                if (isset($_POST['proposal_id'])) {
                    $result = updateSettlementStatus($_POST['proposal_id'], 'accepted', $userId);
                    $message = $result ? 'Settlement accepted successfully!' : 'Failed to accept settlement.';
                    $status = $result ? 'success' : 'error';
                }
                break;
                
            case 'dispute_settlement':
                if (isset($_POST['proposal_id'])) {
                    $result = updateSettlementStatus($_POST['proposal_id'], 'disputed', $userId);
                    $message = $result ? 'Settlement disputed successfully!' : 'Failed to dispute settlement.';
                    $status = $result ? 'success' : 'error';
                }
                break;
                
            case 'add_comment':
                if (isset($_POST['report_id']) && isset($_POST['comment'])) {
                    $result = addComment($_POST['report_id'], $userId, filter_var($_POST['comment'], FILTER_SANITIZE_STRING));
                    $message = $result ? 'Comment added successfully!' : 'Failed to add comment.';
                    $status = $result ? 'success' : 'error';
                }
                break;
                
            case 'upload_evidence':
                if (isset($_POST['report_id']) && isset($_FILES['file'])) {
                    $result = uploadEvidenceFile($_POST['report_id'], $userId, $_FILES['file']);
                    $message = $result['success'] ? 'Evidence uploaded successfully!' : 'Failed to upload evidence: ' . $result['message'];
                    $status = $result['success'] ? 'success' : 'error';
                }
                break;
        }
    }
}

// Get report data
$reportId = filter_var($_GET['report_id'] ?? 'AC-2025041-0873', FILTER_SANITIZE_STRING);
$report = getAccidentReport($reportId) ?: [
    'report_id' => 'AC-2025041-0873',
    'date' => 'April 1, 2025',
    'time' => '14:32',
    'location' => '123 Main St, Portland, OR 97201',
    'status' => 'In Progress',
    'description' => 'Two-vehicle collision at intersection...',
    'vehicles' => [
        'A' => ['make_model' => 'Honda Civic', 'license_plate' => 'ABC-1234', 'driver' => 'John Smith', 'insurance' => 'SafeDrive Insurance', 'policy' => 'SD-987654321'],
        'B' => ['make_model' => 'Toyota Camry', 'license_plate' => 'XYZ-5678', 'driver' => 'Jane Doe', 'insurance' => 'AllState Insurance', 'policy' => 'AS-123456789']
    ],
    'evidence' => [
        ['name' => 'Police Report #PD-2025-0428.pdf', 'type' => 'document'],
        ['name' => 'Dashcam_Footage.mp4', 'type' => 'video']
    ],
    'settlement' => [
        'fault_A' => '30', 'fault_B' => '70', 'damages_A' => '3200', 'damages_B' => '1800',
        'resolution' => 'Insurance companies to handle payment based on determined fault percentages.'
    ],
    'timeline' => [
        ['event' => 'Report Created', 'date' => 'April 1, 2025', 'time' => '14:32', 'description' => 'Initial accident report submitted...'],
        ['event' => 'Second Party Joined', 'date' => 'April 1, 2025', 'time' => '14:47', 'description' => 'Jane Doe (Vehicle B) joined...'],
        ['event' => 'Evidence Uploaded', 'date' => 'April 1, 2025', 'time' => '15:03', 'description' => 'Photos, dashcam footage...'],
        ['event' => 'Settlement Proposed', 'date' => 'April 1, 2025', 'time' => '15:18', 'description' => 'Automated fault assessment...']
    ],
    'parties' => [
        ['name' => 'John Smith', 'type' => 'Vehicle A Driver', 'initials' => 'JS', 'color' => 'blue'],
        ['name' => 'Jane Doe', 'type' => 'Vehicle B Driver', 'initials' => 'JD', 'color' => 'green'],
        ['name' => 'SafeDrive Insurance', 'type' => 'Vehicle A Insurer', 'initials' => 'SI', 'color' => 'purple'],
        ['name' => 'AllState Insurance', 'type' => 'Vehicle B Insurer', 'initials' => 'AI', 'color' => 'orange']
    ]
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AccidentAssist - On-Spot Resolution</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .mobile-menu { display: none; }
        @media (max-width: 768px) {
            .mobile-menu.active { display: block; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-700 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span class="font-bold text-xl">AccidentAssist</span>
            </div>
            <div class="hidden md:flex space-x-6">
                <a href="#" class="hover:text-blue-200">Home</a>
                <a href="#" class="hover:text-blue-200">Report Accident</a>
                <a href="#" class="hover:text-blue-200">Dispute Resolution</a>
                <a href="#" class="hover:text-blue-200">My Reports</a>
                <a href="#" class="hover:text-blue-200">Help</a>
            </div>
            <div class="flex items-center space-x-4">
                <button class="bg-white text-blue-700 px-4 py-2 rounded-lg font-medium hover:bg-blue-50">Login</button>
                <button class="md:hidden" aria-label="Toggle mobile menu" id="mobile-menu-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="mobile-menu md:hidden" id="mobile-menu">
            <div class="px-4 py-2 space-y-2">
                <a href="#" class="block text-blue-200 hover:text-white">Home</a>
                <a href="#" class="block text-blue-200 hover:text-white">Report Accident</a>
                <a href="#" class="block text-blue-200 hover:text-white">Dispute Resolution</a>
                <a href="#" class="block text-blue-200 hover:text-white">My Reports</a>
                <a href="#" class="block text-blue-200 hover:text-white">Help</a>
            </div>
        </div>
    </nav>

    <?php if (!empty($message)): ?>
    <div class="container mx-auto px-4 mt-4">
        <div class="p-4 rounded-lg <?php echo $status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Accident Report #<?php echo htmlspecialchars($report['report_id']); ?></h1>
            <div class="flex flex-wrap items-center text-sm text-gray-500 gap-x-6 gap-y-2">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span><?php echo htmlspecialchars($report['date'] . ' | ' . $report['time']); ?></span>
                </div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span><?php echo htmlspecialchars($report['location']); ?></span>
                </div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-yellow-600 font-medium"><?php echo htmlspecialchars($report['status']); ?></span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Accident Details
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-md font-medium text-gray-700 mb-2">Description</h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($report['description']); ?></p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-md font-medium text-gray-700 mb-2">Vehicle A</h3>
                                <ul class="space-y-1 text-gray-600">
                                    <?php foreach ($report['vehicles']['A'] as $key => $value): ?>
                                    <li><span class="font-medium"><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</span> <?php echo htmlspecialchars($value); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div>
                                <h3 class="text-md font-medium text-gray-700 mb-2">Vehicle B</h3>
                                <ul class="space-y-1 text-gray-600">
                                    <?php foreach ($report['vehicles']['B'] as $key => $value): ?>
                                    <li><span class="font-medium"><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</span> <?php echo htmlspecialchars($value); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Photos & Evidence
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div class="bg-gray-100 rounded-lg overflow-hidden">
                            <img src="https://via.placeholder.com/200x150" alt="Accident photo <?php echo $i; ?>" class="w-full h-32 object-cover cursor-pointer hover:opacity-90 evidence-photo">
                        </div>
                        <?php endfor; ?>
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data" class="flex items-center justify-center bg-blue-50 rounded-lg h-32 border-2 border-dashed border-blue-300 cursor-pointer hover:bg-blue-100">
                            <input type="hidden" name="action" value="upload_evidence">
                            <input type="hidden" name="report_id" value="<?php echo htmlspecialchars($report['report_id']); ?>">
                            <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="file" name="file" id="file-upload" class="hidden" accept="image/*,video/mp4,application/pdf" onchange="this.form.submit()">
                            <label for="file-upload" class="cursor-pointer text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="text-sm text-blue-600 font-medium mt-1">Add Evidence</span>
                            </label>
                        </form>
                    </div>
                    <div class="space-y-3">
                        <?php foreach ($report['evidence'] as $file): ?>
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <?php if ($file['type'] == 'document'): ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                <?php elseif ($file['type'] == 'video'): ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                <?php endif; ?>
                            </svg>
                            <span class="text-gray-600"><?php echo htmlspecialchars($file['name']); ?></span>
                            <button class="ml-auto text-blue-600 hover:text-blue-800">View</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Dispute Resolution
                    </h2>
                    <div class="space-y-4">
                        <div class="border-l-4 border-yellow-500 pl-4 py-2">
                            <h3 class="text-md font-medium text-gray-700">Current Status: <span class="text-yellow-600">Awaiting Agreement</span></h3>
                            <p class="text-gray-600 text-sm mt-1">Both parties need to agree on fault determination and compensation details.</p>
                        </div>
                        <div class="p-4 rounded-lg bg-gray-50">
                            <h3 class="text-md font-medium text-gray-700 mb-2">Proposed Settlement</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-600 mb-1">Fault Determination</h4>
                                    <div class="flex items-center">
                                        <div class="h-6 bg-blue-500 rounded-l-lg text-xs text-white font-medium flex items-center justify-center" style="width: <?php echo $report['settlement']['fault_A']; ?>%">
                                            Vehicle A: <?php echo $report['settlement']['fault_A']; ?>%
                                        </div>
                                        <div class="h-6 bg-green-500 rounded-r-lg text-xs text-white font-medium flex items-center justify-center" style="width: <?php echo $report['settlement']['fault_B']; ?>%">
                                            Vehicle B: <?php echo $report['settlement']['fault_B']; ?>%
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-600 mb-1">Estimated Damages</h4>
                                    <div class="space-y-1">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Vehicle A:</span>
                                            <span class="text-sm font-medium">$<?php echo number_format($report['settlement']['damages_A']); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Vehicle B:</span>
                                            <span class="text-sm font-medium">$<?php echo number_format($report['settlement']['damages_B']); ?></span>
                                        </div>
                                        <div class="flex justify-between pt-1 border-t">
                                            <span class="text-sm font-medium text-gray-600">Total:</span>
                                            <span class="text-sm font-medium">$<?php echo number_format($report['settlement']['damages_A'] + $report['settlement']['damages_B']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-600 mb-1">Resolution</h4>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($report['settlement']['resolution']); ?></p>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                                    <input type="hidden" name="action" value="accept_settlement">
                                    <input type="hidden" name="proposal_id" value="1">
                                    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">Accept Settlement</button>
                                </form>
                                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                                    <input type="hidden" name="action" value="dispute_settlement">
                                    <input type="hidden" name="proposal_id" value="1">
                                    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                    <button type="submit" class="bg-white border border-red-600 text-red-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-50">Dispute Settlement</button>
                                </form>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-md font-medium text-gray-700 mb-2">Comments</h3>
                            <div class="space-y-3 mb-4">
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-medium text-sm mr-2">JS</div>
                                        <div>
                                            <div class="font-medium text-sm">John Smith</div>
                                            <div class="text-xs text-gray-500">April 1, 2025 at 14:40</div>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-sm text-gray-600">I've uploaded the dashcam footage that shows the accident from my perspective. The light was green for me.</div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-green-600 flex items-center justify-center text-white font-medium text-sm mr-2">JD</div>
                                        <div>
                                            <div class="font-medium text-sm">Jane Doe</div>
                                            <div class="text-xs text-gray-500">April 1, 2025 at 14:55</div>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-sm text-gray-600">The turn signal was on but the other vehicle didn't yield. I've added photos showing the damage to my car.</div>
                                </div>
                            </div>
                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="flex gap-2">
                                <input type="hidden" name="action" value="add_comment">
                                <input type="hidden" name="report_id" value="<?php echo htmlspecialchars($report['report_id']); ?>">
                                <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                <input type="text" name="comment" placeholder="Add a comment..." class="flex-1 border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">Send</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Timeline
                    </h2>
                    <div class="relative space-y-4">
                        <?php foreach ($report['timeline'] as $index => $event): ?>
                        <div class="flex">
                            <div class="flex flex-col items-center mr-4">
                                <div class="w-3 h-3 bg-blue-600 rounded-full"></div>
                                <?php if ($index < count($report['timeline']) - 1): ?>
                                <div class="w-0.5 h-full bg-blue-200"></div>
                                <?php endif; ?>
                            </div>
                            <div class="pb-5">
                                <div class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($event['event']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($event['date'] . ' at ' . $event['time']); ?></div>
                                <div class="mt-1 text-sm text-gray-600"><?php echo htmlspecialchars($event['description']); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Involved Parties
                    </h2>
                    <div class="space-y-3">
                        <?php foreach ($report['parties'] as $party): ?>
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-medium text-sm mr-3" style="background-color: <?php echo $party['color']; ?>">
                                <?php echo htmlspecialchars($party['initials']); ?>
                            </div>
                            <div>
                                <div class="text-sm font-medium"><?php echo htmlspecialchars($party['name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($party['type']); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Next Steps
                    </h2>
                    <ol class="space-y-3">
                        <li class="flex items-start">
                            <div class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-white font-medium text-sm mr-3 flex-shrink-0">1</div>
                            <div>
                                <div class="text-sm font-medium text-gray-700">Review Settlement Proposal</div>
                                <div class="text-sm text-gray-600">Examine the fault determination and proposed resolution.</div>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-white font-medium text-sm mr-3 flex-shrink-0">2</div>
                            <div>
                                <div class="text-sm font-medium text-gray-700">Accept or Dispute</div>
                                <div class="text-sm text-gray-600">Indicate if you agree with the settlement terms or wish to dispute them.</div>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-white font-medium text-sm mr-3 flex-shrink-0">3</div>
                            <div>
                                <div class="text-sm font-medium text-gray-700">Contact Insurance</div>
                                <div class="text-sm text-gray-600">Share the report with your insurance company for claim processing.</div>
                            </div>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-blue-700 text-white mt-8">
        <div class="container mx-auto px-4 py-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <h3 class="text-lg font-semibold mb-3">AccidentAssist</h3>
                    <p class="text-blue-200 text-sm">Simplifying accident resolution with technology-driven solutions for faster, fairer outcomes.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-3">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-blue-200 hover:text-white">Home</a></li>
                        <li><a href="#" class="text-blue-200 hover:text-white">Report Accident</a></li>
                        <li><a href="#" class="text-blue-200 hover:text-white">Dispute Resolution</a></li>
                        <li><a href="#" class="text-blue-200 hover:text-white">My Reports</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-3">Resources</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-blue-200 hover:text-white">Help Center</a></li>
                        <li><a href="#" class="text-blue-200 hover:text-white">Contact Support</a></li>
                        <li><a href="#" class="text-blue-200 hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="text-blue-200 hover:text-white">Terms of Service</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-3">Contact Us</h3>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center text-blue-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            support@accidentassist.com
                        </li>
                        <li class="flex items-center text-blue-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            1-800-ACCIDENT
                        </li>
                    </ul>
                </div>
            </div>
            <div class="text-center pt-6 mt-6 border-t border-blue-600 text-sm text-blue-200">
                © 2025 AccidentAssist. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
        });

        // Image gallery lightbox
        const evidencePhotos = document.querySelectorAll('.evidence-photo');
        evidencePhotos.forEach(photo => {
            photo.addEventListener('click', () => {
                const lightbox = document.createElement('div');
                lightbox.className = 'fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50';
                const img = document.createElement('img');
                img.src = photo.src;
                img.alt = photo.alt;
                img.className = 'max-h-[80vh] max-w-full';
                const closeBtn = document.createElement('button');
                closeBtn.className = 'absolute top-4 right-4 text-white text-2xl';
                closeBtn.innerHTML = '×';
                closeBtn.addEventListener('click', () => document.body.removeChild(lightbox));
                lightbox.addEventListener('click', (e) => {
                    if (e.target === lightbox) document.body.removeChild(lightbox);
                });
                lightbox.appendChild(img);
                lightbox.appendChild(closeBtn);
                document.body.appendChild(lightbox);
            });
        });

        // Form submission handling
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                const action = formData.get('action');
                
                if (action === 'dispute_settlement') {
                    const reason = prompt('Please provide a reason for disputing the settlement:');
                    if (!reason) {
                        showNotification('Dispute reason is required.', 'error');
                        return;
                    }
                    formData.append('dispute_reason', reason);
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    showNotification(data.message || 'Operation completed', data.status || 'success');
                    if (data.success) setTimeout(() => location.reload(), 1000);
                } catch (error) {
                    showNotification('Error processing request', 'error');
                }
            });
        });

        // Notification function
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg ${type === 'error' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
    });
    </script>
</body>
</html>