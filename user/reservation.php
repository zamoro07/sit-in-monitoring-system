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
    $stmt = $conn->prepare("SELECT UPLOAD_IMAGE, IDNO, FIRST_NAME, LAST_NAME, COURSE, YEAR_LEVEL, SESSION FROM users WHERE STUD_NUM = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($userImage, $idNumber, $firstName, $lastName, $userCourse, $userYearLevel, $userSessions);
    $stmt->fetch();
    $stmt->close();
    
    $profileImage = !empty($userImage) ? '../images/' . $userImage : "../images/image.jpg";
    $studentName = $firstName . ' ' . $lastName;
} else {
    $profileImage = "../images/image.jpg";
    $idNumber = '';
    $studentName = 'Guest';
    $userCourse = '';
    $userYearLevel = '';
    $userSessions = 0;
}

// Add this near the top with other PHP handlers
if (isset($_GET['get_pc_status'])) {
    $lab = $_GET['lab'];
    // Updated query to get PCs from computer table
    $stmt = $conn->prepare("SELECT PC_NUM, STATUS FROM computer WHERE LABORATORY = ? ORDER BY PC_NUM ASC");
    $labName = 'lab' . $lab; // Convert lab number to format stored in database (e.g., 'lab524')
    $stmt->bind_param("s", $labName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pcStatus = [];
    while ($row = $result->fetch_assoc()) {
        $pcStatus[$row['PC_NUM']] = strtoupper($row['STATUS']);
    }
    $stmt->close();
    echo json_encode($pcStatus);
    exit;
}

// Process reservation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $purpose = isset($_POST['purpose']) ? $_POST['purpose'] : '';
    $lab = isset($_POST['lab']) ? $_POST['lab'] : '';
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $timeIn = isset($_POST['time_in']) ? $_POST['time_in'] : '';
    $pcNumber = isset($_POST['available_pc']) ? $_POST['available_pc'] : '';
    
    // Validate and insert reservation
    if (!empty($purpose) && !empty($lab) && !empty($date) && !empty($timeIn) && !empty($pcNumber)) {
        // Insert into reservation table
        $stmt = $conn->prepare("INSERT INTO reservation (IDNO, FULL_NAME, COURSE, YEAR_LEVEL, PURPOSE, LABORATORY, PC_NUM, DATE, TIME_IN) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("issssssss", 
            $idNumber,
            $studentName,
            $userCourse,    // Use the user's actual course
            $userYearLevel, // Use the user's actual year level
            $purpose,
            $lab,
            $pcNumber,
            $date,
            $timeIn
        );
        
        if ($stmt->execute()) {
            $reservationId = $stmt->insert_id;
            
            // Create notification for admin (USER_ID is NULL for admin)
            $notificationMessage = "$studentName ($userYearLevel) has requested a reservation for $lab on $date at $timeIn for $purpose.";
            
            $notifyStmt = $conn->prepare("INSERT INTO notification (USER_ID, RESERVATION_ID, MESSAGE, IS_READ, CREATED_AT) VALUES (NULL, ?, ?, 0, NOW())");
            $notifyStmt->bind_param("is", $reservationId, $notificationMessage);
            $notifyStmt->execute();
            $notifyStmt->close();
            
            // Store success message in session
            $_SESSION['successMessage'] = "Reservation confirmed successfully!";
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $_SESSION['errorMessage'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['errorMessage'] = "All fields are required";
    }
}

// Get messages from session
$successMessage = isset($_SESSION['successMessage']) ? $_SESSION['successMessage'] : null;
$errorMessage = isset($_SESSION['errorMessage']) ? $_SESSION['errorMessage'] : null;

// Clear messages from session
unset($_SESSION['successMessage']);
unset($_SESSION['errorMessage']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
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
    <title>Reservation</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #ffffff !important;
            min-height: 100vh;
        }
        .gradient-text {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
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
        @media (max-width: 768px) {
            .nav-item span {
                display: none;
            }
            .nav-item i {
                margin-right: 0;
            }
        }
        @media (max-width: 1024px) {
            header .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
        /* Toast notifications styling */
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
<body class="bg-gradient-to-br min-h-screen font-poppins" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
    <!-- Header (copied from profile.php) -->
    <header class="bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-lg py-4 px-6">
        <div class="container mx-auto flex items-center justify-between">
            <div class="flex items-center">
                <h1 class="text-2xl font-bold">CCS SIT-IN MONITORING SYSTEM</h1>
            </div>
            <div class="flex items-center space-x-6">
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
                <button class="md:hidden" @click="mobileMenu = !mobileMenu">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </header>
    <!-- Main Content: Reservation & PC Selection -->
    <div class="container mx-auto p-4">
        <?php if (isset($successMessage) || isset($errorMessage)): ?>
            <script>
                const showToast = (type, message) => {
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
                        icon: type,
                        title: message,
                        background: type === 'success' ? '#10B981' : '#EF4444'
                    });
                };

                <?php if (isset($successMessage)): ?>
                    showToast('success', '<?php echo $successMessage; ?>');
                <?php endif; ?>
                
                <?php if (isset($errorMessage)): ?>
                    showToast('error', '<?php echo $errorMessage; ?>');
                <?php endif; ?>
            </script>
        <?php endif; ?>
        
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Reservation Form -->
            <div class="w-full md:w-1/2"> <!-- Reservation container -->
                <div class="w-full bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
                    <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                        <h2 class="text-xl font-bold tracking-wider uppercase">Make a Reservation</h2>
                    </div>
                    <form action="reservation.php" method="post" class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Student ID -->
                            <div class="flex items-center">
                                <input type="text" id="id_number" name="id_number" value="<?php echo htmlspecialchars($idNumber); ?>" 
                                    class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed focus:ring-2 focus:ring-purple-500/50" readonly placeholder="Student ID">
                            </div>
                            <!-- Full Name -->
                            <div class="flex items-center">
                                <input type="text" id="student_name" name="student_name" value="<?php echo htmlspecialchars($studentName); ?>" 
                                    class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed focus:ring-2 focus:ring-purple-500/50" readonly placeholder="Full Name">
                            </div>
                            <!-- Course -->
                            <div class="flex items-center">
                                <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($userCourse); ?>" 
                                    class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed focus:ring-2 focus:ring-purple-500/50" readonly placeholder="Course">
                            </div>
                            <!-- Year Level -->
                            <div class="flex items-center">
                                <input type="text" id="year_level" name="year_level" value="<?php echo htmlspecialchars($userYearLevel); ?>" 
                                    class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed focus:ring-2 focus:ring-purple-500/50" readonly placeholder="Year Level">
                            </div>
                            <!-- Purpose -->
                            <div class="flex items-center">
                                <select id="purpose" name="purpose" required
                                    class="w-full p-3 border border-gray-300 rounded-lg bg-white hover:border-purple-400 focus:ring-2 focus:ring-purple-500/50 transition-colors duration-200">
                                    <option value="" disabled selected>Select Purpose</option>
                                    <option value="C Programming">C Programming</option>
                                    <option value="C++ Programming">C++ Programming</option>
                                    <option value="C# Programming">C# Programming</option>
                                    <option value="Java Programming">Java Programming</option>
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
                            <!-- Laboratory -->
                            <div class="flex items-center">
                                <select id="lab" name="lab" required
                                    class="w-full p-3 border border-gray-300 rounded-lg bg-white hover:border-purple-400 focus:ring-2 focus:ring-purple-500/50 transition-colors duration-200" 
                                    onchange="updatePcOptions()">
                                    <option value="" disabled selected>Select Laboratory</option>
                                    <?php foreach ([517, 524, 526, 528, 530, 542, 544] as $labNumber): ?>
                                        <option value="<?php echo $labNumber; ?>"><?php echo $labNumber; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Date -->
                            <div class="flex items-center">
                                <input type="date" id="date" name="date" required
                                    min="<?php echo date('Y-m-d'); ?>"
                                    class="w-full p-3 border border-gray-300 rounded-lg bg-white hover:border-purple-400 focus:ring-2 focus:ring-purple-500/50 transition-colors duration-200">
                            </div>
                            <!-- Time In -->
                            <div class="flex items-center">
                                <input type="time" id="time_in" name="time_in" required
                                    class="w-full p-3 border border-gray-300 rounded-lg bg-white hover:border-purple-400 focus:ring-2 focus:ring-purple-500/50 transition-colors duration-200">
                            </div>
                            <!-- Remaining Sessions -->
                            <div class="flex items-center">
                                <input type="text" id="remaining_session" name="remaining_session" 
                                    value="<?php echo htmlspecialchars($userSessions); ?>" 
                                    class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed focus:ring-2 focus:ring-purple-500/50" 
                                    readonly placeholder="Remaining Sessions">
                            </div>
                            
                            <!-- Hidden PC input (will be updated by the PC selection panel) -->
                            <input type="hidden" id="available_pc" name="available_pc" value="">
                        </div>
                        <!-- Submit Button -->
                        <div class="flex justify-center pt-4">
                            <button type="submit" 
                                class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold rounded-lg 
                                hover:shadow-lg transform hover:scale-105 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                                Confirm Reservation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- PC Selection Panel -->
            <div class="w-full md:w-1/2 flex flex-col"> 
                <div class="flex-1 bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
                    <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                        <h2 class="text-xl font-bold tracking-wider uppercase">Select a PC</h2>
                    </div>
                    
                    <div class="p-4">
                        <div id="pc_message" class="text-center py-6 text-gray-500">
                            Please select a laboratory from the reservation form to view available PCs
                        </div>
                        
                        <div id="pc_grid" class="hidden grid grid-cols-5 gap-4 p-4 max-h-96 overflow-y-auto">
                            <!-- PC cards will be dynamically generated -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script>
        // Toggle sidebar navigation
        function toggleNav() {
        const sidenav = document.getElementById("mySidenav");
        if (sidenav.classList.contains("-translate-x-full")) {
            sidenav.classList.remove("-translate-x-full");
            sidenav.classList.add("-translate-x-0");
            sidenav.classList.add("animate-slide-in");
        } else {
            closeNav();
        }
        }

        function closeNav() {
            const sidenav = document.getElementById("mySidenav");
            sidenav.classList.remove("-translate-x-0");
            sidenav.classList.add("-translate-x-full");
            sidenav.classList.remove("animate-slide-in");
        }
        
        // Function to update PC selector based on selected lab
        function updatePcOptions() {
            const labSelector = document.getElementById('lab');
            
            if (labSelector.value) {
                fetchPcStatus(labSelector.value);
            } else {
                // Hide PC grid and show message if no lab is selected
                document.getElementById('pc_grid').classList.add('hidden');
                document.getElementById('pc_message').classList.remove('hidden');
            }
        }
        
        // Function to fetch PC status from the server
        function fetchPcStatus(labNumber) {
            fetch(`reservation.php?get_pc_status=1&lab=${labNumber}`)
                .then(response => response.json())
                .then(data => generatePcGrid(data))
                .catch(error => console.error('Error fetching PC status:', error));
        }
        
        // Function to generate PC grid with cards
        function generatePcGrid(pcStatus) {
            const pcGrid = document.getElementById('pc_grid');
            const pcMessage = document.getElementById('pc_message');

            pcGrid.classList.remove('hidden');
            pcMessage.classList.add('hidden');
            pcGrid.innerHTML = '';

            // Initialize with all PCs as available
            for (let i = 1; i <= 50; i++) {
                const pcNum = i.toString();
                const status = pcStatus[pcNum] || 'AVAILABLE';
                const isAvailable = status.toUpperCase() === 'AVAILABLE';
                const statusClass = isAvailable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                
                const pcCard = document.createElement('div');
                pcCard.className = `rounded-lg border border-gray-200 overflow-hidden shadow-sm transition-all duration-200 hover:shadow-md ${!isAvailable ? 'opacity-50 cursor-not-allowed' : ''}`;
                pcCard.setAttribute('data-pc', pcNum);
                if (isAvailable) {
                    pcCard.onclick = function() { selectPC(pcNum); };
                }

                pcCard.innerHTML = `
                    <div class="flex flex-col items-center justify-center p-3">
                        <div class="text-center text-sm font-medium text-gray-800">PC ${pcNum}</div>
                        <div class="mt-1 ${statusClass} text-xs font-medium px-2.5 py-0.5 rounded-full">${status}</div>
                    </div>
                `;

                pcGrid.appendChild(pcCard);
            }
        }

        // Function to handle PC selection
        function selectPC(pcNumber) {
            // Reset all PC cards
            const pcCards = document.querySelectorAll('#pc_grid > div');
            pcCards.forEach(card => {
                card.classList.remove('ring-2', 'ring-purple-500', 'bg-purple-50');
            });
            
            // Highlight selected PC
            const selectedCard = document.querySelector(`div[data-pc="${pcNumber}"]`);
            if (selectedCard) {
                selectedCard.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50');
            }
            
            // Update the hidden input value
            document.getElementById('available_pc').value = pcNumber;
        }
    </script>
</body>
</html>