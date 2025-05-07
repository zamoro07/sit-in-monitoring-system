<?php
session_start();
require '../db.php';

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

// Fetch resources from database
$resources = [];
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($searchQuery)) {
    // If search query exists, filter resources
    $search = "%$searchQuery%";
    $result = $conn->prepare("SELECT RESOURCES_ID, RESOURCES_NAME, PROFESSOR, DESCRIPTION, RESOURCES_LINK, RESOURCES_IMAGE, CREATED_AT 
                             FROM resources 
                             WHERE RESOURCES_NAME LIKE ? OR PROFESSOR LIKE ? OR DESCRIPTION LIKE ? 
                             ORDER BY CREATED_AT DESC");
    $result->bind_param("sss", $search, $search, $search);
    $result->execute();
    $fetchResult = $result->get_result();
} else {
    // Otherwise fetch all resources
    $fetchResult = $conn->query("SELECT RESOURCES_ID, RESOURCES_NAME, PROFESSOR, DESCRIPTION, RESOURCES_LINK, RESOURCES_IMAGE, CREATED_AT 
                               FROM resources ORDER BY CREATED_AT DESC");
}

if ($fetchResult && $fetchResult->num_rows > 0) {
    while ($row = $fetchResult->fetch_assoc()) {
        $resources[] = $row;
    }
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
    <link rel="stylesheet" href="../css/admin_styles.css">
    <title>Lab Resources</title>
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
        
        /* Custom scrollbar for content */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #2563eb, #3b82f6);
            border-radius: 10px;
        }
        
        /* Update button gradients */
        .btn-gradient {
            background: linear-gradient(to bottom right, #2563eb, #3b82f6);
        }
        
        /* Update resource cards gradient borders */
        .gradient-border {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        }
    </style>
</head>
<body class="min-h-screen font-poppins" style="background: white">
    <!-- Header -->
    <div class="text-center text-white font-bold text-2xl py-4 relative shadow-lg" 
         style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
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
                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile" class="w-20 h-20 rounded-full border-4 border-white/30 object-cover shadow-lg">
                <div class="absolute bottom-0 right-0 bg-green-500 w-3 h-3 rounded-full border-2 border-white"></div>
            </div>
            <p class="text-white font-semibold text-lg mt-2 mb-0"><?php echo htmlspecialchars($firstName); ?></p>
            <p class="text-purple-200 text-xs mb-3">Student</p>
        </div>

        <div class="px-2 py-2">
            <nav class="flex flex-col space-y-1">
                <a href="dashboard.php" class="group px-3 py-2 text-white/90 hover:bg-blue-600 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-home w-5 mr-2 text-center"></i>
                    <span class="font-medium">HOME</span>
                </a>
                <a href="profile.php" class="group px-3 py-2 text-white/90 hover:bg-blue-600 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-user w-5 mr-2 text-center"></i>
                    <span class="font-medium">PROFILE</span>
                </a>
                <a href="edit.php" class="group px-3 py-2 text-white/90 hover:bg-blue-600 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-edit w-5 mr-2 text-center"></i>
                    <span class="font-medium">EDIT</span>
                </a>
                <a href="history.php" class="group px-3 py-2 text-white/90 hover:bg-blue-600 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-history w-5 mr-2 text-center"></i>
                    <span class="font-medium">HISTORY</span>
                </a>
                
                <!-- VIEW Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center justify-between">
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
                        
                        <a href="lab_resources.php" class="group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-desktop w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Resources</span>
                        </a>
                        
                        <a href="lab_schedule.php" class="group px-3 py-2 text-white/90 hover:bg-blue-600 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-calendar-week w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Schedule</span>
                        </a>
                    </div>
                </div>

                <a href="reservation.php" class="group px-3 py-2 text-white/90 hover:bg-blue-600 rounded-lg transition-all duration-200 flex items-center">
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
    <div class="container mx-auto px-4 py-8">
        <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm border border-white/30">
            <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                <i class="fas fa-box-open text-2xl mr-4 relative z-10"></i>
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Lab Resources Hub</h2>
            </div>
            
            <div class="p-6">
                <!-- Modern Futuristic Header -->
                <div class="flex flex-wrap items-center justify-between mb-8">
                    <div class="flex items-center">
                        <div class="relative mr-3">
                            <div class="absolute inset-0 bg-indigo-600 rounded-lg blur-lg opacity-30"></div>
                            <div class="relative bg-gradient-to-br from-indigo-600 to-purple-700 p-3 rounded-lg shadow-lg">
                            </div>
                        </div>
                        <div>
                            <h1 class="text-2xl font-extrabold">
                                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-700">
                                    Resource Hub
                                </span>
                            </h1>
                        </div>
                    </div>
                    
                    <div class="flex items-center mt-4 md:mt-0">
                        <!-- Modern Search Field -->
                        <form action="" method="GET" class="relative">
                            <input type="text" name="search" placeholder="Find resources..." 
                                   value="<?php echo htmlspecialchars($searchQuery); ?>"
                                   class="pl-10 pr-4 py-2.5 w-60 rounded-full border-0 bg-white/80 backdrop-blur-md
                                         shadow-inner focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-indigo-500">
                                <i class="fas fa-search"></i>
                            </div>
                            <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-indigo-500 hover:text-indigo-700">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Display resources using card grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                    <?php if (empty($resources)): ?>
                        <div class="col-span-full text-center py-12">
                            <div class="bg-indigo-50 rounded-xl p-8 max-w-md mx-auto">
                                <i class="fas fa-folder-open text-6xl text-indigo-300 mb-4"></i>
                                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Resources Found</h3>
                                <?php if (!empty($searchQuery)): ?>
                                    <p class="text-gray-600">We couldn't find any resources matching "<?php echo htmlspecialchars($searchQuery); ?>"</p>
                                    <a href="lab_resources.php" class="mt-4 inline-block bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition-colors">
                                        <i class="fas fa-arrow-left mr-2"></i> Back to All Resources
                                    </a>
                                <?php else: ?>
                                    <p class="text-gray-600">Check Later</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($resources as $index => $resource): ?>
                            <?php 
                            // Determine card style based on index for variety
                            $cardClass = "";
                            $cardType = $index % 4;
                            switch($cardType) {
                                case 0:
                                    $cardClass = "resource-card-3d";
                                    break;
                                case 1:
                                    $cardClass = "glass-card";
                                    break;
                                case 2:
                                    $cardClass = "gradient-border";
                                    break;
                                case 3:
                                    $cardClass = "resource-card-float";
                                    break;
                            }
                            ?>
                            <div class="<?php echo $cardClass; ?> rounded-xl overflow-hidden shadow-lg group relative">
                                <?php if ($cardType === 0): ?>
                                    <div class="card-shine rounded-xl"></div>
                                <?php endif; ?>
                                
                                <div class="h-40 overflow-hidden">
                                    <?php if ($resource['RESOURCES_IMAGE']): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($resource['RESOURCES_IMAGE']); ?>" 
                                             alt="<?php echo htmlspecialchars($resource['RESOURCES_NAME']); ?>" 
                                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                                            <i class="fas fa-book-open text-4xl text-indigo-300"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="p-5 bg-white">
                                    <h3 class="font-bold text-gray-800 text-lg mb-1"><?php echo htmlspecialchars($resource['RESOURCES_NAME']); ?></h3>
                                    
                                    <!-- Display professor name if available -->
                                    <?php if (!empty($resource['PROFESSOR'])): ?>
                                    <div class="text-sm text-gray-500 mb-2">
                                        <i class="fas fa-user-tie mr-1 text-indigo-500"></i> 
                                        <?php echo htmlspecialchars($resource['PROFESSOR']); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <p class="text-gray-600 text-sm mb-4 line-clamp-2 <?php echo $cardType === 3 ? 'floating-label' : ''; ?>">
                                        <?php echo htmlspecialchars($resource['DESCRIPTION']); ?>
                                    </p>
                                    
                                    <div class="mt-auto flex justify-between items-center">
                                        <span class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($resource['CREATED_AT'])); ?></span>
                                        <a href="<?php echo htmlspecialchars($resource['RESOURCES_LINK']); ?>" target="_blank" 
                                           class="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center group/link">
                                            <span class="group-hover/link:mr-2 transition-all duration-300">Open</span>
                                            <i class="fas fa-external-link-alt transform group-hover/link:translate-x-1 transition-transform"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
        
        // Add 3D card effect similar to admin page
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.resource-card-3d');
            
            cards.forEach(card => {
                card.addEventListener('mousemove', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    
                    const angleY = (x - centerX) / 20;
                    const angleX = (centerY - y) / 20;
                    
                    this.style.transform = `rotateY(${angleY}deg) rotateX(${angleX}deg)`;
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'rotateY(0deg) rotateX(0deg)';
                });
            });
        });
    </script>
</body>
</html>
