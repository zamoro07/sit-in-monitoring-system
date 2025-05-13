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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        /* Header nav items */
        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .nav-item.active {
            background-color: rgba(255, 255, 255, 0.3);
        }
        .nav-item i {
            width: 1.25rem;
            text-align: center;
            margin-right: 0.75rem;
        }
        /* Mobile menu styles */
        @media (max-width: 768px) {
            .nav-item span {
                display: none;
            }
            .nav-item i {
                margin-right: 0;
            }
        }
        /* Responsive adjustments */
        @media (max-width: 1024px) {
            header .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br min-h-screen font-poppins" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-lg py-4 px-6">
        <div class="container mx-auto flex items-center justify-between">
            <!-- Logo/Title Section -->
            <div class="flex items-center">
                <h1 class="text-2xl font-bold">CCS SIT-IN MONITORING SYSTEM</h1>
            </div>

            <!-- Navigation Items -->
            <div class="flex items-center space-x-6">
                <!-- Nav Links -->
                <nav class="hidden md:flex items-center space-x-4">
                    <a href="dashboard.php" class="nav-item<?php if(basename($_SERVER['PHP_SELF']) == 'dashboard.php') echo ' active'; ?>">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                    
                    <a href="profile.php" class="nav-item<?php if(basename($_SERVER['PHP_SELF']) == 'profile.php') echo ' active'; ?>">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>

                    <a href="edit.php" class="nav-item<?php if(basename($_SERVER['PHP_SELF']) == 'edit.php') echo ' active'; ?>">
                        <i class="fas fa-edit"></i>
                        <span>Edit</span>
                    </a>

                    <a href="history.php" class="nav-item<?php if(basename($_SERVER['PHP_SELF']) == 'history.php') echo ' active'; ?>">
                        <i class="fas fa-history"></i>
                        <span>History</span>
                    </a>

                    <!-- View Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="nav-item">
                            <i class="fas fa-eye"></i>
                            <span>View</span>
                            <i class="fas fa-chevron-down ml-1 text-sm"></i>
                        </button>
                        
                        <div x-show="open" 
                             @click.outside="open = false"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-50">
                            <a href="lab_resources.php" class="block px-4 py-2 text-gray-800 hover:bg-blue-50">
                                <i class="fas fa-desktop mr-2"></i>Lab Resource
                            </a>
                            <a href="lab_schedule.php" class="block px-4 py-2 text-gray-800 hover:bg-blue-50">
                                <i class="fas fa-calendar-week mr-2"></i>Lab Schedule
                            </a>
                        </div>
                    </div>

                    <a href="reservation.php" class="nav-item<?php if(basename($_SERVER['PHP_SELF']) == 'reservation.php') echo ' active'; ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Reservation</span>
                    </a>

                    <!-- User Profile -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2">
                            <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile" 
                                 class="w-8 h-8 rounded-full object-cover border-2 border-white/30">
                            <span class="hidden md:inline-block"><?php echo htmlspecialchars($firstName); ?></span>
                        </button>
                        
                        <div x-show="open" 
                             @click.outside="open = false"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-50">
                            <a href="../logout.php" class="block px-4 py-2 text-gray-800 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- Mobile Menu Button -->
                <button class="md:hidden" @click="mobileMenu = !mobileMenu">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </header>

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