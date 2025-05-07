<?php
session_start();
require '../db.php'; // Add database connection

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_announcement'])) {
    $content = $_POST['new_announcement'];
    $createdBy = 'ADMIN';

    $stmt = $conn->prepare("INSERT INTO announcement (CONTENT, CREATED_DATE, CREATED_BY) VALUES (?, NOW(), ?)");
    $stmt->bind_param("ss", $content, $createdBy);
    
    if ($stmt->execute()) {
        // Get the ID of the newly inserted announcement
        $announcementId = $conn->insert_id;
        
        // Create notifications for all users
        $notifyAllUsers = $conn->prepare("INSERT INTO notification (USER_ID, ANNOUNCEMENT_ID, MESSAGE, IS_READ, CREATED_AT) 
                                          SELECT STUD_NUM, ?, 'Admin posted a new announcement', 0, NOW() 
                                          FROM users");
        $notifyAllUsers->bind_param("i", $announcementId);
        $notifyAllUsers->execute();
        $notifyAllUsers->close();
        
        $_SESSION['toast'] = [
            'status' => 'success',
            'message' => 'Announcement posted successfully!'
        ];
    } else {
        $_SESSION['toast'] = [
            'status' => 'error',
            'message' => 'Failed to post announcement.'
        ];
    }
    $stmt->close();
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch announcements from the database with DESC order
$announcements = [];
$result = $conn->query("SELECT ID, CONTENT, CREATED_DATE, CREATED_BY FROM announcement 
                       WHERE CREATED_BY = 'ADMIN' 
                       ORDER BY ID DESC, CREATED_DATE DESC"); // Changed ordering to show newest first
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}

// Get actual statistics from database
$totalStudents = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM users");
if ($row = $result->fetch_assoc()) {
    $totalStudents = $row['total'];
}

// Get current active sit-ins
$currentSitIns = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM curr_sitin WHERE STATUS = 'Active' AND TIME_OUT IS NULL");
if ($row = $result->fetch_assoc()) {
    $currentSitIns = $row['total'];
}

// Get total sit-ins (including completed ones)
$totalSitIns = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM curr_sitin");
if ($row = $result->fetch_assoc()) {
    $totalSitIns = $row['total'];
}

// Initialize program counts correctly based on PURPOSE enum values
$programCounts = [
    'C Programming' => 0,
    'C++ Programming' => 0,
    'C# Programming' => 0,
    'Java Programming' => 0,
    'Php Programming' => 0,
    'Python Programming' => 0,
    'Database' => 0,
    'Digital Logic & Design' => 0,
    'Embedded System & IOT' => 0,
    'System Integration & Architecture' => 0,
    'Computer Application' => 0,
    'Web Design & Development' => 0,
    'Project Management' => 0
];

$result = $conn->query("SELECT PURPOSE, COUNT(*) as count FROM curr_sitin GROUP BY PURPOSE");
while ($row = $result->fetch_assoc()) {
    $purpose = $row['PURPOSE'];
    if (array_key_exists($purpose, $programCounts)) {
        $programCounts[$purpose] = $row['count'];
    }
}

// Prepare data for ECharts pie chart
$echartsPieData = [];
foreach ($programCounts as $program => $count) {
    $echartsPieData[] = ['value' => $count, 'name' => $program];
}
$echartsPieDataJSON = json_encode($echartsPieData);

// Get students by year level
$yearLevelCounts = [
    '1st Year' => 0,
    '2nd Year' => 0,
    '3rd Year' => 0,
    '4th Year' => 0
];

$result = $conn->query("SELECT YEAR_LEVEL, COUNT(*) as count FROM users GROUP BY YEAR_LEVEL");
while ($row = $result->fetch_assoc()) {
    if (isset($yearLevelCounts[$row['YEAR_LEVEL']])) {
        $yearLevelCounts[$row['YEAR_LEVEL']] = $row['count'];
    }
}

// Convert to JavaScript array - Fix array methods syntax
$yearLevelJSON = json_encode(array_values($yearLevelCounts)); // Fixed from array.values to array_values
$yearLevelLabelsJSON = json_encode(array_keys($yearLevelCounts)); // Fixed from array.keys to array_keys

// Initialize leaderboard array
$leaderboardData = [];

// Update the leaderboard query to count actual sit-in sessions
$leaderboardQuery = "
    SELECT 
        u.FIRST_NAME,
        u.LAST_NAME,
        u.YEAR_LEVEL,
        u.UPLOAD_IMAGE,
        COUNT(c.SITIN_ID) as total_sessions,
        u.TOTAL_POINTS as total_points 
    FROM users u
    LEFT JOIN curr_sitin c ON u.IDNO = c.IDNO
    GROUP BY u.IDNO, u.FIRST_NAME, u.LAST_NAME, u.YEAR_LEVEL, u.UPLOAD_IMAGE, u.TOTAL_POINTS
    ORDER BY u.TOTAL_POINTS DESC, total_sessions DESC
    LIMIT 5
";

$result = $conn->query($leaderboardQuery);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $leaderboardData[] = $row;
    }
}

// Add this after your session checks
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
    <!-- Add ECharts library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.3/echarts.min.js"></script>
    <!-- Add Remixicon library -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <title>Admin Dashboard</title>
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
        /* Add gradient text class for the footer */
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

        /* Update button gradients */
        .btn-gradient {
            background: linear-gradient(to bottom right, #2563eb, #3b82f6);
        }
    </style>
</head>
<body class="min-h-screen font-poppins" style="background: white">
    <!-- Header -->
    <div class="text-center text-white font-bold text-2xl py-4 relative shadow-lg" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
        CCS SIT-IN MONITORING SYSTEM
        <div class="absolute top-4 left-6 cursor-pointer text-white font-medium" onclick="toggleNav(this)">
            Menu
        </div>
        
        <!-- Notification Bell - Modified to initialize with fetchNotifications() -->
        <div class="absolute top-4 right-6" x-data="notificationData" x-init="fetchNotifications()">
            <div class="relative">
                <button @click="open = !open" class="text-white hover:text-pink-200 transition-colors">
                    <i class="fas fa-bell text-xl"></i>
                    <span x-show="unreadCount > 0" x-text="unreadCount" 
                          class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                    </span>
                </button>
                
                <!-- Dropdown Panel -->
                <div x-show="open" 
                     @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl overflow-hidden z-50">
                    
                    <div class="p-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium flex justify-between items-center">
                        <span>Notifications</span>
                        <button @click="markAllAsRead()" x-show="unreadCount > 0" class="text-xs bg-white/20 hover:bg-white/30 rounded px-2 py-1">
                            Mark all as read
                        </button>
                    </div>
                    
                    <div class="max-h-[350px] overflow-y-auto">
                        <template x-if="notifications.length === 0">
                            <div class="flex flex-col items-center justify-center py-8 px-4 text-gray-500">
                                <i class="far fa-bell-slash text-3xl mb-2"></i>
                                <p class="text-center">No notifications yet</p>
                            </div>
                        </template>
                        
                        <template x-for="notification in notifications" :key="notification.NOTIF_ID">
                            <div @click="readNotification(notification.NOTIF_ID, notification)" 
                                 :class="{'bg-indigo-50': !notification.IS_READ}" 
                                 class="p-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mr-3">
                                        <i :class="getNotificationIcon(notification)" class="text-lg mt-1"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900" x-text="getNotificationType(notification)"></p>
                                        <p class="text-xs text-gray-500 mt-1" x-text="notification.MESSAGE"></p>
                                        <div class="flex justify-between items-center mt-2">
                                            <span class="text-xs text-gray-400" x-text="formatDate(notification.CREATED_AT)"></span>
                                            <span x-show="!notification.IS_READ" class="h-2 w-2 bg-blue-500 rounded-full"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <div class="p-2 bg-gray-50 text-center">
                        <a href="admin_notifications.php" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                            View all notifications
                        </a>
                    </div>
                </div>
            </div>
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
                <a href="admin_dashboard.php" class="group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="ri-home-line w-5 mr-2 text-center"></i>
                    <span class="font-medium">HOME</span>
                </a>
                <a href="admin_search.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="ri-search-line w-5 mr-2 text-center"></i>
                    <span class="font-medium">SEARCH</span>
                </a>
                <a href="admin_sitin.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="ri-user-follow-line w-5 mr-2 text-center"></i>
                    <span class="font-medium">SIT-IN</span>
                </a>
                
                <!-- VIEW Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="ri-eye-line w-5 mr-2 text-center"></i>
                            <span class="font-medium">VIEW</span>
                        </div>
                        <i class="ri-arrow-down-s-line transition-transform" :class="{ 'rotate-180': open }"></i>
                    </button>
                    
                    <div x-show="open" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                        class="pl-7 mt-2 space-y-1">
                        
                        <a href="admin_sitinrec.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="ri-file-list-line w-5 mr-2 text-center"></i>
                            <span class="font-medium">Sit-in Records</span>
                        </a>
                        
                        <a href="admin_studlist.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="ri-list-check w-5 mr-2 text-center"></i>
                            <span class="font-medium">List of Students</span>
                        </a>
                        
                        <a href="admin_feedback.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="ri-message-3-line w-5 mr-2 text-center"></i>
                            <span class="font-medium">Feedbacks</span>
                        </a>
                        
                        <a href="#" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-chart-pie w-5 mr-2 text-center"></i>
                            <span class="font-medium">Daily Analytics</span>
                        </a>
                    </div>
                </div>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="ri-computer-line w-5 mr-2 text-center"></i>
                            <span class="font-medium">LAB</span>
                        </div>
                        <i class="ri-arrow-down-s-line transition-transform" :class="{ 'rotate-180': open }"></i>
                    </button>
                    
                    <div x-show="open" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                        class="pl-7 mt-2 space-y-1">
                        
                        <a href="admin_resources.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-box-open w-5 mr-2 text-center"></i>
                            <span class="font-medium">Resources</span>
                        </a>
                        
                        <a href="admin_lab_schedule.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-calendar-alt w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Schedule</span>
                        </a>
                        
                        <a href="admin_lab_usage.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-chart-bar w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Usage Point</span>
                        </a>
                    </div>
                </div>
                <a href="admin_reports.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="ri-line-chart-line w-5 mr-2 text-center"></i>
                    <span class="font-medium">SIT-IN REPORT</span>
                </a>

                <a href="admin_reservation.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="ri-calendar-check-line w-5 mr-2 text-center"></i>
                    <span class="font-medium">RESERVATION/APPROVAL</span>
                </a>
                
                <div class="border-t border-white/10 my-2"></div>
                
                <a href="../logout.php" class="group px-3 py-2 text-white/90 hover:bg-red-500/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="ri-logout-box-r-line w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">LOG OUT</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="px-8 py-8 w-full flex flex-col gap-8">
        <!-- Top Section with Stats and Leaderboard -->
        <div class="flex gap-8">
            <!-- Statistics Section -->
            <div class="flex-1">
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30">
                    <!-- Stats Cards Grid -->
                    <div class="grid grid-cols-3 gap-4 p-6">
                        <div class="bg-gradient-to-br from-blue-50 to-white rounded-xl p-4 shadow-lg border border-blue-100/50 transform hover:scale-102 transition-transform duration-300">
                            <div class="flex flex-col items-center text-center">
                                <div class="mb-2 bg-blue-500/10 p-2 rounded-full">
                                    <i class="fas fa-user-graduate text-xl text-blue-600"></i>
                                </div>
                                <span class="text-3xl font-bold text-blue-600 mb-1"><?php echo $totalStudents; ?></span>
                                <span class="text-xs text-blue-600/70 font-medium uppercase tracking-wider">Students Registered</span>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-purple-50 to-white rounded-xl p-4 shadow-lg border border-purple-100/50 transform hover:scale-102 transition-transform duration-300">
                            <div class="flex flex-col items-center text-center">
                                <div class="mb-2 bg-purple-500/10 p-2 rounded-full">
                                    <i class="fas fa-chair text-xl text-purple-600"></i>
                                </div>  
                                <span class="text-3xl font-bold text-purple-600 mb-1"><?php echo $currentSitIns; ?></span>
                                <span class="text-xs text-purple-600/70 font-medium uppercase tracking-wider">Currently Sit-In</span>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-green-50 to-white rounded-xl p-4 shadow-lg border border-green-100/50 transform hover:scale-102 transition-transform duration-300">
                            <div class="flex flex-col items-center text-center">
                                <div class="mb-2 bg-green-500/10 p-2 rounded-full">
                                    <i class="fas fa-clipboard-list text-xl text-green-600"></i>
                                </div>
                                <span class="text-3xl font-bold text-green-600 mb-1"><?php echo $totalSitIns; ?></span>
                                <span class="text-xs text-green-600/70 font-medium uppercase tracking-wider">Total Sit-Ins</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Horizontal Leaderboard -->
            <div class="w-[800px]">
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30">
                    <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                        <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Student Leaderboard</h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="flex gap-4 overflow-x-auto pb-2">
                            <?php foreach ($leaderboardData as $index => $student): ?>
                                <div class="flex-none w-[200px] bg-gradient-to-br <?php 
                                    switch($index) {
                                        case 0: echo 'from-yellow-50 to-white border-yellow-200'; break;
                                        case 1: echo 'from-gray-50 to-white border-gray-200'; break;
                                        case 2: echo 'from-orange-50 to-white border-orange-200'; break;
                                        default: echo 'from-purple-50 to-white border-purple-200';
                                    }
                                ?> rounded-xl p-4 shadow-lg border transform hover:scale-105 transition-all duration-300">
                                    <div class="flex flex-col items-center text-center space-y-2">
                                        <div class="text-2xl mb-1">
                                            <?php
                                            switch($index) {
                                                case 0: echo '🏆'; break;
                                                case 1: echo '🥈'; break;
                                                case 2: echo '🥉'; break;
                                                case 3: echo '🏅'; break;
                                                case 4: echo '🎖️'; break;
                                                default: echo ($index + 1);
                                            }
                                            ?>
                                        </div>
                                        
                                        <div class="w-16 h-16 rounded-full overflow-hidden bg-gradient-to-br from-purple-500 to-indigo-500 flex items-center justify-center">
                                            <?php if (!empty($student['UPLOAD_IMAGE'])): ?>
                                                <img src="../images/<?php echo $student['UPLOAD_IMAGE']; ?>" 
                                                     alt="<?php echo htmlspecialchars($student['FIRST_NAME']); ?>" 
                                                     class="w-full h-full object-cover"
                                                     onerror="this.onerror=null; this.src='../images/image.jpg';">
                                            <?php else: ?>
                                                <img src="../images/image.jpg" 
                                                     alt="Default Profile" 
                                                     class="w-full h-full object-cover">
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="font-semibold text-gray-800">
                                            <?php echo htmlspecialchars($student['FIRST_NAME'] . ' ' . $student['LAST_NAME']); ?>
                                        </div>
                                        
                                        <div class="text-sm text-gray-600">
                                            <?php echo htmlspecialchars($student['YEAR_LEVEL']); ?>
                                        </div>
                                        
                                        <div class="text-sm space-y-2">
                                            <div class="font-bold text-lg text-indigo-600">
                                                <?php 
                                                    echo number_format($student['total_points']) . ' pts';
                                                ?>
                                            </div>
                                            
                                            <div class="font-medium text-purple-600">
                                                <i class="fas fa-calendar-check mr-1"></i>
                                                <?php echo $student['total_sessions']; ?> sit-ins
                                            </div>
                                            
                                            <div class="text-amber-500 font-medium text-xs">
                                                <?php 
                                                $totalPoints = $student['total_points'];
                                                if ($totalPoints >= 100) {
                                                    echo '⭐⭐⭐ Expert';
                                                } elseif ($totalPoints >= 5) {
                                                    echo '⭐⭐ Intermediate';
                                                } elseif ($totalPoints >= 3) {
                                                    echo '⭐ Advanced';
                                                } elseif ($totalPoints >= 1) {
                                                    echo '📚 Active';
                                                } else {
                                                    echo '🌱 New';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Middle Section -->
        <div class="flex gap-8">
            <!-- Year Level Chart -->
            <div class="w-1/2">
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30">
                    <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                        <i class="fas fa-users text-2xl mr-4 relative z-10"></i>
                        <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Students Year Level</h2>
                    </div>
                    <div class="p-8">
                        <div class="h-[400px] bg-white/80 rounded-2xl p-4 shadow-inner">
                            <div id="yearLevelChart" style="width: 100%; height: 350px; margin: 0 auto;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Programming Distribution Chart -->
            <div class="w-1/2">
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30">
                    <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                        <i class="fas fa-chart-bar text-2xl mr-4 relative z-10"></i>
                        <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Statistics</h2>
                    </div>
                    <div class="p-8 h-[calc(100%-5rem)] flex flex-col">
                        <div class="flex-1 relative bg-white/80 rounded-2xl p-4 shadow-inner">
                            <div id="sitInChart" style="width: 100%; height: 600px; margin: 0 auto;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="flex gap-8">
            <!-- Announcements Section -->
            <div class="w-full">
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30">
                    <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                        <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Announcements</h2>
                    </div>
                    <div class="p-8 h-[calc(100%-5rem)] flex flex-col">
                        <!-- Announcement Form -->
                        <div class="mb-6">
                            <form action="" method="post" class="space-y-4">
                                <textarea 
                                    name="new_announcement" 
                                    placeholder="Type your announcement here..." 
                                    required
                                    class="w-full p-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 resize-y min-h-[120px] shadow-inner bg-white/80"
                                ></textarea>
                                <button type="submit" 
                                    class="bg-gradient-to-r from-blue-600 to-blue-500 text-white py-3 px-6 rounded-xl 
                                    hover:shadow-lg transform hover:scale-105 transition-all duration-300 font-medium">
                                    Post Announcement
                                </button>
                            </form>
                        </div>

                        <!-- Announcements List -->
                        <div class="flex-1 overflow-y-auto">
                            <h3 class="font-bold text-gray-700 mb-4 text-lg">Posted Announcements</h3>
                            <div class="space-y-4 pr-2">
                                <?php if (empty($announcements)): ?>
                                    <p class="text-gray-500 text-center py-4">No announcements available.</p>
                                <?php else: ?>
                                    <?php foreach ($announcements as $announcement): ?>
                                        <div class="bg-white/80 rounded-xl p-5 shadow-md hover:shadow-lg transition-all duration-300 border-l-4 border-gradient-purple" 
                                             style="border-image: linear-gradient(to bottom, #4A69BB, #CD4DCC) 1;">
                                            <div class="flex items-center text-sm font-bold text-purple-600 mb-3">
                                                <span><?php echo htmlspecialchars($announcement['CREATED_BY']); ?></span>
                                                <span class="mx-2">•</span>
                                                <span><?php echo date('Y-M-d', strtotime($announcement['CREATED_DATE'])); ?></span>
                                                <div class="ml-auto flex space-x-2">
                                                    <button onclick="editAnnouncement(<?php echo $announcement['ID']; ?>, this)" 
                                                            class="text-blue-500 hover:text-blue-700 px-2">
                                                        Edit
                                                    </button>
                                                    <button onclick="confirmDelete(<?php echo $announcement['ID']; ?>)" 
                                                            class="text-red-500 hover:text-red-700 px-2">
                                                        Delete
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="text-gray-700 bg-gray-50/80 p-4 rounded-lg announcement-content" 
                                                 id="content-<?php echo $announcement['ID']; ?>">
                                                <?php echo htmlspecialchars($announcement['CONTENT']); ?>
                                            </div>
                                            <div class="hidden edit-form" id="edit-<?php echo $announcement['ID']; ?>">
                                                <textarea class="w-full p-4 border border-gray-200 rounded-xl resize-y min-h-[100px]"><?php echo htmlspecialchars($announcement['CONTENT']); ?></textarea>
                                                <div class="mt-3 flex space-x-2">
                                                    <button onclick="saveAnnouncement(<?php echo $announcement['ID']; ?>)" 
                                                            class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                                                        Save
                                                    </button>
                                                    <button onclick="cancelEdit(<?php echo $announcement['ID']; ?>)" 
                                                            class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <script>
        function toggleNav(x) {
            document.getElementById("mySidenav").classList.toggle("-translate-x-0");
            document.getElementById("mySidenav").classList.toggle("-translate-x-full");
        }

        function closeNav() {
            document.getElementById("mySidenav").classList.remove("-translate-x-0");
            document.getElementById("mySidenav").classList.add("-translate-x-full");
        }
        
        // Notifications functions
        document.addEventListener('alpine:init', () => {
            Alpine.data('notificationData', () => ({
                open: false,
                notifications: [],
                unreadCount: 0,
                
                fetchNotifications() {
                    fetch('fetch_notifications.php')
                        .then(response => response.json())
                        .then(data => {
                            this.notifications = data.notifications;
                            this.unreadCount = data.unread_count;
                        })
                        .catch(error => console.error('Error fetching notifications:', error));
                },
                
                readNotification(id, notification) {
                    fetch('mark_notification_read.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `notification_id=${id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update local notification data
                            this.notifications = this.notifications.map(notif => {
                                if (notif.NOTIF_ID === id) {
                                    return { ...notif, IS_READ: 1 };
                                }
                                return notif;
                            });
                            this.unreadCount = Math.max(0, this.unreadCount - 1);
                            
                            // Handle redirect based on notification type
                            if (notification.RESERVATION_ID) {
                                window.location.href = 'admin_reservation.php';
                            } else if (notification.FEEDBACK_ID) {
                                window.location.href = 'admin_feedback.php';
                            }
                        }
                    })
                    .catch(error => console.error('Error marking notification as read:', error));
                },
                
                markAllAsRead() {
                    fetch('mark_all_notifications_read.php', {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update all notifications to read
                            this.notifications = this.notifications.map(notif => {
                                return { ...notif, IS_READ: 1 };
                            });
                            this.unreadCount = 0;
                            
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
                                icon: 'success',
                                title: 'All notifications marked as read',
                                background: '#10B981'
                            });
                        }
                    })
                    .catch(error => console.error('Error marking all notifications as read:', error));
                },
                
                getNotificationType(notification) {
                    if (notification.RESERVATION_ID) {
                        return 'Reservation Request';
                    } else if (notification.FEEDBACK_ID) {
                        return 'Feedback Received';
                    } else if (notification.ANNOUNCEMENT_ID) {
                        return 'Announcement';
                    } else {
                        return 'Notification';
                    }
                },
                
                getNotificationIcon(notification) {
                    if (notification.RESERVATION_ID) {
                        return 'fas fa-calendar-check text-blue-500';
                    } else if (notification.FEEDBACK_ID) {
                        return 'fas fa-comment-alt text-purple-500';
                    } else if (notification.ANNOUNCEMENT_ID) {
                        return 'fas fa-bullhorn text-yellow-500';
                    } else {
                        return 'fas fa-bell text-gray-500';
                    }
                },
                
                formatDate(dateString) {
                    const date = new Date(dateString);
                    const now = new Date();
                    const diffTime = Math.abs(now - date);
                    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                    
                    if (diffDays === 0) {
                        // Today - show time only
                        return 'Today at ' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                    } else if (diffDays === 1) {
                        return 'Yesterday';
                    } else if (diffDays < 7) {
                        return diffDays + ' days ago';
                    } else {
                        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    }
                }
            }));
        });
        
        // Add this function before the existing scripts
        function confirmDelete(id) {
            // First show confirmation dialog
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-right',
                iconColor: 'white',
                customClass: {
                    popup: 'colored-toast'
                },
                showConfirmButton: true,
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                timer: false
            });

            Toast.fire({
                icon: 'warning',
                title: 'Are you sure to delete?',
                text: 'You won\'t be able to revert this!',
                background: '#F59E0B'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX request to delete
                    fetch(`delete_announcement.php?id=${id}`, {
                        method: 'GET'
                    })
                    .then(response => response.text())
                    .then(() => {
                        // Show success message
                        const SuccessToast = Swal.mixin({
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
                        
                        SuccessToast.fire({
                            icon: 'success',
                            title: 'Announcement deleted successfully',
                            background: '#10B981'
                        }).then(() => {
                            window.location.reload();
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        const ErrorToast = Swal.mixin({
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
                        
                        ErrorToast.fire({
                            icon: 'error',
                            title: 'Failed to delete announcement',
                            background: '#EF4444'
                        });
                    });
                }
            });
        }

        // Initialize the charts
        document.addEventListener('DOMContentLoaded', function() {
            // ECharts Nightingale (Rose) Chart for Sit-In distribution
            const sitInChart = echarts.init(document.getElementById('sitInChart'));
            
            // Define colors for each program
            // Define a more diverse color palette
            const colors = [
                '#36A2EB', // Blue
                '#FF6384', // Pink
                '#FFCE56', // Yellow
                '#4BC0C0', // Teal
                '#9966FF', // Purple
                '#FF9F40', // Orange
                '#4CAF50', // Green
                '#E91E63', // Red
                '#2196F3', // Light Blue
                '#FF5722', // Deep Orange
                '#673AB7', // Deep Purple
                '#009688'  // Cyan
            ];

            // Make sure all purposes are included in the legend, even with zero values
            const pieOption = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} ({d}%)'
                },
                legend: {
                    type: 'plain',
                    orient: 'horizontal',
                    bottom: 10,
                    left: 'center',
                    width: '95%',
                    itemGap: 10,
                    itemWidth: 12,
                    itemHeight: 12,
                    textStyle: {
                        fontSize: 11,
                        color: '#666',
                        padding: [0, 4, 0, 4]
                    },
                    formatter: name => {
                        // Split long names into two lines
                        if (name.includes('&')) {
                            return name.replace(' & ', '\n');
                        }
                        return name;
                    },
                    tooltip: {
                        show: true
                    },
                    data: [
                        'C Programming', 'C++ Programming', 'C# Programming',
                        'Java Programming', 'Php Programming', 'Python Programming',
                        'Database', 'Digital Logic & Design', 'Embedded System & IOT',
                        'System Integration & Architecture', 'Computer Application',
                        'Web Design & Development', 'Project Management'
                    ],
                    pageTextStyle: {
                        color: '#666'
                    },
                    selectedMode: false,
                    grid: {
                        left: 10,
                        right: 10,
                        top: 5,
                        bottom: 10
                    }
                },
                grid: {
                    containLabel: true
                },
                title: {
                    text: 'Programming Languages Distribution',
                    left: 'center',
                    top: 20,
                    textStyle: {
                        fontSize: 16,
                        fontWeight: 'bold'
                    }
                },
                series: [{
                    type: 'pie',
                    radius: ['30%', '60%'],
                    center: ['50%', '40%'],  // Moved up more to accommodate legend
                    avoidLabelOverlap: true,
                    itemStyle: {
                        borderRadius: 6,
                        borderColor: '#fff',
                        borderWidth: 2
                    },
                    label: {
                        show: false
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: '14',
                            fontWeight: 'bold',
                            formatter: '{b}\n{d}%'
                        },
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    },
                    data: <?php echo $echartsPieDataJSON; ?>
                }],
                color: colors,
                backgroundColor: 'transparent'
            };

            sitInChart.setOption(pieOption);
            
            // Make the chart responsive
            window.addEventListener('resize', function() {
                sitInChart.resize();
            });
            
            // Replace the Chart.js initialization with ECharts
            const yearLevelChart = echarts.init(document.getElementById('yearLevelChart'));
            const yearLevelOption = {
                xAxis: {
                    type: 'category',
                    data: <?php echo $yearLevelLabelsJSON; ?>,
                    axisLabel: {
                        fontSize: 12,
                        fontWeight: 'bold'
                    }
                },
                yAxis: {
                    type: 'value',
                    name: 'Number of Students',
                    nameLocation: 'middle',
                    nameGap: 40
                },
                series: [
                    {
                        data: <?php echo $yearLevelJSON; ?>,
                        type: 'bar',
                        showBackground: true,
                        backgroundStyle: {
                            color: 'rgba(180, 180, 180, 0.2)'
                        },
                        itemStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: '#83bff6' },
                                { offset: 0.5, color: '#188df0' },
                                { offset: 1, color: '#188df0' }
                            ])
                        }
                    }
                ]
            };

            yearLevelChart.setOption(yearLevelOption);

            // Update resize handler to include yearLevelChart
            window.addEventListener('resize', function() {
                sitInChart.resize();
                yearLevelChart.resize();
            });

            // Set up polling for notifications every 30 seconds
            setInterval(() => {
                try {
                    const notificationComponent = document.querySelector('[x-data="notificationData"]').__x.$data;
                    notificationComponent.fetchNotifications();
                } catch (error) {
                    console.error('Error updating notifications:', error);
                }
            }, 30000);
        });

        function editAnnouncement(id, button) {
            // Hide content and show edit form
            document.getElementById(`content-${id}`).style.display = 'none';
            document.getElementById(`edit-${id}`).style.display = 'block';
        }

        function cancelEdit(id) {
            // Show content and hide edit form
            document.getElementById(`content-${id}`).style.display = 'block';
            document.getElementById(`edit-${id}`).style.display = 'none';
        }

        function saveAnnouncement(id) {
            const content = document.querySelector(`#edit-${id} textarea`).value;
            
            fetch('update_announcement.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `announcement_id=${id}&content=${encodeURIComponent(content)}`
            })
            .then(response => response.text())
            .then(result => {
                if (result === 'success') {
                    // Update the content display
                    document.getElementById(`content-${id}`).innerText = content;
                    // Hide edit form
                    cancelEdit(id);
                    
                    // Show toast notification
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
                        icon: 'success',
                        title: 'Announcement updated successfully',
                        background: '#10B981'
                    });
                } else {
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
                        icon: 'error',
                        title: 'Failed to update announcement',
                        background: '#EF4444'
                    });
                }
            })
            .catch(error => {
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
                    icon: 'error',
                    title: 'Error updating announcement',
                    background: '#EF4444'
                });
            });
        }
    </script>
</body>
</html>