<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Get selected values from GET or use defaults
$selectedLab = isset($_GET['lab']) ? $_GET['lab'] : 'Lab 517';
$selectedDay = isset($_GET['day']) ? $_GET['day'] : 'Monday';

// Format the lab value for display
$displayLab = $selectedLab;
if (strpos($selectedLab, 'Lab') === false) {
    $displayLab = 'Lab ' . $selectedLab;
}

// Fetch schedules from database for the selected lab and day
$query = "SELECT * FROM lab_schedule WHERE LABORATORY = ? AND DAY = ? ORDER BY TIME_START ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $displayLab, $selectedDay);
$stmt->execute();
$result = $stmt->get_result();
$schedules = $result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../logo/ccs.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_lab_schedule.css">
    <title>Lab Schedule</title>
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
        .colored-toast.swal2-icon-success {
            background-color: #10B981 !important;
        }
        .colored-toast.swal2-icon-error {
            background-color: #EF4444 !important;
        }
        .colored-toast {
            color: #fff !important;
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
    <!-- New Header -->
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
                    <a href="admin_dashboard.php" class="nav-item">
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
                        <button class="nav-item">
                            <i class="ri-eye-line"></i>
                            <span>View</span>
                            <i class="ri-arrow-down-s-line"></i>
                        </button>
                        <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 hidden group-hover:block">
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
                        <button class="nav-item active">
                            <i class="ri-computer-line"></i>
                            <span>Lab</span>
                            <i class="ri-arrow-down-s-line"></i>
                        </button>
                        <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 hidden group-hover:block">
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

                    <a href="../logout.php" class="nav-item hover:bg-red-500/20">
                        <i class="ri-logout-box-r-line"></i>
                        <span>Logout</span>
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="modern-card">
            <div class="card-header p-5 flex items-center justify-center relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #4066E0 0%, #4D6AFF 100%)">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10 font-sans text-white">Laboratory Management</h2>
            </div>
            
            <div class="p-6">
                <!-- Search and Controls Section -->
                <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
                    <!-- Search Field - Resized -->
                    <div class="relative flex-grow md:flex-grow-0">
                        <input type="text" placeholder="Search..."
                               class="pl-10 pr-4 py-2.5 w-full md:w-64 rounded-full border-0 bg-white/80 backdrop-blur-md
                                     shadow-inner focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-indigo-500">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>

                    <!-- Dropdown Selectors -->
                    <div class="flex items-center space-x-4">
                        <!-- Laboratory Dropdown -->
                        <div class="relative">
                            <select id="labSelect" onchange="selectLab(this.value)"
                                    class="block appearance-none bg-white border border-gray-300 hover:border-gray-500 px-4 py-2 pr-8 rounded-full shadow leading-tight focus:outline-none focus:shadow-outline">
                                <option value="517" <?php echo $selectedLab == 'Lab 517' || $selectedLab == '517' ? 'selected' : ''; ?>>Lab 517</option>
                                <option value="524" <?php echo $selectedLab == 'Lab 524' || $selectedLab == '524' ? 'selected' : ''; ?>>Lab 524</option>
                                <option value="526" <?php echo $selectedLab == 'Lab 526' || $selectedLab == '526' ? 'selected' : ''; ?>>Lab 526</option>
                                <option value="528" <?php echo $selectedLab == 'Lab 528' || $selectedLab == '528' ? 'selected' : ''; ?>>Lab 528</option>
                                <option value="530" <?php echo $selectedLab == 'Lab 530' || $selectedLab == '530' ? 'selected' : ''; ?>>Lab 530</option>
                                <option value="542" <?php echo $selectedLab == 'Lab 542' || $selectedLab == '542' ? 'selected' : ''; ?>>Lab 542</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>

                        <!-- Day Dropdown -->
                        <div class="relative">
                            <select id="daySelect" onchange="selectDay(this.value)"
                                    class="block appearance-none bg-white border border-gray-300 hover:border-gray-500 px-4 py-2 pr-8 rounded-full shadow leading-tight focus:outline-none focus:shadow-outline">
                                <option value="Monday" <?php echo $selectedDay == 'Monday' ? 'selected' : ''; ?>>Monday</option>
                                <option value="Tuesday" <?php echo $selectedDay == 'Tuesday' ? 'selected' : ''; ?>>Tuesday</option>
                                <option value="Wednesday" <?php echo $selectedDay == 'Wednesday' ? 'selected' : ''; ?>>Wednesday</option>
                                <option value="Thursday" <?php echo $selectedDay == 'Thursday' ? 'selected' : ''; ?>>Thursday</option>
                                <option value="Friday" <?php echo $selectedDay == 'Friday' ? 'selected' : ''; ?>>Friday</option>
                                <option value="Saturday" <?php echo $selectedDay == 'Saturday' ? 'selected' : ''; ?>>Saturday</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Add New Schedule Button - Kept Original -->
                    <button id="openScheduleModal" class="bg-gradient-to-br from-blue-600 to-purple-700 text-white font-medium py-2.5 px-5 rounded-full hover:shadow-md transition-all">
                        <i class="fas fa-plus mr-2"></i> Add New Schedule
                    </button>
                </div>
                
                <!-- Current Selection -->
                <div class="mb-6">
                    <span class="selection-badge">

                        Laboratory <?php echo str_replace('Lab ', '', $displayLab); ?>
                    </span>
                    <span class="selection-badge">
                        <i class="fas fa-calendar-day"></i>
                        <?php echo $selectedDay; ?>
                    </span>
                </div>
                
                <!-- Schedule Table - Redesigned -->
                <div class="mt-6 overflow-fix">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th width="20%">Time Slot</th>
                                <th width="40%">Course Details</th>
                                <th width="25%">Professor</th>
                                <th width="15%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($schedules) > 0): ?>
                                <?php foreach ($schedules as $schedule): ?>
                                    <tr>
                                        <td>
                                            <div class="flex items-center">
                                                <i class="far fa-clock text-blue-600 mr-2"></i>
                                                <span>
                                                    <?php 
                                                        $startTime = date('g:i A', strtotime($schedule['TIME_START']));
                                                        $endTime = date('g:i A', strtotime($schedule['TIME_END']));
                                                        echo $startTime . ' - ' . $endTime; 
                                                    ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="subject-name"><?php echo htmlspecialchars($schedule['SUBJECT']); ?></div>
                                                <div class="subject-desc">
                                                    <?php 
                                                        echo "Course Code"; 
                                                    ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex items-center">
                                                <?php 
                                                    $nameParts = explode(' ', $schedule['PROFESSOR']);
                                                    $initials = '';
                                                    foreach ($nameParts as $part) {
                                                        if (!empty($part)) {
                                                            $initials .= strtoupper(substr($part, 0, 1));
                                                        }
                                                    }
                                                    
                                                    $colors = ['blue', 'green', 'purple', 'orange', 'teal', 'pink', 'indigo'];
                                                    $colorIndex = crc32($schedule['PROFESSOR']) % count($colors);
                                                    $color = $colors[$colorIndex];
                                                    
                                                    $bgColorClass = "bg-{$color}-100";
                                                    $textColorClass = "text-{$color}-700";
                                                ?>
                                                <div class="w-8 h-8 rounded-full <?php echo $bgColorClass; ?> flex items-center justify-center mr-3">
                                                    <span class="<?php echo $textColorClass; ?> font-medium"><?php echo $initials; ?></span>
                                                </div>
                                                <?php echo htmlspecialchars($schedule['PROFESSOR']); ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="flex justify-center space-x-2">
                                                <button class="btn-table-action btn-edit" title="Edit" onclick="editSchedule(<?php echo $schedule['SCHED_ID']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn-table-action btn-delete" title="Delete" onclick="confirmDeleteSchedule(<?php echo $schedule['SCHED_ID']; ?>)">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-6 text-gray-500 italic">No schedules found for this day and laboratory</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Schedule Modal -->
    <div id="scheduleModal" class="modal">
        <div class="modal-content">
            <button type="button" class="modal-close" id="closeScheduleModal">&times;</button>
            
            <div class="modal-columns">
                <!-- Left Column -->
                <div class="modal-left">
                    <h3 class="modal-title">Add New Schedule</h3>
                    <p class="modal-subtitle">Create a new laboratory class schedule with the details on the right.</p>
                    
                    <div class="feature-list">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <span>Select day of the week</span>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-laptop-code"></i>
                            </div>
                            <span>Choose laboratory room</span>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <span>Set time duration</span>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <span>Add subject details</span>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <span>Assign professor</span>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Form -->
                <div class="modal-right">
                    <form id="scheduleForm" action="add_schedule.php" method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="day" class="form-label">Day of Week</label>
                                <div class="input-icon select-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                    <select id="day" name="day" class="form-control form-select" required>
                                        <option value="" disabled selected>Select Day</option>
                                        <option value="Monday">Monday</option>
                                        <option value="Tuesday">Tuesday</option>
                                        <option value="Wednesday">Wednesday</option>
                                        <option value="Thursday">Thursday</option>
                                        <option value="Friday">Friday</option>
                                        <option value="Saturday">Saturday</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="laboratory" class="form-label">Laboratory</label>
                                <div class="input-icon select-icon">
                                    <i class="fas fa-desktop"></i>
                                    <select id="laboratory" name="laboratory" class="form-control form-select" required>
                                        <option value="" disabled selected>Select Laboratory</option>
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
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Schedule Time</label>
                            <div class="form-row">
                                <div>
                                    <div class="input-icon">
                                        <i class="fas fa-hourglass-start"></i>
                                        <input type="time" id="time_start" name="time_start" class="form-control" required placeholder="Start Time">
                                    </div>
                                </div>
                                <div>
                                    <div class="input-icon">
                                        <i class="fas fa-hourglass-end"></i>
                                        <input type="time" id="time_end" name="time_end" class="form-control" required placeholder="End Time">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject" class="form-label">Subject</label>
                            <div class="input-icon">
                                <i class="fas fa-book-open"></i>
                                <input type="text" id="subject" name="subject" class="form-control" placeholder="Enter subject name" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="professor" class="form-label">Professor</label>
                            <div class="input-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <input type="text" id="professor" name="professor" class="form-control" placeholder="Enter professor name" required>
                            </div>
                        </div>
                        
                        <div class="btn-row">
                            <button type="button" class="btn btn-cancel" id="cancelScheduleModal">
                                <i class="fas fa-times btn-icon"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save btn-icon"></i>Save Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    

    <script>
        function toggleNav(x) {
            document.getElementById("mySidenav").classList.toggle("-translate-x-0");
            document.getElementById("mySidenav").classList.toggle("-translate-x-full");
            
            x.classList.toggle("change");
            if (x.classList.contains("change")) {
                x.querySelector(".bar1").classList.add("rotate-45", "translate-y-2");
                x.querySelector(".bar2").classList.add("opacity-0");
                x.querySelector(".bar3").classList.add("-rotate-45", "-translate-y-2");
            } else {
                x.querySelector(".bar1").classList.remove("rotate-45", "translate-y-2");
                x.querySelector(".bar2").classList.remove("opacity-0");
                x.querySelector(".bar3").classList.remove("-rotate-45", "-translate-y-2");
            }
        }

        function closeNav() {
            document.getElementById("mySidenav").classList.remove("-translate-x-0");
            document.getElementById("mySidenav").classList.add("-translate-x-full");
        }
        
        // Show success message after adding or editing schedule
        function showSuccessMessage(message) {
            Swal.fire({
                toast: true,
                icon: 'success',
                title: message,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    popup: 'colored-toast'
                }
            });
        }
        
        // Show error message
        function showErrorMessage(message) {
            Swal.fire({
                toast: true,
                icon: 'error',
                title: message,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    popup: 'colored-toast'
                }
            });
        }
        
        // Confirm delete schedule
        function confirmDeleteSchedule(scheduleId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6d28d9',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Handle delete action here
                    // You would typically send an AJAX request to delete the schedule
                    // Then show success message
                    showSuccessMessage('Schedule has been deleted!');
                }
            });
        }
        
        // Modal functions
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('scheduleModal');
            const openModalBtn = document.getElementById('openScheduleModal');
            const closeModalBtn = document.getElementById('closeScheduleModal');
            const cancelModalBtn = document.getElementById('cancelScheduleModal');
            const scheduleForm = document.getElementById('scheduleForm');
            
            // Make sure modal is hidden on load
            modal.style.display = 'none';
            
            // Open modal
            openModalBtn.addEventListener('click', function() {
                modal.classList.add('active');
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal
            });
            
            // Close modal on X button
            closeModalBtn.addEventListener('click', function() {
                modal.classList.remove('active');
                modal.style.display = 'none';
                document.body.style.overflow = 'auto'; // Enable scrolling again
            });
            
            // Close modal on Cancel button
            cancelModalBtn.addEventListener('click', function() {
                modal.classList.remove('active');
                modal.style.display = 'none';
                document.body.style.overflow = 'auto'; // Enable scrolling again
            });
            
            // Close modal when clicking outside the modal content
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.classList.remove('active');
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto'; // Enable scrolling again
                }
            });
            
            // Handle form submission
            scheduleForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form data
                const formData = new FormData(this);
                
                // Send AJAX request
                fetch('add_schedule.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        modal.classList.remove('active');
                        modal.style.display = 'none';
                        document.body.style.overflow = 'auto';
                        
                        // Show success message
                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: data.message || 'Schedule added successfully',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            customClass: {
                                popup: 'colored-toast'
                            }
                        });
                        
                        // Reset the form
                        scheduleForm.reset();
                        
                        // Refresh the page after a short delay to show the new schedule
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        // Show error message
                        Swal.fire({
                            toast: true,
                            icon: 'error',
                            title: data.message || 'Failed to add schedule',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            customClass: {
                                popup: 'colored-toast'
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        toast: true,
                        icon: 'error',
                        title: 'An error occurred while adding the schedule',
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'colored-toast'
                        }
                    });
                });
            });
        });

        // Functions to handle lab and day selection
        function selectLab(lab) {
            const currentDay = new URLSearchParams(window.location.search).get('day') || 'Monday';
            window.location.href = `admin_lab_schedule.php?lab=${lab}&day=${currentDay}`;
        }
        
        function selectDay(day) {
            const currentLab = new URLSearchParams(window.location.search).get('lab') || '517';
            window.location.href = `admin_lab_schedule.php?lab=${currentLab}&day=${day}`;
        }
        
        // Function to edit an existing schedule
        function editSchedule(scheduleId) {
            // Fetch schedule details with AJAX
            fetch(`get_schedule.php?id=${scheduleId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const schedule = data.schedule;
                        
                        // Fill the form with existing data
                        document.getElementById('day').value = schedule.DAY;
                        document.getElementById('laboratory').value = schedule.LABORATORY;
                        document.getElementById('time_start').value = schedule.TIME_START;
                        document.getElementById('time_end').value = schedule.TIME_END;
                        document.getElementById('subject').value = schedule.SUBJECT;
                        document.getElementById('professor').value = schedule.PROFESSOR;
                        
                        // Add schedule ID to form for update operation
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'schedule_id';
                        hiddenInput.value = scheduleId;
                        document.getElementById('scheduleForm').appendChild(hiddenInput);
                        
                        // Change form action to update
                        document.getElementById('scheduleForm').action = 'update_schedule.php';
                        
                        // Update modal title
                        document.querySelector('.modal-title').textContent = 'Edit Schedule';
                        
                        // Update button text
                        document.querySelector('.btn-primary').innerHTML = '<i class="fas fa-save btn-icon"></i>Update Schedule';
                        
                        // Show modal
                        const modal = document.getElementById('scheduleModal');
                        modal.classList.add('active');
                        modal.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                    } else {
                        showErrorMessage(data.message || 'Failed to load schedule details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage('An error occurred while loading schedule details');
                });
        }
        
        // When canceling or closing the modal, reset the form
        document.addEventListener('DOMContentLoaded', function() {
            const closeModalBtn = document.getElementById('closeScheduleModal');
            const cancelModalBtn = document.getElementById('cancelScheduleModal');
            
            function resetForm() {
                document.getElementById('scheduleForm').reset();
                document.getElementById('scheduleForm').action = 'add_schedule.php';
                document.querySelector('.modal-title').textContent = 'Add New Schedule';
                document.querySelector('.btn-primary').innerHTML = '<i class="fas fa-save btn-icon"></i>Save Schedule';
                
                // Remove any hidden schedule ID field
                const hiddenInput = document.querySelector('input[name="schedule_id"]');
                if (hiddenInput) {
                    hiddenInput.remove();
                }
            }
            
            closeModalBtn.addEventListener('click', resetForm);
            cancelModalBtn.addEventListener('click', resetForm);
            
            // Reset the form when opening the modal for a new schedule
            document.getElementById('openScheduleModal').addEventListener('click', resetForm);
        });
        
        // Delete schedule function
        function confirmDeleteSchedule(scheduleId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6d28d9',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send delete request via AJAX
                    fetch('delete_schedule.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `schedule_id=${scheduleId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccessMessage('Schedule has been deleted!');
                            // Reload page after a delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            showErrorMessage(data.message || 'Failed to delete schedule');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showErrorMessage('An error occurred while deleting the schedule');
                    });
                }
            });
        }
    </script>
</body>
</html>