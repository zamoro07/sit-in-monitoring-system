<?php
session_start();
require '../db.php'; // Updated path

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
    
}

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$firstName = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Guest';

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

// Fetch announcements from the database with DESC order
$announcements = [];
$result = $conn->query("SELECT CONTENT, CREATED_DATE, CREATED_BY FROM announcement 
                       WHERE CREATED_BY = 'ADMIN' 
                       ORDER BY ID DESC, CREATED_DATE DESC"); // Changed ordering to show newest first
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
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
    <title>Dashboard</title>
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
        body {
            font-family: 'Poppins', sans-serif;
            background: white;
            min-height: 100vh;
        }

        /* Header style */
        .text-center {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        }

        /* Add gradient text class for the footer */
        .gradient-text {
            background: linear-gradient(to right, #ec4899, #a855f7, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
        
        /* Custom scrollbar for content */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, rgba(74,105,187,0.7), rgba(205,77,204,0.7));
            border-radius: 10px;
        }
        
        /* Custom animation for announcements */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .announcement-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        /* Custom border for rules */
        .custom-border-left {
            border-left: 3px solid;
            border-image: linear-gradient(to bottom, rgba(74,105,187,1), rgba(205,77,204,1)) 1;
        }
        
        /* Toast notification styles */
        .colored-toast.swal2-icon-success {
            background-color: #10B981 !important;
        }
        .colored-toast.swal2-icon-error {
            background-color: #EF4444 !important;
        }
        .colored-toast {
            color: #fff !important;
        }

        .section-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
            padding: 2rem;
        }

        .section {
            background:linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            border-radius: 0.75rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            width: 100%;
            max-width: 500px;
        }

        .section h2 {
            font-size: 1.5rem;
            font-weight: bold;
            color:rgb(0, 0, 0);
            margin-bottom: 1rem;
            text-align: center;
        }

        .section-content {
            max-height: 60vh;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .section-content::-webkit-scrollbar {
            width: 6px;
        }

        .section-content::-webkit-scrollbar-thumb {
            background: #2563eb;
            border-radius: 3px;
        }

        .announcement, .rule {
            background: #f9fafb;
            border-left: 4px solid #2563eb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .announcement p, .rule p {
            margin: 0;
            color: #333;
        }
    </style>
</head>
<body class="bg- min-h-screen font-poppins">
    <!-- Header -->
    <div class="text-center text-white font-bold text-2xl py-4 relative shadow-lg">
        CCS SIT-IN MONITORING SYSTEM
        <div class="absolute top-4 left-6 cursor-pointer" onclick="toggleNav(this)">
            <div class="bar1 w-8 h-1 bg-white my-1 transition-all duration-300"></div>
            <div class="bar2 w-8 h-1 bg-white my-1 transition-all duration-300"></div>
            <div class="bar3 w-8 h-1 bg-white my-1 transition-all duration-300"></div>
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
                                <div class="flex items-center">
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
                        <a href="user_notifications.php" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                            View all notifications
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Side Navigation -->
    <div id="mySidenav" class="fixed top-0 left-0 h-screen w-72 transform -translate-x-full transition-transform duration-300 ease-in-out z-50 shadow-xl overflow-y-auto" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
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
                <a href="dashboard.php" class="group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center">
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
                <div class="border-t border-white/10 my-2"></div>
                <a href="../logout.php" class="group px-3 py-2 text-white/90 hover:bg-red-500/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-sign-out-alt w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">LOG OUT</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="section-container">
        <!-- Announcements Section -->
        <div class="section">
            <h2>Announcements</h2>
            <div class="section-content">
                <?php if (empty($announcements)): ?>
                    <p>No announcements available.</p>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="announcement">
                            <p><strong><?php echo htmlspecialchars($announcement['CREATED_BY']); ?></strong> - <?php echo date('Y-M-d', strtotime($announcement['CREATED_DATE'])); ?></p>
                            <p><?php echo htmlspecialchars($announcement['CONTENT']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rules and Regulations Section -->
        <div class="section">
            <h2>Rules and Regulations</h2>
            <div class="section-content">
                <ol>
                    <?php
                    $rules = [
                        "Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkmans and other personal pieces of equipment must be switched off.",
                        "Games are not allowed inside the lab. This includes computer-related games, card games and other games that may disturb the operation of the lab.",
                        "Surfing the Internet is allowed only with the permission of the instructor. Downloading and installing of software are strictly prohibited.",
                        "Getting access to other websites not related to the course (especially pornographic and illicit sites) is strictly prohibited.",
                        "Deleting computer files and changing the set-up of the computer is a major offense.",
                        "Observe computer time usage carefully. A fifteen-minute allowance is given for each use. Otherwise, the unit will be given to those who wish to \"sit-in\".",
                        "Observe proper decorum while inside the laboratory.",
                        "Chewing gum, eating, drinking, smoking, and other forms of vandalism are prohibited inside the lab.",
                        "Anyone causing a continual disturbance will be asked to leave the lab. Acts or gestures offensive to the members of the community, including public display of physical intimacy, are not tolerated.",
                        "Persons exhibiting hostile or threatening behavior such as yelling, swearing, or disregarding requests made by lab personnel will be asked to leave the lab.",
                        "For serious offense, the lab personnel may call the Civil Security Office (CSU) for assistance.",
                        "Any technical problem or difficulty must be addressed to the laboratory supervisor, student assistant or instructor immediately."
                    ];
                    
                    foreach ($rules as $rule):
                    ?>
                        <li class="rule">
                            <p><?php echo $rule; ?></p>
                        </li>
                    <?php endforeach; ?>
                </ol>
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

        // Add this to your existing script section
        document.addEventListener('DOMContentLoaded', function() {
            // Format dates if needed and apply animations
            const announcementItems = document.querySelectorAll('.announcement-fade-in');
            
            // Stagger animation effect
            announcementItems.forEach((item, index) => {
                item.style.opacity = '0';
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 150);
            });
        });
        
        // Notifications functions
        document.addEventListener('alpine:init', () => {
            Alpine.data('notificationData', () => ({
                open: false,
                notifications: [],
                unreadCount: 0,
                
                fetchNotifications() {
                    fetch('../fetch_user_notifications.php')
                        .then(response => response.json())
                        .then(data => {
                            this.notifications = data.notifications;
                            this.unreadCount = data.unread_count;
                        })
                        .catch(error => console.error('Error fetching notifications:', error));
                },
                
                readNotification(id, notification) {
                    fetch('../mark_notification_read.php', {
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
                                window.location.href = 'reservation.php';
                            } else if (notification.ANNOUNCEMENT_ID) {
                                // For announcement notifications, just scroll to the announcements section
                                const announcementsSection = document.querySelector('.announcements-section');
                                if (announcementsSection) {
                                    announcementsSection.scrollIntoView({ behavior: 'smooth' });
                                } else {
                                    // If no specific section, just reload to show fresh announcements
                                    window.location.reload();
                                }
                            }
                        }
                    })
                    .catch(error => console.error('Error marking notification as read:', error));
                },
                
                markAllAsRead() {
                    fetch('../mark_all_user_notifications_read.php', {
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
                        if (notification.MESSAGE && notification.MESSAGE.includes('approved')) {
                            return 'Reservation Approved';
                        } else if (notification.MESSAGE && notification.MESSAGE.includes('rejected')) {
                            return 'Reservation Rejected';
                        } else {
                            return 'Reservation Update';
                        }
                    } else if (notification.ANNOUNCEMENT_ID) {
                        return 'New Announcement';
                    } else {
                        return 'Notification';
                    }
                },
                
                getNotificationIcon(notification) {
                    if (notification.RESERVATION_ID) {
                        return 'fas fa-calendar-check text-blue-500';
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

        // Add this to initialize notifications when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Get the Alpine.js component instance
            const notificationComponent = document.querySelector('[x-data="notificationData"]').__x.$data;
            // Fetch notifications initially
            notificationComponent.fetchNotifications();
            
            // Set up a poll to check for new notifications every 30 seconds
            setInterval(() => {
            notificationComponent.fetchNotifications();
            }, 30000);
            
            // Format dates if needed and apply animations
            const announcementItems = document.querySelectorAll('.announcement-fade-in');
            
            // Stagger animation effect
            announcementItems.forEach((item, index) => {
            item.style.opacity = '0';
            setTimeout(() => {
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, index * 150);
            });
        });
    </script>
</body>
</html>