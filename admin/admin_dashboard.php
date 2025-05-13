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

// Update the leaderboard query to correctly sum points and sessions
$leaderboardQuery = "
    SELECT 
        u.FIRST_NAME,
        u.LAST_NAME,
        u.YEAR_LEVEL,
        u.UPLOAD_IMAGE,
        COUNT(c.SITIN_ID) as total_sessions,
        u.POINTS as current_points,
        u.TOTAL_POINTS as total_points 
    FROM users u
    LEFT JOIN curr_sitin c ON u.IDNO = c.IDNO
    GROUP BY 
        u.IDNO, 
        u.FIRST_NAME, 
        u.LAST_NAME, 
        u.YEAR_LEVEL, 
        u.UPLOAD_IMAGE, 
        u.POINTS,
        u.TOTAL_POINTS
    ORDER BY 
        u.TOTAL_POINTS DESC, 
        u.POINTS DESC,
        total_sessions DESC
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
    </style>
</head>
<body class="min-h-screen font-poppins" style="background: white">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <!-- Left - System Title with Logo -->
                <div class="flex items-center space-x-4">
                    <img src="../logo/ccs.png" alt="Logo" class="w-10 h-10">
                    <h1 class="font-bold text-xl">CCS SIT-IN MONITORING SYSTEM</h1>
                </div>

                <!-- Center/Right - Navigation Menu -->
                <nav class="flex items-center space-x-6">
                    <a href="admin_dashboard.php" class="nav-item active">
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

                    <!-- View Dropdown -->
                    <div class="relative group">
                        <button class="nav-item" onclick="toggleDropdown('viewDropdown')">
                            <i class="ri-eye-line"></i>
                            <span>View</span>
                            <i class="ri-arrow-down-s-line"></i>
                        </button>
                        <div id="viewDropdown" class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 hidden">
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

                    <!-- Lab Dropdown -->
                    <div class="relative group">
                        <button class="nav-item" onclick="toggleDropdown('labDropdown')">
                            <i class="ri-computer-line"></i>
                            <span>Lab</span>
                            <i class="ri-arrow-down-s-line"></i>
                        </button>
                        <div id="labDropdown" class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 hidden">
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

                    <a href="admin_reservation.php" class="nav-item">
                        <i class="ri-calendar-check-line"></i>
                        <span>Reservation</span>
                    </a>

                    <!-- Notification Bell -->
                    <div class="relative" x-data="notificationData" x-init="fetchNotifications()">
                        <button @click="open = !open" class="nav-item">
                            <i class="fas fa-bell"></i>
                            <span x-show="unreadCount > 0" 
                                  x-text="unreadCount"
                                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                            </span>
                        </button>
                        
                        <!-- Notification Dropdown -->
                        <div x-show="open" 
                             @click.outside="open = false"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg py-2 z-50">
                            <div class="px-4 py-2 border-b flex justify-between items-center">
                                <h3 class="font-semibold text-gray-700">Notifications</h3>
                                <button @click="markAllAsRead" 
                                        x-show="unreadCount > 0"
                                        class="text-sm text-blue-600 hover:text-blue-800">
                                    Mark all as read
                                </button>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <template x-if="notifications.length === 0">
                                    <div class="px-4 py-3 text-sm text-gray-500 text-center">
                                        No notifications
                                    </div>
                                </template>
                                <template x-for="notification in notifications" :key="notification.NOTIF_ID">
                                    <div @click="readNotification(notification.NOTIF_ID, notification)"
                                         :class="{'bg-blue-50': !notification.IS_READ}"
                                         class="px-4 py-3 hover:bg-gray-50 cursor-pointer">
                                        <div class="flex items-start">
                                            <i :class="getNotificationIcon(notification)" class="mt-1"></i>
                                            <div class="ml-3">
                                                <p class="text-sm text-gray-900" x-text="notification.MESSAGE"></p>
                                                <p class="text-xs text-gray-500 mt-1" x-text="formatDate(notification.CREATED_AT)"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <a href="../logout.php" class="nav-item hover:bg-red-500/20">
                        <i class="ri-logout-box-r-line"></i>
                        <span>Logout</span>
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="px-8 py-8 w-full flex flex-col gap-8">
        <!-- Middle Section: Year Level Chart and Statistics -->
        <div class="flex gap-8">
            <!-- Year Level Chart -->
            <div class="w-1/2">
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30">
                    <!-- Header -->
                    <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                        <i class="fas fa-users text-2xl mr-4 relative z-10"></i>
                        <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Students Year Level</h2>
                    </div>
                    <!-- Stats Cards Row (moved here) -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 px-8 pt-8 pb-4">
                        <div class="bg-gradient-to-br from-blue-50 to-white rounded-xl p-4 shadow-lg border border-blue-100/50 flex flex-col items-center text-center">
                            <div class="mb-2 bg-blue-500/10 p-2 rounded-full">
                                <i class="fas fa-user-graduate text-xl text-blue-600"></i>
                            </div>
                            <span class="text-3xl font-bold text-blue-600 mb-1"><?php echo $totalStudents; ?></span>
                            <span class="text-xs text-blue-600/70 font-medium uppercase tracking-wider">Students Registered</span>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-white rounded-xl p-4 shadow-lg border border-purple-100/50 flex flex-col items-center text-center">
                            <div class="mb-2 bg-purple-500/10 p-2 rounded-full">
                                <i class="fas fa-chair text-xl text-purple-600"></i>
                            </div>
                            <span class="text-3xl font-bold text-purple-600 mb-1"><?php echo $currentSitIns; ?></span>
                            <span class="text-xs text-purple-600/70 font-medium uppercase tracking-wider">Currently Sit-In</span>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-white rounded-xl p-4 shadow-lg border border-green-100/50 flex flex-col items-center text-center">
                            <div class="mb-2 bg-green-500/10 p-2 rounded-full">
                                <i class="fas fa-clipboard-list text-xl text-green-600"></i>
                            </div>
                            <span class="text-3xl font-bold text-green-600 mb-1"><?php echo $totalSitIns; ?></span>
                            <span class="text-xs text-green-600/70 font-medium uppercase tracking-wider">Total Sit-Ins</span>
                        </div>
                    </div>
                    <!-- Chart -->
                    <div class="p-8 pt-4">
                        <div class="h-[400px] bg-white/90 rounded-2xl p-4 shadow-inner border border-blue-100/40">
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

        <!-- Bottom Section: Announcements and Leaderboard -->
        <div class="flex gap-8">
            <!-- Announcements -->
            <div class="w-1/2">
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30">
                    <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                        <i class="fas fa-bullhorn text-2xl mr-4 relative z-10"></i>
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
                                    class="w-full p-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 resize-y min-h-[100px] shadow-inner bg-white/80"
                                ></textarea>
                                <button type="submit" 
                                    class="bg-gradient-to-r from-blue-600 to-blue-500 text-white py-2 px-4 rounded-xl 
                                    hover:shadow-lg transform hover:scale-105 transition-all duration-300 font-medium">
                                    Post Announcement
                                </button>
                            </form>
                        </div>

                        <!-- Announcements List -->
                        <div class="flex-1 overflow-y-auto">
                            <h3 class="font-bold text-gray-700 mb-4">Posted Announcements</h3>
                            <div class="space-y-4 pr-2">
                                <?php if (empty($announcements)): ?>
                                    <p class="text-gray-500 text-center py-4">No announcements available.</p>
                                <?php else: ?>
                                    <?php foreach ($announcements as $announcement): ?>
                                        <div class="bg-white/80 rounded-xl p-4 shadow-md hover:shadow-lg transition-all duration-300 border-l-4 border-gradient-purple" 
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

            <!-- Vertical Leaderboard -->
            <div class="w-1/2">
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30">
                    <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                        <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Student Leaderboard</h2>
                    </div>
                    <div class="p-4 flex flex-col">
                        <?php foreach ($leaderboardData as $index => $student): ?>
                            <div class="mb-4 bg-gradient-to-br <?php 
                                switch($index) {
                                    case 0: echo 'from-yellow-50 to-white border-yellow-200'; break;
                                    case 1: echo 'from-gray-50 to-white border-gray-200'; break;
                                    case 2: echo 'from-orange-50 to-white border-orange-200'; break;
                                    default: echo 'from-purple-50 to-white border-purple-200';
                                }
                            ?> rounded-xl p-4 shadow-lg border transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center space-x-4">
                                    <div class="text-2xl">
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
                                    <div class="w-12 h-12 rounded-full overflow-hidden bg-gradient-to-br from-purple-500 to-indigo-500 flex items-center justify-center">
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
                                    <div>
                                        <div class="font-semibold text-gray-800">
                                            <?php echo htmlspecialchars($student['FIRST_NAME'] . ' ' . $student['LAST_NAME']); ?>
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            <?php echo htmlspecialchars($student['YEAR_LEVEL']); ?>
                                        </div>
                                        <div class="text-sm space-y-2">
                                            <div class="font-bold text-lg text-indigo-600">
                                                <?php 
                                                    $displayPoints = $student['current_points'] + $student['total_points'];
                                                    echo number_format($displayPoints) . ' pts';
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
                            </div>
                        <?php endforeach; ?>
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

        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            dropdown.classList.toggle('hidden');
        }
    </script>
</body>
</html>