<?php
session_start();
require '../db.php'; // Add database connection

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Pagination variables
$entriesPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $entriesPerPage;

// Get total number of notifications for admin
$totalQuery = "SELECT COUNT(*) as total FROM notification WHERE USER_ID IS NULL OR USER_ID = 0";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalNotifications = $totalRow['total'];

// Fetch notifications with pagination
$query = "SELECT * FROM notification 
          WHERE USER_ID IS NULL OR USER_ID = 0
          ORDER BY CREATED_AT DESC 
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $entriesPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Calculate pagination numbers
$totalPages = ceil($totalNotifications / $entriesPerPage);
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
    <title>Notifications | Admin</title>
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
            background: linear-gradient(to right, #ec4899, #a855f7, #6366f1);
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
                <img src="../images/image.jpg" alt="Logo" class="w-20 h-20 rounded-full border-4 border-white/30 object-cover shadow-lg">
                <div class="absolute bottom-0 right-0 bg-green-500 w-3 h-3 rounded-full border-2 border-white"></div>
            </div>
            <p class="text-white font-semibold text-lg mt-2 mb-0">Admin</p>
            <p class="text-purple-200 text-xs mb-3">Administrator</p>
        </div>

        <div class="px-2 py-2">
            <nav class="flex flex-col space-y-1">
                <a href="admin_dashboard.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-home w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">HOME</span>
                </a>
                <!-- Include the rest of your sidebar navigation -->
                <!-- ... -->
                <div class="border-t border-white/10 my-2"></div>
                
                <a href="../logout.php" class="group px-3 py-2 text-white/90 hover:bg-red-500/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-sign-out-alt w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">LOG OUT</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto p-4 md:p-8">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(to bottom right, rgb(49, 46, 129), rgb(107, 33, 168), rgb(190, 24, 93))">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                <i class="fas fa-bell text-2xl mr-4 relative z-10"></i>
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">All Notifications</h2>
            </div>
            
            <div class="p-6">
                <!-- Controls -->
                <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                    <div class="flex items-center mb-4 md:mb-0">
                        <label class="text-gray-600 mr-2">Show</label>
                        <select id="entriesPerPage" onchange="changeEntriesPerPage(this.value)" class="border border-gray-300 rounded px-2 py-1">
                            <option value="10" <?php echo $entriesPerPage == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo $entriesPerPage == 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $entriesPerPage == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $entriesPerPage == 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                        <span class="text-gray-600 ml-2">entries</span>
                    </div>
                    
                    <button onclick="markAllAsRead()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded flex items-center">
                        <i class="fas fa-check-double mr-2"></i> Mark all as read
                    </button>
                </div>
                
                <!-- Notifications List -->
                <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                    <?php if ($result->num_rows === 0): ?>
                    <div class="flex flex-col items-center justify-center py-12 px-4 text-gray-500">
                        <i class="far fa-bell-slash text-5xl mb-4 text-gray-300"></i>
                        <p class="text-xl font-medium">No notifications</p>
                        <p class="text-sm mt-2 text-center">You don't have any notifications at the moment</p>
                    </div>
                    <?php else: ?>
                        <div class="divide-y divide-gray-200">
                            <?php while ($notification = $result->fetch_assoc()): 
                                $notificationType = 'System';
                                $notificationIcon = 'fas fa-bell';
                                $iconBgColor = 'bg-gray-100';
                                $iconTextColor = 'text-gray-500';
                                $redirectUrl = '#';
                                
                                if (!empty($notification['RESERVATION_ID'])) {
                                    $notificationType = 'Reservation';
                                    $notificationIcon = 'fas fa-calendar-check';
                                    $iconBgColor = 'bg-blue-100';
                                    $iconTextColor = 'text-blue-500';
                                    $redirectUrl = 'admin_reservation.php';
                                } elseif (!empty($notification['FEEDBACK_ID'])) {
                                    $notificationType = 'Feedback';
                                    $notificationIcon = 'fas fa-comment-alt';
                                    $iconBgColor = 'bg-purple-100';
                                    $iconTextColor = 'text-purple-500';
                                    $redirectUrl = 'admin_feedback.php';
                                } elseif (!empty($notification['ANNOUNCEMENT_ID'])) {
                                    $notificationType = 'Announcement';
                                    $notificationIcon = 'fas fa-bullhorn';
                                    $iconBgColor = 'bg-yellow-100';
                                    $iconTextColor = 'text-yellow-500';
                                    $redirectUrl = 'admin_dashboard.php';
                                }
                            ?>
                                <div class="p-4 <?php echo $notification['IS_READ'] ? 'bg-white' : 'bg-indigo-50'; ?> hover:bg-gray-50 transition-colors">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mr-4">
                                            <div class="h-10 w-10 rounded-full <?php echo $iconBgColor; ?> flex items-center justify-center">
                                                <i class="<?php echo $notificationIcon . ' ' . $iconTextColor; ?>"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-start">
                                                <h3 class="font-medium text-gray-900"><?php echo $notificationType; ?> Notification</h3>
                                                <span class="text-xs text-gray-500"><?php echo date('M d, Y h:i A', strtotime($notification['CREATED_AT'])); ?></span>
                                            </div>
                                            <p class="mt-1 text-sm text-gray-600"><?php echo htmlspecialchars($notification['MESSAGE']); ?></p>
                                            <div class="mt-2 flex justify-between items-center">
                                                <div class="flex space-x-2">
                                                    <?php if ($redirectUrl !== '#'): ?>
                                                        <a href="<?php echo $redirectUrl; ?>" class="text-xs text-blue-600 hover:text-blue-800">View Details</a>
                                                    <?php endif; ?>
                                                    
                                                    <button onclick="markAsRead(<?php echo $notification['NOTIF_ID']; ?>)" 
                                                            class="text-xs text-gray-500 hover:text-gray-700"
                                                            <?php echo $notification['IS_READ'] ? 'disabled' : ''; ?>>
                                                        <?php echo $notification['IS_READ'] ? 'Read' : 'Mark as read'; ?>
                                                    </button>
                                                </div>
                                                <?php if (!$notification['IS_READ']): ?>
                                                    <span class="h-2 w-2 bg-blue-500 rounded-full"></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="flex justify-center mt-6">
                        <nav class="inline-flex rounded-md shadow-sm">
                            <a href="?page=1&entries=<?php echo $entriesPerPage; ?>" 
                               class="px-3 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                First
                            </a>
                            
                            <?php if ($currentPage > 1): ?>
                                <a href="?page=<?php echo $currentPage - 1; ?>&entries=<?php echo $entriesPerPage; ?>" 
                                   class="px-3 py-2 border-t border-b border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    Prev
                                </a>
                            <?php endif; ?>
                            
                            <?php
                            // Display page numbers
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                            
                            for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&entries=<?php echo $entriesPerPage; ?>" 
                                   class="px-3 py-2 border border-gray-300 bg-white text-sm font-medium 
                                          <?php echo $i === $currentPage ? 'text-indigo-600 bg-indigo-50' : 'text-gray-500 hover:bg-gray-50'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($currentPage < $totalPages): ?>
                                <a href="?page=<?php echo $currentPage + 1; ?>&entries=<?php echo $entriesPerPage; ?>" 
                                   class="px-3 py-2 border-t border-b border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    Next
                                </a>
                            <?php endif; ?>
                            
                            <a href="?page=<?php echo $totalPages; ?>&entries=<?php echo $entriesPerPage; ?>" 
                               class="px-3 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                Last
                            </a>
                        </nav>
                    </div>
                <?php endif; ?>
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
        function toggleNav() {
            document.getElementById("mySidenav").classList.toggle("-translate-x-0");
            document.getElementById("mySidenav").classList.toggle("-translate-x-full");
        }

        function closeNav() {
            document.getElementById("mySidenav").classList.remove("-translate-x-0");
            document.getElementById("mySidenav").classList.add("-translate-x-full");
        }
        
        function changeEntriesPerPage(entries) {
            window.location.href = `?entries=${entries}&page=1`;
        }
        
        function markAsRead(id) {
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
                    // Refresh the page to show updated state
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
                
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
                    title: 'Failed to mark notification as read',
                    background: '#EF4444'
                });
            });
        }
        
        function markAllAsRead() {
            fetch('mark_all_notifications_read.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
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
                    }).then(() => {
                        // Refresh the page to show updated state
                        window.location.reload();
                    });
                }
            })
            .catch(error => {
                console.error('Error marking all notifications as read:', error);
                
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
                    title: 'Failed to mark all notifications as read',
                    background: '#EF4444'
                });
            });
        }
    </script>
</body>
</html>
