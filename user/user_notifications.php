<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$firstName = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Guest';

// Fetch user profile image
if ($userId) {
    $stmt = $conn->prepare("SELECT UPLOAD_IMAGE FROM users WHERE STUD_NUM = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($userImage);
    $stmt->fetch();
    $stmt->close();
    
    $profileImage = !empty($userImage) ? '../images/' . $userImage : "../images/image.jpg";
} else {
    $profileImage = "../images/image.jpg";
}

// Fetch all notifications for this user
$notifications = [];
$query = "SELECT n.*, r.ID as RES_ID, a.ID as ANN_ID, r.STATUS as RES_STATUS, 
                 a.CONTENT as ANNOUNCEMENT_CONTENT, a.CREATED_DATE as ANNOUNCEMENT_DATE
          FROM notification n
          LEFT JOIN reservation r ON n.RESERVATION_ID = r.ID
          LEFT JOIN announcement a ON n.ANNOUNCEMENT_ID = a.ID
          WHERE n.USER_ID = ?
          ORDER BY n.CREATED_AT DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Notifications | Student Dashboard</title>
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
            background: linear-gradient(to right, #ec4899, #a855f7, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-purple-800 to-pink-700 min-h-screen font-poppins">
    <!-- Header -->
    <div class="text-center text-white font-bold text-2xl py-4 relative shadow-lg" style="background: linear-gradient(to bottom right, rgb(49, 46, 129), rgb(107, 33, 168), rgb(190, 24, 93))">
        CCS SIT-IN MONITORING SYSTEM
        <div class="absolute top-4 left-6 cursor-pointer" onclick="toggleNav(this)">
            <div class="bar1 w-8 h-1 bg-white my-1 transition-all duration-300"></div>
            <div class="bar2 w-8 h-1 bg-white my-1 transition-all duration-300"></div>
            <div class="bar3 w-8 h-1 bg-white my-1 transition-all duration-300"></div>
        </div>
    </div>

    <!-- Side Navigation -->
    <div id="mySidenav" class="fixed top-0 left-0 h-screen w-72 bg-gradient-to-b from-indigo-900 to-purple-800 transform -translate-x-full transition-transform duration-300 ease-in-out z-50 shadow-xl overflow-y-auto">
        <div class="absolute top-0 right-0 m-3">
            <button onclick="closeNav()" class="text-white hover:text-pink-200 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="flex flex-col items-center mt-6">
            <div class="relative">
                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile" class="w-20 h-20 rounded-full border-4 border-white/30 object-cover shadow-lg">
                <div class="absolute bottom-0 right-0 bg-green-500 w-3 h-3 rounded-full border-2 border-white"></div>
            </div>
            <p class="text-white font-semibold text-lg mt-2 mb-0"><?php echo htmlspecialchars($firstName); ?></p>
            <p class="text-purple-200 text-xs mb-3">Student</p>
        </div>

        <div class="px-2 py-2">
            <nav class="flex flex-col space-y-1">
                <a href="dashboard.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-home w-5 mr-2 text-center"></i>
                    <span class="font-medium">HOME</span>
                </a>
                <a href="profile.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-user w-5 mr-2 text-center"></i>
                    <span class="font-medium">PROFILE</span>
                </a>
                <a href="edit.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-edit w-5 mr-2 text-center"></i>
                    <span class="font-medium">EDIT</span>
                </a>
                <a href="history.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-history w-5 mr-2 text-center"></i>
                    <span class="font-medium">HISTORY</span>
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
                        
                        <a href="lab_resources.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-desktop w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Resource</span>
                        </a>
                        
                        <a href="lab_schedule.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-calendar-week w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Schedule</span>
                        </a>
                    </div>
                </div>

                <a href="reservation.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-calendar-alt w-5 mr-2 text-center"></i>
                    <span class="font-medium">RESERVATION</span>
                </a>
                
                <a href="user_notifications.php" class="group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-bell w-5 mr-2 text-center"></i>
                        <span class="font-medium">NOTIFICATIONS</span>
                    </div>
                </a>
                
                <div class="border-t border-white/10 my-2"></div>
                <a href="../logout.php" class="group px-3 py-2 text-white/90 hover:bg-red-500/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-sign-out-alt w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">LOG OUT</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto py-8 px-4">
        <div class="w-full lg:w-3/4 xl:w-2/3 mx-auto">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="text-white p-4 flex items-center justify-between relative overflow-hidden" 
                     style="background: linear-gradient(to bottom right, rgb(49, 46, 129), rgb(107, 33, 168), rgb(190, 24, 93))">
                    <div class="flex items-center">
                        <i class="fas fa-bell text-2xl mr-4"></i>
                        <h2 class="text-xl font-bold">Notifications</h2>
                    </div>
                    
                    <?php if (!empty($notifications)): ?>
                    <button id="markAllReadBtn" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                        Mark all as read
                    </button>
                    <?php endif; ?>
                </div>
                
                <div class="p-4">
                    <?php if (empty($notifications)): ?>
                    <div class="text-center py-12">
                        <i class="far fa-bell-slash text-gray-400 text-5xl mb-4"></i>
                        <p class="text-gray-500 text-lg">No notifications yet</p>
                        <p class="text-gray-400 mt-2">When you receive notifications, they will appear here</p>
                    </div>
                    <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="p-4 border rounded-lg <?php echo $notification['IS_READ'] ? 'bg-white' : 'bg-indigo-50'; ?> transition-colors hover:bg-gray-50 notification-item" data-id="<?php echo $notification['NOTIF_ID']; ?>">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mr-3">
                                        <?php if (isset($notification['RESERVATION_ID']) && !empty($notification['RESERVATION_ID'])): ?>
                                            <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-blue-100">
                                                <i class="fas fa-calendar-check text-blue-600"></i>
                                            </span>
                                        <?php elseif (isset($notification['ANNOUNCEMENT_ID']) && !empty($notification['ANNOUNCEMENT_ID'])): ?>
                                            <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-yellow-100">
                                                <i class="fas fa-bullhorn text-yellow-600"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-gray-100">
                                                <i class="fas fa-bell text-gray-600"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-semibold text-gray-900">
                                                <?php 
                                                if (isset($notification['RESERVATION_ID']) && !empty($notification['RESERVATION_ID'])) {
                                                    echo 'Reservation Update';
                                                } elseif (isset($notification['ANNOUNCEMENT_ID']) && !empty($notification['ANNOUNCEMENT_ID'])) {
                                                    echo 'New Announcement';
                                                } else {
                                                    echo 'Notification';
                                                }
                                                ?>
                                            </h3>
                                            
                                            <span class="text-xs text-gray-500">
                                                <?php 
                                                $date = new DateTime($notification['CREATED_AT']);
                                                echo $date->format('M d, Y - h:i A'); 
                                                ?>
                                            </span>
                                        </div>
                                        
                                        <?php if (isset($notification['ANNOUNCEMENT_ID']) && !empty($notification['ANNOUNCEMENT_ID'])): ?>
                                            <p class="mt-1 text-sm text-gray-600">
                                                <?php
                                                $content = $notification['ANNOUNCEMENT_CONTENT'];
                                                $excerpt = strlen($content) > 100 ? substr($content, 0, 100) . '...' : $content;
                                                echo htmlspecialchars($excerpt);
                                                ?>
                                            </p>
                                            <div class="mt-2 text-xs text-gray-500">
                                                <i class="far fa-calendar-alt mr-1"></i>
                                                <?php echo date('Y-M-d', strtotime($notification['ANNOUNCEMENT_DATE'])); ?>
                                                <a href="dashboard.php" class="ml-2 text-indigo-600 hover:text-indigo-800">
                                                    View announcement
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <p class="mt-1 text-sm text-gray-600"><?php echo htmlspecialchars($notification['MESSAGE']); ?></p>
                                            
                                            <?php if (isset($notification['RESERVATION_ID']) && !empty($notification['RESERVATION_ID'])): ?>
                                                <div class="mt-2">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                        <?php
                                                        switch($notification['RES_STATUS']) {
                                                            case 'Approved':
                                                                echo 'bg-green-100 text-green-800';
                                                                break;
                                                            case 'Rejected':
                                                                echo 'bg-red-100 text-red-800';
                                                                break;
                                                            case 'Pending':
                                                                echo 'bg-yellow-100 text-yellow-800';
                                                                break;
                                                            default:
                                                                echo 'bg-gray-100 text-gray-800';
                                                        }
                                                        ?>">
                                                        <?php echo $notification['RES_STATUS'] ?? 'Status Unknown'; ?>
                                                    </span>
                                                    
                                                    <a href="reservation.php" class="ml-2 text-xs text-indigo-600 hover:text-indigo-800">
                                                        View details
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <?php if (!$notification['IS_READ']): ?>
                                            <div class="absolute top-2 right-2 h-2 w-2 bg-blue-500 rounded-full"></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="py-4 px-6 bg-white/95 backdrop-blur-sm mt-8 relative">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500"></div>
        <p class="text-center text-sm text-gray-600">
            &copy; 2025 CCS Sit-in Monitoring System | <span class="gradient-text font-medium">UC - College of Computer Studies</span>
        </p>
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
        
        document.addEventListener('DOMContentLoaded', function() {
            // Mark notification as read when clicked
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function() {
                    const notificationId = this.getAttribute('data-id');
                    
                    fetch('../mark_notification_read.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `notification_id=${notificationId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.classList.remove('bg-indigo-50');
                            this.classList.add('bg-white');
                            
                            // Remove the blue dot
                            const blueDot = this.querySelector('.bg-blue-500');
                            if (blueDot) {
                                blueDot.remove();
                            }
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            });
            
            // Mark all as read button
            const markAllReadBtn = document.getElementById('markAllReadBtn');
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', function() {
                    fetch('../mark_all_user_notifications_read.php', {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update UI to mark all as read
                            document.querySelectorAll('.notification-item').forEach(item => {
                                item.classList.remove('bg-indigo-50');
                                item.classList.add('bg-white');
                                
                                // Remove the blue dot
                                const blueDot = item.querySelector('.bg-blue-500');
                                if (blueDot) {
                                    blueDot.remove();
                                }
                            });
                            
                            // Show success message
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'All notifications marked as read',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            }
        });
    </script>
</body>
</html>
