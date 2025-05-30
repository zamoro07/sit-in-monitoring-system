<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Handle student addition via POST request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Idno'])) {
    $idno = $_POST['Idno'];
    $lastname = $_POST['Lastname'];
    $firstname = $_POST['Firstname'];
    $midname = $_POST['Midname'];
    $course = $_POST['Course'];
    $year_level = $_POST['Year_Level'];
    $username = $_POST['Username'];
    $password = password_hash($_POST['Password'], PASSWORD_DEFAULT);

    // Prepare statement to insert new student
    $stmt = $conn->prepare("INSERT INTO users (idno, last_name, first_name, mid_name, course, year_level, user_name, password_hash, session) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 30)");
    $stmt->bind_param("ssssssss", $idno, $lastname, $firstname, $midname, $course, $year_level, $username, $password);

    if ($stmt->execute()) {
        // Set a success flag in session
        $_SESSION['student_added'] = true;
        header("Location: admin_studlist.php");
        exit;
    } else {
        $_SESSION['student_added'] = false;
        header("Location: admin_studlist.php");
        exit;
    }

    $stmt->close();
}

// Initialize pagination variables
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

// Initialize search query
$search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';

// Get total records before applying pagination
$count_query = "SELECT COUNT(*) as total FROM users";
if (!empty($search)) {
    $count_query .= " WHERE IDNO LIKE '%$search%' 
                OR LAST_NAME LIKE '%$search%' 
                OR FIRST_NAME LIKE '%$search%' 
                OR COURSE LIKE '%$search%'
                OR YEAR_LEVEL LIKE '%$search%'";
}
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = $count_row['total'];

// Modify query to include pagination
$query = "SELECT * FROM users";
if (!empty($search)) {
    $query .= " WHERE IDNO LIKE '%$search%' 
                OR LAST_NAME LIKE '%$search%' 
                OR FIRST_NAME LIKE '%$search%' 
                OR COURSE LIKE '%$search%'
                OR YEAR_LEVEL LIKE '%$search%'";
}
$query .= " LIMIT $entries_per_page OFFSET $offset";
$result = mysqli_query($conn, $query);
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
    <title>Admin Student List</title>
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
        /* Existing styles */
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
                        <button class="nav-item active">
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
                        <button class="nav-item">
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

    <div class="content-container w-11/12 mx-auto my-8 bg-white p-6 rounded-lg shadow-lg overflow-hidden border border-gray-200">
        <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
            <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Student Information</h2>
        </div>
        
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <!-- Left side - Entries and Search -->
                <div class="flex items-center space-x-4">
                    <!-- Entries per page selector -->
                    <div class="flex items-center bg-gray-50 rounded-lg p-2 shadow-sm">
                        <label class="text-gray-600 mr-2 text-sm">Show</label>
                        <select id="entriesPerPage" onchange="changeEntries(this.value)" 
                            class="bg-white border border-gray-200 rounded-md px-3 py-1.5 shadow-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none">
                            <option value="10" <?php echo $entries_per_page == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo $entries_per_page == 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $entries_per_page == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $entries_per_page == 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                        <span class="text-gray-600 ml-2 text-sm">entries</span>
                    </div>

                    <!-- Search field -->
                    <div class="relative">
                        <input type="text" 
                            id="searchInput" 
                            placeholder="Search students..." 
                            class="w-64 pl-10 pr-4 py-2 border border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none"
                            onkeypress="if(event.key === 'Enter') { event.preventDefault(); searchTable(); }">
                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <!-- Right side - Action Buttons -->
                <div class="flex items-center space-x-3">
                    <button id="addStudentBtn" 
                        class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="ri-user-add-line mr-2"></i>
                        Add New Student
                    </button>
                    <button id="resetSessionBtn" 
                        class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="ri-refresh-line mr-2"></i>
                        Reset All Session
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                <thead style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);" class="text-white">
                        <tr>
                            <th class="px-6 py-3 text-left">ID Number</th>
                            <th class="px-6 py-3 text-left">Name</th>
                            <th class="px-6 py-3 text-left">Year Level</th>
                            <th class="px-6 py-3 text-left">Course</th>
                            <th class="px-6 py-3 text-left">Remaining Session</th>
                            <th class="px-6 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="bg-white">
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $fullName = $row['LAST_NAME'] . ', ' . $row['FIRST_NAME'] . ' ' . $row['MID_NAME'];
                                echo "<tr class='hover:bg-gray-100'>";
                                echo "<td class='px-6 py-4'>" . $row['IDNO'] . "</td>";
                                echo "<td class='px-6 py-4'>" . $fullName . "</td>";
                                echo "<td class='px-6 py-4'>" . $row['YEAR_LEVEL'] . "</td>";
                                echo "<td class='px-6 py-4'>" . $row['COURSE'] . "</td>";
                                echo "<td class='px-6 py-4'>" . $row['SESSION'] . "</td>";
                                echo "<td class='px-6 py-4'>
                                        <div class='flex items-center gap-3'>
                                            <button onclick=\"window.location.href='edit_student.php?id=" . $row['STUD_NUM'] . "'\" 
                                                class='p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors duration-200' 
                                                title='Edit'>
                                                <i class='ri-edit-line text-lg'></i>
                                            </button>
                                            <button onclick='confirmDelete(" . $row['STUD_NUM'] . ")' 
                                                class='p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition-colors duration-200' 
                                                title='Delete'>
                                                <i class='ri-delete-bin-line text-lg'></i>
                                            </button>
                                            <button onclick='confirmResetSession(" . $row['STUD_NUM'] . ")' 
                                                class='p-1.5 rounded-lg text-green-600 hover:bg-green-50 transition-colors duration-200' 
                                                title='Reset Session'>
                                                <i class='ri-refresh-line text-lg'></i>
                                            </button>
                                        </div>
                                    </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='px-6 py-4 text-center text-gray-500 italic'>No data available</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col md:flex-row md:justify-between md:items-center mt-6 gap-4">
                <div class="text-gray-600 text-sm">
                    <?php
                    $start_entry = $total_records > 0 ? $offset + 1 : 0;
                    $end_entry = min($offset + $entries_per_page, $total_records);
                    echo "Showing <span class='font-semibold'>$start_entry</span> to <span class='font-semibold'>$end_entry</span> of <span class='font-semibold'>$total_records</span> entries";
                    ?>
                </div>
                <div class="inline-flex rounded-lg shadow-sm">
                    <?php
                    $total_pages = ceil($total_records / $entries_per_page);
                    
                    // First page button
                    echo "<button onclick=\"changePage(1)\" " . ($current_page == 1 ? 'disabled' : '') . " 
                          class=\"px-3.5 py-2 text-sm bg-white border border-gray-300 rounded-l-lg hover:bg-gray-50 text-gray-500" . 
                          ($current_page == 1 ? ' opacity-50 cursor-not-allowed' : '') . "\">
                          <i class=\"fas fa-angles-left\"></i>
                      </button>";

                    // Previous page button
                    $prev_page = max(1, $current_page - 1);
                    echo "<button onclick=\"changePage($prev_page)\" " . ($current_page == 1 ? 'disabled' : '') . " 
                          class=\"px-3.5 py-2 text-sm bg-white border-t border-b border-l border-gray-300 hover:bg-gray-50 text-gray-500" . 
                          ($current_page == 1 ? ' opacity-50 cursor-not-allowed' : '') . "\">
                          <i class=\"fas fa-angle-left\"></i>
                      </button>";

                    // Page numbers
                    for($i = 1; $i <= $total_pages; $i++) {
                        if($i == $current_page) {
                            echo "<button class=\"px-3.5 py-2 text-sm bg-blue-600 text-white border border-blue-600\">$i</button>";
                        } else {
                            echo "<button onclick=\"changePage($i)\" 
                                  class=\"px-3.5 py-2 text-sm bg-white border border-gray-300 hover:bg-gray-50 text-gray-700\">$i</button>";
                        }
                    }

                    // Next page button
                    $next_page = min($total_pages, $current_page + 1);
                    echo "<button onclick=\"changePage($next_page)\" " . ($current_page == $total_pages ? 'disabled' : '') . "
                          class=\"px-3.5 py-2 text-sm bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 text-gray-500" . 
                          ($current_page == $total_pages ? ' opacity-50 cursor-not-allowed' : '') . "\">
                          <i class=\"fas fa-angle-right\"></i>
                      </button>";

                    // Last page button
                    echo "<button onclick=\"changePage($total_pages)\" " . ($current_page == $total_pages ? 'disabled' : '') . "
                          class=\"px-3.5 py-2 text-sm bg-white border border-gray-300 rounded-r-lg hover:bg-gray-50 text-gray-500" . 
                          ($current_page == $total_pages ? ' opacity-50 cursor-not-allowed' : '') . "\">
                          <i class=\"fas fa-angles-right\"></i>
                      </button>";
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="py-4 px-6 bg-white/95 backdrop-blur-sm mt-8 relative">
        <div class="absolute inset-x-0 top-0 h-1" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)"></div>
        <p class="text-center text-sm text-gray-600">
            &copy; 2025 CCS Sit-in Monitoring System | <span class="gradient-text font-medium">UC - College of Computer Studies</span>
        </p>
    </div>


    <script>
        function handleKeyPress(event) {
            if (event.key === "Enter") {
                searchTable();
            }
        }

        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const tbody = document.getElementById('tableBody');
            const rows = tbody.getElementsByTagName('tr');

            for (let row of rows) {
                let found = false;
                const cells = row.getElementsByTagName('td');
                
                for (let cell of cells) {
                    const text = cell.textContent || cell.innerText;
                    if (text.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
                
                row.style.display = found ? '' : 'none';
            }
        }

        function toggleNav(x) {
            document.getElementById("mySidenav").classList.toggle("-translate-x-0");
            document.getElementById("mySidenav").classList.toggle("-translate-x-full");
        }

        function closeNav() {
            document.getElementById("mySidenav").classList.remove("-translate-x-0");
            document.getElementById("mySidenav").classList.add("-translate-x-full");
        }
        
        // SweetAlert confirmation for delete
        function confirmDelete(studentId) {
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
                    // Send deletion request
                    window.location.href = 'delete_student.php?id=' + studentId + '&confirmed=true';
                }
            });
        }
        
        // Reset all sessions with SweetAlert
        document.getElementById('resetSessionBtn').addEventListener('click', function() {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-right',
                iconColor: 'white',
                customClass: {
                    popup: 'colored-toast'
                },
                showConfirmButton: true,
                showCancelButton: true,
                confirmButtonText: 'Yes, reset all!',
                cancelButtonText: 'Cancel',
                timer: false
            });

            Toast.fire({
                icon: 'warning',
                title: 'Reset All Sessions?',
                text: 'This will reset ALL student sessions back to 30',
                background: '#F59E0B'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Use fetch API to call reset_all_session.php
                    fetch('reset_all_session.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
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
                                title: 'All sessions reset successfully',
                                background: '#10B981'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
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
                                title: data.message || 'Failed to reset sessions',
                                background: '#EF4444'
                            });
                        }
                    })
                    .catch(error => {
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
                            title: 'Network error. Please try again',
                            background: '#EF4444'
                        });
                    });
                }
            });
        });

        // Reset individual student session
        function confirmResetSession(studentId) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-right',
                iconColor: 'white',
                customClass: {
                    popup: 'colored-toast'
                },
                showConfirmButton: true,
                showCancelButton: true,
                confirmButtonText: 'Yes, reset it!',
                cancelButtonText: 'Cancel',
                timer: false
            });

            Toast.fire({
                icon: 'warning',
                title: 'Reset Session?',
                text: "This will reset this student's session back to 30",
                background: '#F59E0B'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('reset_session.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            studentId: studentId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
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
                                title: 'Session reset successfully',
                                background: '#10B981'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
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
                                title: data.message || 'Failed to reset session',
                                background: '#EF4444'
                            });
                        }
                    })
                    .catch(error => {
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
                            title: 'Network error. Please try again',
                            background: '#EF4444'
                        });
                    });
                }
            });
        }

        // Open modal
        document.getElementById('addStudentBtn').addEventListener('click', () => {
            document.getElementById('addStudentModal').classList.remove('hidden');
            document.getElementById('addStudentModal').classList.add('flex');
        });

        // Close modal
        document.getElementById('closeModal').addEventListener('click', () => {
            document.getElementById('addStudentModal').classList.add('hidden');
            document.getElementById('addStudentModal').classList.remove('flex');
        });

        document.getElementById('cancelBtn').addEventListener('click', () => {
            document.getElementById('addStudentModal').classList.add('hidden');
            document.getElementById('addStudentModal').classList.remove('flex');
        });

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === document.getElementById('addStudentModal')) {
                document.getElementById('addStudentModal').classList.add('hidden');
                document.getElementById('addStudentModal').classList.remove('flex');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Check for student added success message
            <?php if(isset($_SESSION['student_added']) && $_SESSION['student_added'] === true): ?>
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
                    title: 'Student added successfully',
                    background: '#10B981'
                }).then(() => {
                    window.location.href = 'admin_studlist.php';
                });
                <?php unset($_SESSION['student_added']); ?>
            <?php endif; ?>
            
            // Form validation for ID number
            const idnoInput = document.getElementById('Idno');
            idnoInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 8);
            });

            // Name input validation (letters only)
            const nameInputs = ['Lastname', 'Firstname', 'Midname'];
            nameInputs.forEach(function(id) {
                const input = document.getElementById(id);
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
                });
            });

            // Form validation
            const form = document.getElementById('addStudentForm');
            form.addEventListener('submit', function(event) {
                // Validate ID number length
                if (idnoInput.value.length !== 8) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Error!',
                        text: 'ID Number must be exactly 8 digits.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }
                
                // Let the form submit naturally if validation passes
                return true;
            });
        });

        function changeEntries(entries) {
            window.location.href = `admin_studlist.php?entries=${entries}&page=1`;
        }

        function changePage(page) {
            const entries = document.getElementById('entriesPerPage').value;
            window.location.href = `admin_studlist.php?entries=${entries}&page=${page}`;
        }
    </script>
</body>
</html>