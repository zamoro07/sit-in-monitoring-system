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
    $stmt = $conn->prepare("SELECT IDNO, LAST_NAME, FIRST_NAME, MID_NAME, COURSE, YEAR_LEVEL, EMAIL, ADDRESS, UPLOAD_IMAGE, SESSION FROM users WHERE STUD_NUM = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($idNo, $lastName, $dbFirstName, $midName, $course, $yearLevel, $email, $address, $userImage, $session);
    $stmt->fetch();
    $stmt->close();
    
    $profileImage = !empty($userImage) ? '../images/' . $userImage : "../images/image.jpg";
    $fullName = trim("$dbFirstName $midName $lastName");
} else {
    $profileImage = "../images/image.jpg";
    $idNo = '';
    $fullName = '';
    $yearLevel = '';
    $course = '';
    $email = '';
    $address = '';
    $session = '';
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
    <title>Profile</title>
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
        /* Update existing styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: #ffffff !important;
            min-height: 100vh;
        }

        /* Student Info Section - add solid black border */
        .w-11\/12 {
            border: 2px solid #000000 !important;
        }

        /* Keep existing hover-row styles */
        .hover-row {
            border-left: 4px solid #2563eb !important;
        }
        
        /* Add gradient text class for the footer */
        .gradient-text {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
        
        /* New hover effect for table rows */
        .hover-row:hover {
            background-color: rgba(99, 102, 241, 0.05);
            transform: translateX(4px);
            transition: all 0.3s ease;
        }
        
        .hover-row {
            border-left: 4px solid #2563eb !important;
        }
        
        .border-indigo-400 {
            border-color: #2563eb !important;
        }
        
        .border-purple-400 {
            border-color: #2563eb !important;
        }
        
        .text-indigo-400 {
            color: #2563eb !important;
        }
        
        .text-purple-400 {
            color: #2563eb !important;
        }
    </style>
</head>
<body class="bg-gradient-to-br min-h-screen font-poppins" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
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
                <a href="dashboard.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-home w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">HOME</span>
                </a>
                <a href="profile.php" class="group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-user w-5 mr-2 text-center"></i>
                    <span class="font-medium">PROFILE</span>
                </a>
                <a href="edit.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-edit w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">EDIT</span>
                </a>
                <a href="history.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-history w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">HISTORY</span>
                </a>
                
                <!-- VIEW Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center justify-between">
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
                        
                        <a href="lab_resources.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-desktop w-5 mr-2 text-center"></i>
                            <span class="font-medium group-hover:translate-x-1 transition-transform">Lab Resource</span>
                        </a>
                        
                        <a href="lab_schedule.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-calendar-week w-5 mr-2 text-center"></i>
                            <span class="font-medium group-hover:translate-x-1 transition-transform">Lab Schedule</span>
                        </a>
                    </div>
                </div>

                <a href="reservation.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-calendar-alt w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">RESERVATION</span>
                </a>
                
                <div class="border-t border-white/10 my-2"></div>
                
                <a href="../logout.php" class="group px-3 py-2 text-white/90 hover:bg-red-500/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-sign-out-alt w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">LOG OUT</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Student Info Section -->
    <div class="w-11/12 md:w-8/12 mx-auto my-8 bg-white rounded-lg shadow-lg overflow-hidden border-2 border-black">
        <!-- Header section -->
        <div class="text-white p-6 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
            <h2 class="text-2xl font-bold tracking-wider uppercase relative z-10">Student Information</h2>
        </div>

        <!-- Profile section -->
        <div class="p-8">
            <!-- Profile image and basic info -->
            <div class="flex flex-col items-center mb-8">
                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-blue-100 mb-4">
                    <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Student Image" class="w-full h-full object-cover">
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($fullName); ?></h3>
                <p class="text-blue-600 font-medium mb-2"><?php echo htmlspecialchars($course); ?></p>
                <p class="text-gray-600"><?php echo htmlspecialchars($email); ?></p>
            </div>

            <!-- Information Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                <!-- Left Column -->
                <div class="space-y-4">
                    <!-- ID Number Card -->
                    <div class="hover-row rounded-lg p-6 border-l-4 border-blue-500 bg-gray-50 transition-all duration-300">
                        <div class="flex items-center space-x-3">
                            <div class="flex-1">
                                <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">ID NUMBER</p>
                                <p class="font-semibold text-xl text-gray-800"><?php echo htmlspecialchars($idNo); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Year Level Card -->
                    <div class="hover-row rounded-lg p-6 border-l-4 border-blue-500 bg-gray-50 transition-all duration-300">
                        <div class="flex items-center space-x-3">
                            <div class="flex-1">
                                <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">YEAR LEVEL</p>
                                <p class="font-semibold text-xl text-gray-800"><?php echo htmlspecialchars($yearLevel); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <!-- Address Card -->
                    <div class="hover-row rounded-lg p-6 border-l-4 border-blue-500 bg-gray-50 transition-all duration-300">
                        <div class="flex items-center space-x-3">
                            <div class="flex-1">
                                <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">ADDRESS</p>
                                <p class="font-semibold text-xl text-gray-800"><?php echo htmlspecialchars($address); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Session Card -->
                    <div class="hover-row rounded-lg p-6 border-l-4 border-blue-500 bg-gray-50 transition-all duration-300">
                        <div class="flex items-center space-x-3">
                            <div class="flex-1">
                                <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">SESSION</p>
                                <p class="font-semibold text-xl text-gray-800"><?php echo htmlspecialchars($session); ?></p>
                            </div>
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
    </script>
</body>
</html>