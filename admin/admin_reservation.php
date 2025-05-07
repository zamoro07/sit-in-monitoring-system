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
    <title>Reservation/Approval</title>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif']
                    },
                }
            }
        }
    </script>
    <style>
        .gradient-text {
            background: linear-gradient(to right, #2563eb, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
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
    <!-- Header -->
    <div class="text-center text-white font-bold text-2xl py-4 relative shadow-lg" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
        CCS SIT-IN MONITORING SYSTEM
        <div class="absolute top-4 left-6 cursor-pointer" onclick="toggleNav(this)">
            <div class="bar1 w-8 h-1 bg-white my-1 transition-all duration-300"></div>
            <div class="bar2 w-8 h-1 bg-white my-1 transition-all duration-300"></div>
            <div class="bar3 w-8 h-1 bg-white my-1 transition-all duration-300"></div>
        </div>
    </div>

    <!-- Side Navigation -->
    <div id="mySidenav" class="fixed top-0 left-0 h-screen w-72 bg-gradient-to-b from-blue-600 to-blue-800 transform -translate-x-full transition-transform duration-300 ease-in-out z-50 shadow-xl overflow-y-auto">
        <div class="absolute top-0 right-0 m-3">
            <button onclick="closeNav()" class="text-white hover:text-pink-200 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="flex flex-col items-center mt-6">
            <div class="relative">
                <img src="../images/image.jpg" alt="Logo" class="w-20 h-20 rounded-full border-4 border-white/30 object-cover shadow-lg">
                <div class="absolute bottom-0 right-0 bg-green-500 w-3 h-3 rounded-full border-2 border-white"></div>
            </div>
            <p class="text-white font-semibold text-lg mt-2 mb-0">Admin</p>
            <p class="text-purple-200 text-xs mb-3">Administrator</p>
        </div>

        <div class="px-2 py-2">
            <nav class="flex flex-col space-y-1">
                <a href="admin_dashboard.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-home w-5 mr-2 text-center"></i>
                    <span class="font-medium">HOME</span>
                </a>
                <a href="admin_search.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-search w-5 mr-2 text-center"></i>
                    <span class="font-medium">SEARCH</span>
                </a>
                <a href="admin_sitin.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-user-check w-5 mr-2 text-center"></i>
                    <span class="font-medium">SIT-IN</span>
                </a>
                
                <!-- VIEW Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-eye w-5 mr-2 text-center"></i>
                            <span class="font-medium">VIEW</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'transform rotate-180': open }"></i>
                    </button>
                    
                    <div x-show="open" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                        class="pl-7 mt-2 space-y-1">
                        
                        <a href="admin_sitinrec.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-book w-5 mr-2 text-center"></i>
                            <span class="font-medium">Sit-in Records</span>
                        </a>
                        
                        <a href="admin_studlist.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-list w-5 mr-2 text-center"></i>
                            <span class="font-medium">List of Students</span>
                        </a>
                        
                        <a href="admin_feedback.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-comments w-5 mr-2 text-center"></i>
                            <span class="font-medium">Feedbacks</span>
                        </a>
                        
                        <a href="#" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-chart-pie w-5 mr-2 text-center"></i>
                            <span class="font-medium">Daily Analytics</span>
                        </a>
                    </div>
                </div>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-desktop w-5 mr-2 text-center"></i>
                            <span class="font-medium">LAB</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'transform rotate-180': open }"></i>
                    </button>
                    
                    <div x-show="open" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                        class="pl-7 mt-2 space-y-1">
                        
                        <a href="admin_resources.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-box-open w-5 mr-2 text-center"></i>
                            <span class="font-medium">Resources</span>
                        </a>
                        
                        <a href="admin_lab_schedule.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-calendar-alt w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Schedule</span>
                        </a>
                        
                        <a href="admin_lab_usage.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-chart-bar w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Usage Point</span>
                        </a>
                    </div>
                </div>
                <a href="admin_reports.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-chart-line w-5 mr-2 text-center"></i>
                    <span class="font-medium">SIT-IN REPORT</span>
                </a>

                <a href="admin_reservation.php" class="group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-calendar-check w-5 mr-2 text-center"></i>
                    <span class="font-medium">RESERVATION/APPROVAL</span>
                </a>
                
                <div class="border-t border-white/10 my-2"></div>
                
                <a href="../logout.php" class="group px-3 py-2 text-white/90 hover:bg-red-500/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-sign-out-alt w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">LOG OUT</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="container mx-auto px-6 py-8">
        <!-- Main Grid Layout -->
        <div class="flex flex-col gap-6">
            <!-- Top Row - Controls and Request -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Computer Controls Card -->
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30">
                    <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                        <i class="fas fa-desktop text-2xl mr-4 relative z-10"></i>
                        <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Computer Controls</h2>
                    </div>
                    
                    <!-- Lab Selection -->
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
                            
                            <!-- Filter Button -->
                            <button class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50 shadow-lg">
                                <i class="fas fa-filter mr-2"></i> FILTER
                            </button>
                        </div>
                        
                        <!-- PC List with Grid Layout -->
                        <div class="bg-white/80 rounded-xl p-4 shadow-inner max-h-[400px] overflow-y-auto">
                            <div id="pc_message" class="text-center py-6 text-gray-500">
                                Click FILTER to view computers for the selected laboratory
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

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-4 mt-6">
                            <button onclick="setStatus('available')" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-xl transition-all duration-300">
                                <i class="fas fa-check-circle mr-2"></i> Available
                            </button>
                            <button onclick="setStatus('used')" class="bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-6 rounded-xl transition-all duration-300">
                                <i class="fas fa-times-circle mr-2"></i> Used
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Reservation Request Card -->
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30">
                    <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                        <i class="fas fa-clipboard-check text-2xl mr-4 relative z-10"></i>
                        <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Reservation Request</h2>
                    </div>
                    
                    <div class="p-6">
                        <!-- Scrollable Request List -->
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
                                                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-all duration-300">
                                                        <i class="fas fa-check mr-2"></i> Approve
                                                    </button>
                                                </form>
                                                <form method="POST" class="flex-1">
                                                    <input type="hidden" name="reservation_id" value="<?php echo $row['ID']; ?>">
                                                    <input type="hidden" name="action" value="disapprove">
                                                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-all duration-300">
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

            <!-- Bottom Row - Logs -->
            <div class="w-full">
                <!-- Logs Card -->
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30">
                    <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                        <i class="fas fa-history text-2xl mr-4 relative z-10"></i>
                        <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Logs</h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="overflow-y-auto max-h-[600px] bg-white/80 rounded-xl shadow-inner">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white/50">
                                    <?php while($log = $logs->fetch_assoc()): ?>
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

        // Function to handle PC selection
        let selectedPCs = new Set();
        let labStatuses = {};

        // Load all PC statuses when page loads
        function loadAllLabStatuses() {
            fetch('admin_reservation.php?get_all_pc_status')
            .then(response => response.json())
            .then(data => {
                labStatuses = data;
            });
        }

        // Call this when page loads
        document.addEventListener('DOMContentLoaded', loadAllLabStatuses);

        function showPCs() {
            const lab = document.getElementById('labSelect').value;
            if (!lab) {
                alert('Please select a laboratory first');
                return;
            }

            // Reset only selection states
            selectedPCs.clear();
            document.querySelectorAll('.pc-card').forEach(card => {
                card.classList.remove('selected', 'ring-2', 'ring-purple-500', 'bg-purple-50');
                
                // Set all PCs as available by default
                card.style.backgroundColor = '#dcfce7';
                card.style.borderColor = '#22c55e';
                
                // Add available label
                const pcNum = card.getAttribute('data-pc');
                const statusDiv = card.querySelector('.status-label') || document.createElement('div');
                statusDiv.className = 'mt-1 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full status-label';
                statusDiv.textContent = 'Available';
                if (!card.querySelector('.status-label')) {
                    card.querySelector('.flex').appendChild(statusDiv);
                }
            });

            // Show the grid
            document.getElementById('pc_message').classList.add('hidden');
            document.getElementById('pc_grid').classList.remove('hidden');
            
            // Load this lab's status from database to override defaults
            fetch(`admin_reservation.php?lab=${lab}`)
            .then(response => response.json())
            .then(data => {
                labStatuses[lab] = data;
                
                // Apply any existing statuses from database
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

            // Initialize lab status if not exists
            if (!labStatuses[lab]) {
                labStatuses[lab] = {};
            }
            
            // Send update to server
            fetch('admin_reservation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `pc_action=1&pc_list=${JSON.stringify(Array.from(selectedPCs))}&status=${status}&laboratory=${lab}`
            })
            .then(response => {
                // Update local storage and display
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

                // Clear selections
                selectedPCs.clear();
                document.querySelectorAll('.pc-card.selected').forEach(card => {
                    card.classList.remove('selected', 'ring-2', 'ring-purple-500', 'bg-purple-50');
                });
            });
        }

        // Remove the updatePCDisplay function since we're handling updates directly in setStatus
        document.querySelector('.w-full.mt-4').addEventListener('click', showPCs);

        // Remove the lab change event listener since we only want updates on filter click
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

        // Add click event listeners to all PC cards
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