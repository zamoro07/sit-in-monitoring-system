<?php
session_start();
require '../db.php';

// Check if admin is not logged in, redirect to login page
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Add time-in handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['time_in'])) {
    $idno = $_POST['idno'];
    $fullName = $_POST['full_name'];
    $purpose = $_POST['purpose'];
    $laboratory = $_POST['laboratory'];
    
    // Check if student already has an active session
    $checkStmt = $conn->prepare("SELECT * FROM curr_sitin WHERE IDNO = ? AND STATUS = 'Active'");
    $checkStmt->bind_param("i", $idno);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['toast'] = [
            'icon' => 'error',
            'title' => 'Student already has an active sit-in session.',
            'background' => '#EF4444'
        ];
    } else {
        // Insert new sit-in record
        $stmt = $conn->prepare("INSERT INTO curr_sitin (IDNO, FULL_NAME, PURPOSE, LABORATORY, TIME_IN, DATE, STATUS) VALUES (?, ?, ?, ?, NOW(), CURDATE(), 'Active')");
        $stmt->bind_param("isss", $idno, $fullName, $purpose, $laboratory);
        
        if ($stmt->execute()) {
            $_SESSION['toast'] = [
                'icon' => 'success',
                'title' => 'Time-in recorded successfully',
                'background' => '#10B981'
            ];
        } else {
            $_SESSION['toast'] = [
                'icon' => 'error',
                'title' => 'Error recording time-in',
                'background' => '#EF4444'
            ];
        }
    }
    header("Location: admin_search.php");
    exit();
}

// Only fetch student data when search button is clicked via POST
$student = null;
$searched = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search']) && !empty($_POST['search'])) {
    $searched = true;
    $stmt = $conn->prepare("SELECT * FROM users WHERE IDNO = ?");
    $stmt->bind_param("s", $_POST['search']);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
}

// Add this after your session checks, before the HTML
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
                icon: '<?php echo $_SESSION['toast']['icon']; ?>',
                title: '<?php echo $_SESSION['toast']['title']; ?>',
                background: '<?php echo $_SESSION['toast']['background']; ?>'
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
    <title>Admin Search</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../logo/ccs.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <!-- Header Menu Button -->
        <div class="absolute top-4 left-6 cursor-pointer text-white font-medium" onclick="toggleNav(this)">
            Menu
        </div>
    </div>

    <!-- Side Navigation -->
    <div id="mySidenav" class="fixed top-0 left-0 h-screen w-72 bg-gradient-to-b from-blue-600 to-blue-800 transform -translate-x-full transition-transform duration-300 ease-in-out z-50 shadow-xl overflow-y-auto">
        <div class="absolute top-0 right-0 m-3">
            <button onclick="closeNav()" class="text-white hover:text-gray-200 transition-colors px-3 py-1">
                Close
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
                <a href="admin_dashboard.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200">
                    <span class="font-medium">HOME</span>
                </a>
                <a href="admin_search.php" class="group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200">
                    <span class="font-medium">SEARCH</span>
                </a>
                <a href="admin_sitin.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200">
                    <span class="font-medium">SIT-IN</span>
                </a>
                
                <!-- VIEW Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center justify-between">
                        <span class="font-medium">VIEW</span>
                        <span class="text-sm" :class="{ 'rotate-180': open }">▼</span>
                    </button>
                    
                    <div x-show="open" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                        class="pl-7 mt-2 space-y-1">
                        
                        <a href="admin_sitinrec.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200">
                            <span class="font-medium">Sit-in Records</span>
                        </a>
                        
                        <a href="admin_studlist.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200">
                            <span class="font-medium">List of Students</span>
                        </a>
                        
                        <a href="admin_feedback.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200">
                            <span class="font-medium">Feedbacks</span>
                        </a>
                        
                        <a href="#" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200">
                            <span class="font-medium">Daily Analytics</span>
                        </a>
                    </div>
                </div>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center justify-between">
                        <span class="font-medium">LAB</span>
                        <span class="text-sm" :class="{ 'rotate-180': open }">▼</span>
                    </button>
                    
                    <div x-show="open" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                        class="pl-7 mt-2 space-y-1">
                        
                        <a href="admin_resources.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200">
                            <span class="font-medium">Resources</span>
                        </a>
                        
                        <a href="admin_lab_schedule.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200">
                            <span class="font-medium">Lab Schedule</span>
                        </a>
                        
                        <a href="admin_lab_usage.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200">
                            <span class="font-medium">Lab Usage Point</span>
                        </a>
                    </div>
                </div>

                <a href="admin_reports.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200">
                    <span class="font-medium">SIT-IN REPORT</span>
                </a>

                <a href="admin_reservation.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200">
                    <span class="font-medium">RESERVATION/APPROVAL</span>
                </a>
                
                <div class="border-t border-white/10 my-2"></div>
                
                <a href="../logout.php" class="group px-3 py-2 text-white/90 hover:bg-red-500/20 rounded-lg transition-all duration-200">
                    <span class="font-medium group-hover:translate-x-1 transition-transform">LOG OUT</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="px-8 py-8 w-full flex flex-wrap gap-8">
        <div class="flex-1 min-w-[400px] bg-white rounded-xl shadow-lg overflow-hidden h-[700px] border border-[rgba(255,255,255,1)]">
        <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Search Student</h2>
            </div>
            <div class="p-6 h-[calc(100%-4rem)] flex flex-col">
                <div class="mb-6">
                    <form method="POST" action="" class="space-y-3">
                        <div class="flex gap-2">
                            <input type="text" name="search" placeholder="Enter ID Number..." 
                                   class="flex-1 p-3 border border-gray-300 rounded-lg">
                            <button type="submit" class="relative inline-flex items-center justify-center overflow-hidden rounded-lg group bg-gradient-to-br from-blue-600 to-blue-500 p-0.5 text-sm font-medium hover:text-white">
                                <span class="relative rounded-md bg-white px-8 py-3 transition-all duration-300 ease-in-out group-hover:bg-opacity-0 text-blue-700 font-bold group-hover:text-white">
                                    Search
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Student Results Area -->
                <div class="flex-1 overflow-y-auto">
                    <div class="space-y-6 pr-2">
                        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $searched): ?>
                            <?php if ($student): ?>
                                <!-- Student Card -->
                                <div class="bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border-l-4 border-blue-500">
                                    <!-- Student Header -->
                                    <div class="flex flex-col lg:flex-row items-start gap-8">
                                        <!-- Left side - Profile Image -->
                                        <div class="relative group">
                                            <?php if (!empty($student['UPLOAD_IMAGE'])): ?>
                                                <img src="../images/<?php echo htmlspecialchars($student['UPLOAD_IMAGE']); ?>" 
                                                     alt="Student Photo"
                                                     class="w-32 h-32 rounded-2xl object-cover shadow-md group-hover:scale-105 transition-transform duration-300"
                                                     onerror="this.src='../images/image.jpg'">
                                            <?php else: ?>
                                                <img src="../images/image.jpg"
                                                     alt="Default Photo"
                                                     class="w-32 h-32 rounded-2xl object-cover shadow-md group-hover:scale-105 transition-transform duration-300">
                                            <?php endif; ?>
                                            <div class="absolute -bottom-2 -right-2 bg-green-400 text-white rounded-full px-2 py-1 text-xs">
                                                Verified
                                            </div>
                                        </div>

                                        <!-- Right side - Student Information -->
                                        <div class="flex-1">
                                            <!-- Name and ID -->
                                            <div class="mb-6">
                                                <h3 class="text-2xl font-bold text-gray-800 mb-2">
                                                    <?php echo htmlspecialchars($student['FIRST_NAME'] . ' ' . $student['LAST_NAME']); ?>
                                                </h3>
                                                <div class="inline-block bg-blue-100 text-blue-700 px-4 py-1.5 rounded-full text-sm font-medium">
                                                    <?php echo htmlspecialchars($student['IDNO']); ?>
                                                </div>
                                            </div>

                                            <!-- Student Details -->
                                            <div class="space-y-3">
                                                <div class="flex items-center text-gray-700">
                                                    <span class="font-medium min-w-[100px]">Course:</span>
                                                    <span><?php echo htmlspecialchars($student['COURSE']); ?></span>
                                                </div>
                                                <div class="flex items-center text-gray-700">
                                                    <span class="font-medium min-w-[100px]">Year Level:</span>
                                                    <span><?php echo htmlspecialchars($student['YEAR_LEVEL']); ?></span>
                                                </div>
                                                <div class="flex items-center text-gray-700">
                                                    <span class="font-medium min-w-[100px]">Email:</span>
                                                    <span class="text-blue-600"><?php echo htmlspecialchars($student['EMAIL']); ?></span>
                                                </div>
                                            </div>

                                            <!-- Sessions Progress -->
                                            <div class="mt-6">
                                                <div class="flex justify-between items-center mb-2">
                                                    <p class="text-sm text-gray-600">Sessions Progress</p>
                                                    <p class="text-lg font-bold text-purple-600"><?php echo $student['SESSION']; ?>/30</p>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full transition-all duration-500" 
                                                         style="width: <?php echo ($student['SESSION'] / 30) * 100; ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Register Session Section -->
                                    <div class="mt-8 pt-6 border-t border-gray-200">
                                        

                                        <form method="POST" action="" class="space-y-4">
                                            <input type="hidden" name="idno" value="<?php echo htmlspecialchars($student['IDNO']); ?>">
                                            <input type="hidden" name="full_name" value="<?php echo htmlspecialchars($student['FIRST_NAME'] . ' ' . $student['LAST_NAME']); ?>">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div class="relative">
                                                    <select name="purpose" class="w-full p-3 bg-gray-50 border border-gray-300 rounded-xl cursor-pointer hover:border-purple-500 transition-colors" required>
                                                        <option value=""disabled selected>Select Purpose</option>
                                                        <option value="C Programming">C Programming</option>
                                                        <option value="C++ Programming">C++ Programming</option>
                                                        <option value="C# Programming">C# Programming</option>
                                                        <option value="Java Programming">Java Programming</option>
                                                        <option value="Php Programming">Php Programming</option>
                                                        <option value="Python Programming">Python Programming</option>
                                                        <option value="Database">Database</option>
                                                        <option value="Digital Logic & Design">Digital Logic & Design</option>
                                                        <option value="Embedded System & IOT">Embedded System & IOT</option>
                                                        <option value="System Integration & Architecture">System Integration & Architecture</option>
                                                        <option value="Computer Application">Computer Application</option>
                                                        <option value="Web Design & Development">Web Design & Development</option>
                                                        <option value="Project Management">Project Management</option>
                                                    </select>
                                                </div>
                                                <div class="relative">
                                                    <select name="laboratory" class="w-full p-3 bg-gray-50 border border-gray-300 rounded-xl cursor-pointer hover:border-purple-500 transition-colors" required>
                                                        <option value=""disabled selected>Select Laboratory</option>
                                                        <option value="Lab 517">Lab 517</option>
                                                        <option value="Lab 524">Lab 524</option>
                                                        <option value="Lab 526">Lab 526</option>
                                                        <option value="Lab 528">Lab 528</option>
                                                        <option value="Lab 530">Lab 530</option>
                                                        <option value="Lab 542">Lab 542</option>
                                                        <option value="Lab 544">Lab 544</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="flex justify-end">
                                                <button type="submit" name="time_in" class="relative inline-flex items-center justify-center overflow-hidden rounded-lg group bg-gradient-to-br from-blue-600 to-blue-500 p-0.5 text-sm font-medium hover:text-white">
                                                    <span class="relative rounded-md bg-white px-8 py-3 transition-all duration-300 ease-in-out group-hover:bg-opacity-0 text-blue-700 font-bold group-hover:text-white">
                                                        Time - In
                                                    </span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <div class="bg-red-50 p-6 rounded-xl inline-block">
                                        <i class="fas fa-user-times text-4xl text-red-400 mb-3"></i>
                                        <p class="text-gray-600">No student found with ID Number: <span class="font-semibold"><?php echo htmlspecialchars($_POST['search']); ?></span></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
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

        // SweetAlert for success and error messages
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

        <?php if(isset($_SESSION['swal_error'])): ?>
        Toast.fire({
            icon: 'error',
            title: '<?php echo $_SESSION['swal_error']; ?>',
            background: '#EF4444'
        });
        <?php unset($_SESSION['swal_error']); endif; ?>

        <?php if(isset($_SESSION['swal_success'])): ?>
        Toast.fire({
            icon: 'success',
            title: '<?php echo $_SESSION['swal_success']; ?>',
            background: '#10B981'
        });
        <?php unset($_SESSION['swal_success']); endif; ?>
    </script>
</body>
</html>