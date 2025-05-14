<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Initialize pagination variables
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

// Initialize search query
$search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';

// Update points and sessions if points reach 3 or more
$update_query = "UPDATE users 
                SET SESSION = SESSION + FLOOR(POINTS / 3),
                    TOTAL_POINTS = TOTAL_POINTS + POINTS,
                    POINTS = POINTS % 3 
                WHERE POINTS >= 3";
mysqli_query($conn, $update_query);

// Get total records
$count_query = "SELECT COUNT(*) as total FROM users";
if (!empty($search)) {
    $count_query .= " WHERE IDNO LIKE '%$search%' 
                OR LAST_NAME LIKE '%$search%' 
                OR FIRST_NAME LIKE '%$search%'";
}
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = $count_row['total'];

// Get student information query
$query = "SELECT * FROM users";

if (!empty($search)) {
    $query .= " WHERE users.IDNO LIKE '%$search%' 
                OR users.LAST_NAME LIKE '%$search%' 
                OR users.FIRST_NAME LIKE '%$search%' 
                OR users.COURSE LIKE '%$search%'
                OR users.YEAR_LEVEL LIKE '%$search%'";
}
$query .= " ORDER BY users.LAST_NAME ASC 
           LIMIT $entries_per_page OFFSET $offset";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
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
    <title>Lab Usage Points</title>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
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
    <!-- New Header from admin_search.php -->
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

    <!-- Main Content Container -->
    <div class="container mx-auto px-4 mt-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
            <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Student Points</h2>
            </div>
            
            <div class="p-6">
                <!-- Entries and Search Controls -->
                <div class="flex items-center justify-between mb-4">
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
                                placeholder="Search..." 
                                class="w-64 pl-10 pr-4 py-2 border border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none"
                                onkeypress="if(event.key === 'Enter') { event.preventDefault(); searchTable(); }">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Table content -->
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);" class="text-white">
                            <tr>
                                <th class="px-6 py-3 text-left">ID Number</th>
                                <th class="px-6 py-3 text-left">Student Name</th>
                                <th class="px-6 py-3 text-left">Course</th>
                                <th class="px-6 py-3 text-left">Year Level</th>
                                <th class="px-6 py-3 text-center">Points</th>
                                <th class="px-6 py-3 text-center">Credit Sessions</th>
                                <th class="px-6 py-3 text-center">Total Points</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="bg-white">
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $fullName = $row['LAST_NAME'] . ', ' . $row['FIRST_NAME'] . ' ' . $row['MID_NAME'];
                                    
                                    // Get the actual sit-in count for this student
                                    $sitin_query = "SELECT COUNT(*) as sit_in_count FROM curr_sitin WHERE IDNO = " . $row['IDNO'];
                                    $sitin_result = mysqli_query($conn, $sitin_query);
                                    $sitin_row = mysqli_fetch_assoc($sitin_result);
                                    $sit_in_count = $sitin_row ? $sitin_row['sit_in_count'] : 0;
                                    
                                    echo "<tr class='hover:bg-gray-100'>";
                                    echo "<td class='px-6 py-4'>" . $row['IDNO'] . "</td>";
                                    echo "<td class='px-6 py-4'>" . $fullName . "</td>";
                                    echo "<td class='px-6 py-4'>" . $row['COURSE'] . "</td>";
                                    echo "<td class='px-6 py-4'>" . $row['YEAR_LEVEL'] . "</td>";
                                    echo "<td class='px-6 py-4 text-center'>" . $row['POINTS'] . "</td>";
                                    echo "<td class='px-6 py-4 text-center'>" . $row['SESSION'] . "</td>";
                                    echo "<td class='px-6 py-4 text-center'>" . $row['TOTAL_POINTS'] . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='px-6 py-4 text-center text-gray-500 italic'>No data available</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
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
    </div>

    <script>
        // Toast notification handler
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

        function toggleNav(x) {
            document.getElementById("mySidenav").classList.toggle("-translate-x-0");
            document.getElementById("mySidenav").classList.toggle("-translate-x-full");
        }

        function closeNav() {
            document.getElementById("mySidenav").classList.remove("-translate-x-0");
            document.getElementById("mySidenav").classList.add("-translate-x-full");
        }

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

        function changeEntries(entries) {
            window.location.href = `admin_lab_usage.php?entries=${entries}&page=1`;
        }

        function changePage(page) {
            const entries = document.getElementById('entriesPerPage').value;
            window.location.href = `admin_lab_usage.php?entries=${entries}&page=${page}`;
        }
    </script>
</body>
</html>
