<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Fetch pending reservations
$stmt = $conn->prepare("SELECT * FROM reservation WHERE STATUS = 'Pending' ORDER BY DATE, TIME_IN");
$stmt->execute();
$reservations = $stmt->get_result();

// Handle approve/disapprove actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['reservation_id'])) {
        $action = $_POST['action'];
        $reservationId = $_POST['reservation_id'];
        
        // First get the reservation details
        $getReservation = $conn->prepare("SELECT IDNO, FULL_NAME, LABORATORY, PC_NUM, DATE, TIME_IN, PURPOSE FROM reservation WHERE ID = ?");
        $getReservation->bind_param('i', $reservationId);
        $getReservation->execute();
        $result = $getReservation->get_result();
        $reservationData = $result->fetch_assoc();
        
        $newStatus = ($action === 'approve') ? 'Approved' : 'Disapproved';
        
        // Format laboratory name correctly (e.g., "524" to "Lab 524")
        $formattedLab = "Lab " . $reservationData['LABORATORY'];
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Update reservation status
            $updateStmt = $conn->prepare("UPDATE reservation SET STATUS = ? WHERE ID = ?");
            $updateStmt->bind_param('si', $newStatus, $reservationId);
            $updateStmt->execute();
            
            // If approved, insert into curr_sitin table and update computer status
            if ($action === 'approve') {
                // Insert into curr_sitin
                $sitinStmt = $conn->prepare("INSERT INTO curr_sitin (IDNO, FULL_NAME, PURPOSE, LABORATORY, TIME_IN, DATE, STATUS) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
                $sitinStmt->bind_param('ssssss', 
                    $reservationData['IDNO'],
                    $reservationData['FULL_NAME'],
                    $reservationData['PURPOSE'],
                    $formattedLab,  // Use the formatted laboratory name
                    $reservationData['TIME_IN'],
                    $reservationData['DATE']
                );
                $sitinStmt->execute();

                // Update computer status to 'used'
                $labName = 'lab' . $reservationData['LABORATORY'];
                $pcNum = $reservationData['PC_NUM'];
                $usedStatus = 'used';
                
                $updateComputerStmt = $conn->prepare("INSERT INTO computer (LABORATORY, PC_NUM, STATUS) 
                                                     VALUES (?, ?, ?) 
                                                     ON DUPLICATE KEY UPDATE STATUS = ?");
                $updateComputerStmt->bind_param('siss', 
                    $labName,
                    $pcNum,
                    $usedStatus,
                    $usedStatus
                );
                $updateComputerStmt->execute();
            }
            
            // Insert into logs
            $logStmt = $conn->prepare("INSERT INTO reservation_logs (IDNO, FULL_NAME, LABORATORY, PC_NUM, DATE, TIME_IN, STATUS) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $logStmt->bind_param('ississs', 
                $reservationData['IDNO'],
                $reservationData['FULL_NAME'],
                $reservationData['LABORATORY'],
                $reservationData['PC_NUM'],
                $reservationData['DATE'],
                $reservationData['TIME_IN'],
                $newStatus
            );
            $logStmt->execute();
            
            $conn->commit();

            $_SESSION['toast'] = [
                'status' => 'success',
                'message' => $action === 'approve' ? 'Reservation approved successfully' : 'Reservation disapproved successfully'
            ];
            
            // Redirect to refresh the page
            header("Location: admin_reservation.php");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['toast'] = [
                'status' => 'error',
                'message' => 'Failed to process reservation: ' . $e->getMessage()
            ];
            header("Location: admin_reservation.php");
            exit;
        }
    }
}

// When approving a reservation:
if (isset($_POST['approve'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("UPDATE reservation SET STATUS = 'Approved' WHERE ID = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Get the student ID to create a notification
        $getUserQuery = $conn->prepare("SELECT IDNO, FULL_NAME, STUD_NUM, LABORATORY, DATE FROM reservation r 
                                        JOIN users u ON r.IDNO = u.IDNO WHERE r.ID = ?");
        $getUserQuery->bind_param("i", $id);
        $getUserQuery->execute();
        $result = $getUserQuery->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Create notification
            $userId = $row['STUD_NUM'];
            $message = "Your reservation for " . $row['LABORATORY'] . " on " . $row['DATE'] . " has been approved";
            
            $notifyUser = $conn->prepare("INSERT INTO notification (USER_ID, RESERVATION_ID, MESSAGE, IS_READ, CREATED_AT) 
                                          VALUES (?, ?, ?, 0, NOW())");
            $notifyUser->bind_param("iis", $userId, $id, $message);
            $notifyUser->execute();
            $notifyUser->close();
        }
        $getUserQuery->close();
        
        $_SESSION['toast'] = [
            'status' => 'success',
            'message' => 'Reservation approved successfully!'
        ];
    } else {
        $_SESSION['toast'] = [
            'status' => 'error',
            'message' => 'Failed to approve reservation.'
        ];
    }
    $stmt->close();
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// When rejecting a reservation:
if (isset($_POST['reject'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("UPDATE reservation SET STATUS = 'Rejected' WHERE ID = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Get the student ID to create a notification
        $getUserQuery = $conn->prepare("SELECT IDNO, FULL_NAME, STUD_NUM, LABORATORY, DATE FROM reservation r 
                                        JOIN users u ON r.IDNO = u.IDNO WHERE r.ID = ?");
        $getUserQuery->bind_param("i", $id);
        $getUserQuery->execute();
        $result = $getUserQuery->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Create notification
            $userId = $row['STUD_NUM'];
            $message = "Your reservation for " . $row['LABORATORY'] . " on " . $row['DATE'] . " has been rejected";
            
            $notifyUser = $conn->prepare("INSERT INTO notification (USER_ID, RESERVATION_ID, MESSAGE, IS_READ, CREATED_AT) 
                                          VALUES (?, ?, ?, 0, NOW())");
            $notifyUser->bind_param("iis", $userId, $id, $message);
            $notifyUser->execute();
            $notifyUser->close();
        }
        $getUserQuery->close();
        
        $_SESSION['toast'] = [
            'status' => 'success',
            'message' => 'Reservation rejected successfully!'
        ];
    } else {
        $_SESSION['toast'] = [
            'status' => 'error',
            'message' => 'Failed to reject reservation.'
        ];
    }
    $stmt->close();
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle PC status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pc_action'])) {
    $pcList = isset($_POST['pc_list']) ? json_decode($_POST['pc_list']) : [];
    $status = $_POST['status'];
    $laboratory = $_POST['laboratory'];
    
    if (!empty($pcList)) {
        $stmt = $conn->prepare("INSERT INTO computer (LABORATORY, PC_NUM, STATUS) VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE STATUS = VALUES(STATUS)");
                               
        foreach ($pcList as $pcNum) {
            $stmt->bind_param("sis", $laboratory, $pcNum, $status);
            $stmt->execute();
        }
        $stmt->close();
    }
    exit; // Since this will be called via AJAX
}

// Fetch computer status for selected lab
if (isset($_GET['lab'])) {
    $lab = $_GET['lab'];
    $stmt = $conn->prepare("SELECT PC_NUM, STATUS FROM computer WHERE LABORATORY = ?");
    $stmt->bind_param("s", $lab);
    $stmt->execute();
    $result = $stmt->get_result();
    $pcStatus = [];
    while ($row = $result->fetch_assoc()) {
        $pcStatus[$row['PC_NUM']] = $row['STATUS'];
    }
    $stmt->close();
    echo json_encode($pcStatus);
    exit;
}

// Fetch logs for the table
$logsQuery = "SELECT * FROM reservation_logs ORDER BY ACTION_DATE DESC LIMIT 10";
$logs = $conn->query($logsQuery);

// Add this after the other POST handlers
if (isset($_GET['get_all_pc_status'])) {
    $stmt = $conn->prepare("SELECT LABORATORY, PC_NUM, STATUS FROM computer");
    $stmt->execute();
    $result = $stmt->get_result();
    $allPcStatus = [];
    while ($row = $result->fetch_assoc()) {
        $lab = $row['LABORATORY'];
        if (!isset($allPcStatus[$lab])) {
            $allPcStatus[$lab] = [];
        }
        $allPcStatus[$lab][$row['PC_NUM']] = $row['STATUS'];
    }
    echo json_encode($allPcStatus);
    exit;
}

// Add this before the HTML output
if (isset($_SESSION['toast'])) {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-right',
                iconColor: 'white',
                customClass: {
                    popup: 'colored-toast'
                },
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true
            });
            Toast.fire({
                icon: '<?php echo $_SESSION['toast']['status']; ?>',
                title: '<?php echo $_SESSION['toast']['message']; ?>',
                background: '<?php echo $_SESSION['toast']['status'] === 'success' ? '#10B981' : '#EF4444'; ?>'
            });
        });
    </script>
    <?php
    unset($_SESSION['toast']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../logo/ccs.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <title>Reservation Approval</title>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: white;
            transition: background-color 0.3s ease;
        }

        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .nav-item.active {
            background-color: rgba(255, 255, 255, 0.3);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #333;
            transition: background-color 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .colored-toast.swal2-icon-success {
            background-color: #10B981 !important;
        }
        .colored-toast.swal2-icon-error {
            background-color: #EF4444 !important;
        }
        .colored-toast {
            color: #fff !important;
        }
    </style>
</head>
<body class="min-h-screen font-poppins" style="background: white">
    <div class="bg-gradient-to-r from-[#4066E0] to-[#4D6AFF] text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <img src="../logo/ccs.png" alt="Logo" class="w-10 h-10">
                    <h1 class="font-bold text-xl">CCS SIT-IN MONITORING SYSTEM</h1>
                </div>
                <nav class="flex items-center space-x-6">
                    <a href="admin_dashboard.php" class="nav-item">
                        <i class="ri-home-line"></i>
                        <span>Home</span>
                    </a>
                    <a href="admin_search.php" class="nav-item">
                        <i class="ri-search-line"></i>
                        <span>Search</span>
                    </a>
                    <a href="admin_sitin.php" class="nav-item">
                        <i class="ri-user-follow-line"></i>
                        <span>Sit-in</span>
                    </a>
                    <div class="relative group">
                        <button class="nav-item">
                            <i class="ri-eye-line"></i>
                            <span>View</span>
                            <i class="ri-arrow-down-s-line"></i>
                        </button>
                        <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 hidden group-hover:block">
                            <a href="admin_sitinrec.php" class="dropdown-item">
                                <i class="ri-file-list-line mr-2"></i>Sit-in Records
                            </a>
                            <a href="admin_studlist.php" class="dropdown-item">
                                <i class="ri-list-check mr-2"></i>List of Students
                            </a>
                            <a href="admin_feedback.php" class="dropdown-item">
                                <i class="ri-message-3-line mr-2"></i>Feedbacks
                            </a>
                        </div>
                    </div>
                    <div class="relative group">
                        <button class="nav-item">
                            <i class="ri-computer-line"></i>
                            <span>Lab</span>
                            <i class="ri-arrow-down-s-line"></i>
                        </button>
                        <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 hidden group-hover:block">
                            <a href="admin_resources.php" class="dropdown-item">
                                <i class="fas fa-box-open mr-2"></i>Resources
                            </a>
                            <a href="admin_lab_schedule.php" class="dropdown-item">
                                <i class="fas fa-calendar-alt mr-2"></i>Lab Schedule
                            </a>
                            <a href="admin_lab_usage.php" class="dropdown-item">
                                <i class="fas fa-chart-bar mr-2"></i>Lab Usage Point
                            </a>
                        </div>
                    </div>
                    <a href="admin_reports.php" class="nav-item">
                        <i class="ri-line-chart-line"></i>
                        <span>Reports</span>
                    </a>
                    <a href="admin_reservation.php" class="nav-item active">
                        <i class="ri-calendar-check-line"></i>
                        <span>Reservation</span>
                    </a>
                    <a href="../logout.php" class="nav-item hover:bg-red-500/20">
                        <i class="ri-logout-box-r-line"></i>
                        <span>Logout</span>
                    </a>
                </nav>
            </div>
        </div>
    </div>
    <div class="container mx-auto px-6 py-8">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30">
                    <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #4066E0 0%, #4D6AFF 100%)">
                        <h2 class="text-xl font-bold tracking-wider uppercase">Computer Controls</h2>
                    </div>
                    <div class="p-6">
                        <div class="mb-6">
                            <div class="relative">
                                <select id="labSelect" class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none shadow-inner bg-white/80 appearance-none">
                                    <option value="" disabled selected>Select a Lab</option>
                                    <option value="lab517">Lab 517</option>
                                    <option value="lab524">Lab 524</option>
                                    <option value="lab526">Lab 526</option>
                                    <option value="lab528">Lab 528</option>
                                    <option value="lab530">Lab 530</option>
                                    <option value="lab542">Lab 542</option>
                                    <option value="lab544">Lab 544</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            <button class="w-full mt-4 bg-gradient-to-r from-[#4066E0] to-[#4D6AFF] hover:from-[#3055CF] hover:to-[#3C59EE] text-white font-medium py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-[#4066E0] focus:ring-opacity-50 shadow-lg">
                                FILTER
                            </button>
                        </div>
                        <div class="bg-white/80 rounded-xl p-4 shadow-inner max-h-[400px] overflow-y-auto">
                            <div id="pc_message" class="text-center py-6 text-gray-500">
                                Click FILTER 
                            </div>
                            <div id="pc_grid" class="hidden grid grid-cols-5 gap-2">
                                <?php for($i = 1; $i <= 50; $i++): ?>
                                    <div class="rounded-lg border border-gray-200 overflow-hidden shadow-sm transition-all duration-200 hover:shadow-md cursor-pointer pc-card" 
                                         data-pc="<?php echo $i; ?>" onclick="togglePC(<?php echo $i; ?>)">
                                        <div class="flex flex-col items-center justify-center p-3">
                                            <div class="text-purple-700 mb-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                                          d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div class="text-center text-sm font-medium text-gray-800">PC <?php echo $i; ?></div>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-6">
                            <button onclick="setStatus('available')" class="bg-gradient-to-r from-[#4066E0] to-[#4D6AFF] hover:from-[#3055CF] hover:to-[#3C59EE] text-white font-medium py-3 px-6 rounded-xl transition-all duration-300">
                                <i class="fas fa-check-circle mr-2"></i> Available
                            </button>
                            <button onclick="setStatus('used')" class="bg-gradient-to-r from-[#4066E0] to-[#4D6AFF] hover:from-[#3055CF] hover:to-[#3C59EE] text-white font-medium py-3 px-6 rounded-xl transition-all duration-300">
                                <i class="fas fa-times-circle mr-2"></i> Used
                            </button>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30">
                    <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #4066E0 0%, #4D6AFF 100%)">
                        <h2 class="text-xl font-bold tracking-wider uppercase">Reservation Request</h2>
                        <button 
                            x-data 
                            @click="$dispatch('open-logs-modal')" 
                            class="absolute right-4 bg-white/10 hover:bg-white/20 text-white font-medium py-2 px-4 rounded-lg transition-all duration-300 flex items-center gap-2"
                        >
                            <i class="ri-file-list-line"></i> View Logs
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
                            <?php if ($reservations->num_rows > 0): ?>
                                <?php while ($row = $reservations->fetch_assoc()): ?>
                                    <div class="bg-white rounded-xl p-4 shadow-md hover:shadow-lg transition-all duration-300 border border-gray-100">
                                        <div class="space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                                    <?php echo htmlspecialchars($row['STATUS']); ?>
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <p class="text-gray-600">ID Number:</p>
                                                    <p class="font-medium"><?php echo htmlspecialchars($row['IDNO']); ?></p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-600">Student Name:</p>
                                                    <p class="font-medium"><?php echo htmlspecialchars($row['FULL_NAME']); ?></p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-600">Date:</p>
                                                    <p class="font-medium"><?php echo htmlspecialchars($row['DATE']); ?></p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-600">Time:</p>
                                                    <p class="font-medium"><?php echo htmlspecialchars($row['TIME_IN']); ?></p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-600">Laboratory:</p>
                                                    <p class="font-medium"><?php echo htmlspecialchars($row['LABORATORY']); ?></p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-600">PC Number:</p>
                                                    <p class="font-medium">PC <?php echo htmlspecialchars($row['PC_NUM']); ?></p>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-gray-600">Purpose:</p>
                                                <p class="font-medium"><?php echo htmlspecialchars($row['PURPOSE']); ?></p>
                                            </div>
                                            <div class="flex gap-2 mt-4">
                                                <form method="POST" class="flex-1">
                                                    <input type="hidden" name="reservation_id" value="<?php echo $row['ID']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="w-full bg-gradient-to-r from-[#4066E0] to-[#4D6AFF] hover:from-[#3055CF] hover:to-[#3C59EE] text-white font-medium py-2 px-4 rounded-lg transition-all duration-300">
                                                        <i class="fas fa-check mr-2"></i> Approve
                                                    </button>
                                                </form>
                                                <form method="POST" class="flex-1">
                                                    <input type="hidden" name="reservation_id" value="<?php echo $row['ID']; ?>">
                                                    <input type="hidden" name="action" value="disapprove">
                                                    <button type="submit" class="w-full bg-gradient-to-r from-[#4066E0] to-[#4D6AFF] hover:from-[#3055CF] hover:to-[#3C59EE] text-white font-medium py-2 px-4 rounded-lg transition-all duration-300">
                                                        <i class="fas fa-times mr-2"></i> Disapprove
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-6 text-gray-500">
                                    No pending reservations found
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full">
                <div class="flex justify-between items-end">
                    <!-- Removing the old View Logs button placement -->
                </div>
            </div>
        </div>
    </div>
    <!-- Logs Modal (hidden by default, shown on button click) -->
    <div 
        x-data="{ open: false }"
        x-on:open-logs-modal.window="open = true"
        x-show="open"
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    >
        <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30 w-full max-w-4xl max-h-[90vh] flex flex-col">
            <div class="text-white p-4 flex items-center justify-between relative overflow-hidden"
                 style="background: linear-gradient(135deg, #4066E0 0%, #4D6AFF 100%)">
                <h2 class="text-xl font-bold tracking-wider uppercase">Logs</h2>
                <button @click="open = false" class="text-white text-2xl hover:text-gray-200 transition">
                    &times;
                </button>
            </div>
            <div class="p-6 flex-1 overflow-y-auto">
                <div class="overflow-y-auto max-h-[60vh] bg-white/80 rounded-xl shadow-inner">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white/50">
                            <?php
                            // Re-run the logs query here to ensure logs are available in the modal
                            $logsQuery = "SELECT * FROM reservation_logs ORDER BY ACTION_DATE DESC LIMIT 10";
                            $logsModal = $conn->query($logsQuery);
                            while($log = $logsModal->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($log['IDNO']); ?> - <?php echo htmlspecialchars($log['FULL_NAME']); ?></div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo date('M d, Y', strtotime($log['DATE'])); ?> - 
                                            Lab <?php echo htmlspecialchars($log['LABORATORY']); ?> - 
                                            PC <?php echo htmlspecialchars($log['PC_NUM']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                            <?php echo $log['STATUS'] === 'Approved' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo htmlspecialchars($log['STATUS']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        function toggleNav(x) {
            document.getElementById("mySidenav").classList.toggle("-translate-x-0");
            document.getElementById("mySidenav").classList.toggle("-translate-x-full");
        }

        function closeNav() {
            document.getElementById("mySidenav").classList.remove("-translate-x-0");
            document.getElementById("mySidenav").classList.add("-translate-x-full");
        }

        let selectedPCs = new Set();
        let labStatuses = {};

        function loadAllLabStatuses() {
            fetch('admin_reservation.php?get_all_pc_status')
            .then(response => response.json())
            .then(data => {
                labStatuses = data;
            });
        }

        document.addEventListener('DOMContentLoaded', loadAllLabStatuses);

        function showPCs() {
            const lab = document.getElementById('labSelect').value;
            if (!lab) {
                alert('Please select a laboratory first');
                return;
            }

            selectedPCs.clear();
            document.querySelectorAll('.pc-card').forEach(card => {
                card.classList.remove('selected', 'ring-2', 'ring-purple-500', 'bg-purple-50');
                card.style.backgroundColor = '#dcfce7';
                card.style.borderColor = '#22c55e';
                const pcNum = card.getAttribute('data-pc');
                const statusDiv = card.querySelector('.status-label') || document.createElement('div');
                statusDiv.className = 'mt-1 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full status-label';
                statusDiv.textContent = 'Available';
                if (!card.querySelector('.status-label')) {
                    card.querySelector('.flex').appendChild(statusDiv);
                }
            });

            document.getElementById('pc_message').classList.add('hidden');
            document.getElementById('pc_grid').classList.remove('hidden');
            
            fetch(`admin_reservation.php?lab=${lab}`)
            .then(response => response.json())
            .then(data => {
                labStatuses[lab] = data;
                document.querySelectorAll('.pc-card').forEach(card => {
                    const pcNum = card.getAttribute('data-pc');
                    if (labStatuses[lab] && labStatuses[lab][pcNum] === 'used') {
                        card.style.backgroundColor = '#fee2e2';
                        card.style.borderColor = '#ef4444';
                        const statusLabel = card.querySelector('.status-label');
                        if (statusLabel) {
                            statusLabel.className = 'mt-1 bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full status-label';
                            statusLabel.textContent = 'Used';
                        }
                    }
                });
            });
        }

        function setStatus(status) {
            if (selectedPCs.size === 0) {
                alert('Please select at least one PC');
                return;
            }

            const lab = document.getElementById('labSelect').value;
            if (!lab) {
                alert('Please select a laboratory first');
                return;
            }

            if (!labStatuses[lab]) {
                labStatuses[lab] = {};
            }
            
            fetch('admin_reservation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `pc_action=1&pc_list=${JSON.stringify(Array.from(selectedPCs))}&status=${status}&laboratory=${lab}`
            })
            .then(response => {
                selectedPCs.forEach(pcNum => {
                    labStatuses[lab][pcNum] = status;
                    const pcCard = document.querySelector(`[data-pc="${pcNum}"]`);
                    const statusLabel = pcCard.querySelector('.status-label');
                    
                    if (status === 'available') {
                        pcCard.style.backgroundColor = '#dcfce7';
                        pcCard.style.borderColor = '#22c55e';
                        if (statusLabel) {
                            statusLabel.className = 'mt-1 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full status-label';
                            statusLabel.textContent = 'Available';
                        }
                    } else if (status === 'used') {
                        pcCard.style.backgroundColor = '#fee2e2';
                        pcCard.style.borderColor = '#ef4444';
                        if (statusLabel) {
                            statusLabel.className = 'mt-1 bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full status-label';
                            statusLabel.textContent = 'Used';
                        }
                    }
                });

                selectedPCs.clear();
                document.querySelectorAll('.pc-card.selected').forEach(card => {
                    card.classList.remove('selected', 'ring-2', 'ring-purple-500', 'bg-purple-50');
                });
            });
        }

        document.querySelector('.w-full.mt-4').addEventListener('click', showPCs);

        document.getElementById('labSelect').removeEventListener('change', updatePCDisplay);

        function togglePC(pcNumber) {
            const pcCard = document.querySelector(`[data-pc="${pcNumber}"]`);
            const currentLab = document.getElementById('labSelect').value;
            
            if (!currentLab) {
                alert('Please select a laboratory first');
                return;
            }

            if (pcCard.classList.contains('selected')) {
                pcCard.classList.remove('selected', 'ring-2', 'ring-purple-500', 'bg-purple-50');
                selectedPCs.delete(pcNumber);
            } else {
                pcCard.classList.add('selected', 'ring-2', 'ring-purple-500', 'bg-purple-50');
                selectedPCs.add(pcNumber);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.pc-card').forEach(card => {
                card.addEventListener('click', function() {
                    const pcNumber = this.getAttribute('data-pc');
                    togglePC(pcNumber);
                });
            });
        });
    </script>
</body>
</html>