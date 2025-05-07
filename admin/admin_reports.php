<?php
session_start();
require '../db.php';

// Check if admin is not logged in
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

// Get total records count
$count_query = "SELECT COUNT(*) as total FROM curr_sitin";
if (!empty($search)) {
    $count_query .= " WHERE IDNO LIKE '%$search%' 
                      OR FULL_NAME LIKE '%$search%' 
                      OR PURPOSE LIKE '%$search%' 
                      OR LABORATORY LIKE '%$search%'";
}
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $entries_per_page);

// Modify the base query to include pagination
$query = "SELECT IDNO, FULL_NAME, PURPOSE, LABORATORY, TIME_IN, TIME_OUT, DATE FROM curr_sitin";
if (!empty($search)) {
    $query .= " WHERE IDNO LIKE '%$search%' 
                OR FULL_NAME LIKE '%$search%' 
                OR PURPOSE LIKE '%$search%' 
                OR LABORATORY LIKE '%$search%'";
}
$query .= " ORDER BY DATE DESC LIMIT ? OFFSET ?";

// Use prepared statement for the main query
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $entries_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitin Reports</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../logo/ccs.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Add DataTables CSS and JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css">
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
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
                <img src="../images/image.jpg" alt="Logo" class="w-20 h-20 rounded-full border-4 border-white/30 object-cover shadow-lg">
                <div class="absolute bottom-0 right-0 bg-green-500 w-3 h-3 rounded-full border-2 border-white"></div>
            </div>
            <p class="text-white font-semibold text-lg mt-2 mb-0">Admin</p>
            <p class="text-purple-200 text-xs mb-3">Administrator</p>
        </div>

        <div class="px-2 py-2">
            <!-- Navigation Section -->
            <nav class="flex flex-col space-y-1">
                <!-- Home Link -->
                <a href="admin_dashboard.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-home w-5 mr-2 text-center"></i>
                    <span class="font-medium">HOME</span>
                </a>

                <!-- Search Link -->
                <a href="admin_search.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-search w-5 mr-2 text-center"></i>
                    <span class="font-medium">SEARCH</span>
                </a>

                <!-- Sit-in Link -->
                <a href="admin_sitin.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-user-check w-5 mr-2 text-center"></i>
                    <span class="font-medium">SIT-IN</span>
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
                        
                        <a href="admin_sitinrec.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-book w-5 mr-2 text-center"></i>
                            <span class="font-medium">Sit-in Records</span>
                        </a>
                        
                        <a href="admin_studlist.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-list w-5 mr-2 text-center"></i>
                            <span class="font-medium">List of Students</span>
                        </a>
                        
                        <a href="admin_feedback.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-comments w-5 mr-2 text-center"></i>
                            <span class="font-medium">Feedbacks</span>
                        </a>
                        
                        <a href="#" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-chart-pie w-5 mr-2 text-center"></i>
                            <span class="font-medium">Daily Analytics</span>
                        </a>
                    </div>
                </div>

                <!-- LAB Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-desktop w-5 mr-2 text-center"></i>
                            <span class="font-medium">LAB</span>
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
                        
                        <a href="admin_resources.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-box-open w-5 mr-2 text-center"></i>
                            <span class="font-medium">Resources</span>
                        </a>
                        
                        <a href="admin_lab_schedule.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-calendar-alt w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Schedule</span>
                        </a>
                        
                        <a href="admin_lab_usage.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-chart-bar w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Usage Point</span>
                        </a>
                    </div>
                </div>

                <!-- Reports Link -->
                <a href="admin_reports.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-chart-line w-5 mr-2 text-center"></i>
                    <span class="font-medium">SIT-IN REPORT</span>
                </a>

                <!-- Reservation/Approval Link -->
                <a href="admin_reservation.php" class="group px-3 py-2 text-white/90 hover:bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-calendar-check w-5 mr-2 text-center"></i>
                    <span class="font-medium">RESERVATION/APPROVAL</span>
                </a>

                <!-- Divider -->
                <div class="border-t border-white/10 my-2"></div>

                <!-- Logout Link -->
                <a href="../logout.php" class="group px-3 py-2 text-white/90 hover:bg-red-500/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-sign-out-alt w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">LOG OUT</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 mt-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
        <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
             style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                <i class="fas fa-chart-line text-2xl mr-4 relative z-10"></i>
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Generate Reports</h2>
            </div>

            <div class="p-6">
                <!-- Date and Search Controls -->
                <div class="flex items-center justify-between mb-6">
                    <form method="POST" class="flex space-x-3" id="filterForm">
                        <input type="date" name="selected_date" value="<?php echo isset($_POST['selected_date']) ? $_POST['selected_date'] : ''; ?>" class="border rounded px-3 py-2">

                        <select name="selected_purpose" class="border rounded px-3 py-2">
                            <option value="" <?php echo !isset($_POST['selected_purpose']) ? 'selected' : ''; ?>>Select Purpose</option>
                            <option value="C Programming" <?php echo isset($_POST['selected_purpose']) && $_POST['selected_purpose'] == 'C Programming' ? 'selected' : ''; ?>>C Programming</option>
                            <option value="C++ Programming" <?php echo isset($_POST['selected_purpose']) && $_POST['selected_purpose'] == 'C++ Programming' ? 'selected' : ''; ?>>C++ Programming</option>
                            <option value="C# Programming" <?php echo isset($_POST['selected_purpose']) && $_POST['selected_purpose'] == 'C# Programming' ? 'selected' : ''; ?>>C# Programming</option>
                            <option value="Java Programming" <?php echo isset($_POST['selected_purpose']) && $_POST['selected_purpose'] == 'Java Programming' ? 'selected' : ''; ?>>Java Programming</option>
                            <option value="Php Programming" <?php echo isset($_POST['selected_purpose']) && $_POST['selected_purpose'] == 'Php Programming' ? 'selected' : ''; ?>>Php Programming</option>
                            <option value="Python Programming" <?php echo isset($_POST['selected_purpose']) && $_POST['selected_purpose'] == 'Python Programming' ? 'selected' : ''; ?>>Python Programming</option>
                            <option value="Database" <?php echo isset($_POST['selected_purpose']) && $_POST['selected_purpose'] == 'Database' ? 'selected' : ''; ?>>Database</option>
                            <option value="Digital Logic & Design" <?php echo isset($_POST['selected_purpose']) && $_POST['selected_purpose'] == 'Digital Logic & Design' ? 'selected' : ''; ?>>Digital Logic & Design</option>
                            <option value="Embedded System & IOT" <?php echo isset($_POST['selected_purpose']) && $_POST['selected_purpose'] == 'Embedded System & IOT' ? 'selected' : ''; ?>>Embedded System & IOT</option>
                            <option value="System Integration & Architecture" <?php echo isset($_POST['selected_purpose']) && $_POST['selected_purpose'] == 'System Integration & Architecture' ? 'selected' : ''; ?>>System Integration & Architecture</option>
                            <option value="Computer Application" <?php echo isset($_POST['selected_purpose']) && $_POST['selected_purpose'] == 'Computer Application' ? 'selected' : ''; ?>>Computer Application</option>
                            <option value="Web Design & Development" <?php echo isset($_POST['selected_purpose']) && $_POST['selected_purpose'] == 'Web Design & Development' ? 'selected' : ''; ?>>Web Design & Development</option>
                            <option value="Project Management" <?php echo isset($_POST['selected_purpose']) && $_POST['selected_purpose'] == 'Project Management' ? 'selected' : ''; ?>>Project Management</option>
                        </select>

                        <select name="selected_laboratory" class="border rounded px-3 py-2">
                            <option value="" <?php echo !isset($_POST['selected_laboratory']) ? 'selected' : ''; ?>>Select Laboratory</option>
                            <option value="Lab 517" <?php echo isset($_POST['selected_laboratory']) && $_POST['selected_laboratory'] == 'Lab 517' ? 'selected' : ''; ?>>Lab 517</option>
                            <option value="Lab 524" <?php echo isset($_POST['selected_laboratory']) && $_POST['selected_laboratory'] == 'Lab 524' ? 'selected' : ''; ?>>Lab 524</option>
                            <option value="Lab 526" <?php echo isset($_POST['selected_laboratory']) && $_POST['selected_laboratory'] == 'Lab 526' ? 'selected' : ''; ?>>Lab 526</option>
                            <option value="Lab 528" <?php echo isset($_POST['selected_laboratory']) && $_POST['selected_laboratory'] == 'Lab 528' ? 'selected' : ''; ?>>Lab 528</option>
                            <option value="Lab 530" <?php echo isset($_POST['selected_laboratory']) && $_POST['selected_laboratory'] == 'Lab 530' ? 'selected' : ''; ?>>Lab 530</option>
                            <option value="Lab 542" <?php echo isset($_POST['selected_laboratory']) && $_POST['selected_laboratory'] == 'Lab 542' ? 'selected' : ''; ?>>Lab 542</option>
                            <option value="Lab 544" <?php echo isset($_POST['selected_laboratory']) && $_POST['selected_laboratory'] == 'Lab 544' ? 'selected' : ''; ?>>Lab 544</option>
                        </select>

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors duration-200">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                        <button type="button" onclick="resetFilters()" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors duration-200">
                            <i class="fas fa-undo mr-2"></i>Reset
                        </button>
                    </form>
                </div>
                <script>
                    function resetFilters() {
                        document.querySelector('input[name="selected_date"]').value = '';
                        document.querySelector('select[name="selected_purpose"]').selectedIndex = 0;
                        document.querySelector('select[name="selected_laboratory"]').selectedIndex = 0;
                        document.getElementById('filterForm').submit();
                    }
                </script>
                <?php
                // Filter data based on selected criteria
                $selected_date = isset($_POST['selected_date']) ? $_POST['selected_date'] : '';
                $selected_purpose = isset($_POST['selected_purpose']) ? $_POST['selected_purpose'] : '';
                $selected_laboratory = isset($_POST['selected_laboratory']) ? $_POST['selected_laboratory'] : '';
                
                // Build the query dynamically based on filters
                $filter_query = "SELECT IDNO, FULL_NAME, PURPOSE, LABORATORY, TIME_IN, TIME_OUT, DATE FROM curr_sitin WHERE 1=1";
                
                $params = [];
                $types = '';
                
                if (!empty($selected_date)) {
                    $filter_query .= " AND DATE = ?";
                    $params[] = $selected_date;
                    $types .= 's';
                }
                
                if (!empty($selected_purpose)) {
                    $filter_query .= " AND PURPOSE = ?";
                    $params[] = $selected_purpose;
                    $types .= 's';
                }
                
                if (!empty($selected_laboratory)) {
                    $filter_query .= " AND LABORATORY = ?";
                    $params[] = $selected_laboratory;
                    $types .= 's';
                }
                
                $filter_query .= " ORDER BY DATE DESC LIMIT ? OFFSET ?";
                $params[] = $entries_per_page;
                $params[] = $offset;
                $types .= 'ii';
                
                // Use prepared statement for the filtered query
                $stmt = $conn->prepare($filter_query);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
                ?>

                <!-- Entries and Search Bar in single row -->
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
                                name="search" 
                                id="searchInput" 
                                placeholder="Search reports..." 
                                value="<?php echo isset($_POST['search']) ? htmlspecialchars($_POST['search']) : ''; ?>"
                                class="w-64 pl-10 pr-4 py-2 border border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none"
                                onkeypress="if(event.key === 'Enter') { event.preventDefault(); searchTable(); }">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>

                    <!-- Export buttons container moved to right -->
                    <div class="dt-buttons flex space-x-3">
                        <!-- DataTables will automatically insert buttons here -->
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table id="reportsTable" class="min-w-full">
                    <thead style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);" class="text-white">
                            <tr>
                                <th class="px-6 py-3 text-left">ID Number</th>
                                <th class="px-6 py-3 text-left">Name</th>
                                <th class="px-6 py-3 text-left">Purpose</th>
                                <th class="px-6 py-3 text-left">Laboratory</th>
                                <th class="px-6 py-3 text-left">Time In</th>
                                <th class="px-6 py-3 text-left">Time Out</th>
                                <th class="px-6 py-3 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Convert time to Asia/Manila timezone and 12-hour format
                                    $time_in = date('h:i A', strtotime($row['TIME_IN']));
                                    $time_out = $row['TIME_OUT'] ? date('h:i A', strtotime($row['TIME_OUT'])) : 'Active';
                                    
                                    echo "<tr class='border-b hover:bg-gray-50'>";
                                    echo "<td class='px-6 py-4'>" . $row['IDNO'] . "</td>";
                                    echo "<td class='px-6 py-4'>" . $row['FULL_NAME'] . "</td>";
                                    echo "<td class='px-6 py-4'>" . $row['PURPOSE'] . "</td>";
                                    echo "<td class='px-6 py-4'>" . $row['LABORATORY'] . "</td>";
                                    echo "<td class='px-6 py-4'>" . $time_in . "</td>";
                                    echo "<td class='px-6 py-4'>" . $time_out . "</td>";
                                    echo "<td class='px-6 py-4'>" . $row['DATE'] . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr class='border-b hover:bg-gray-50'>";
                                echo "<td colspan='7' class='px-6 py-4 text-center text-gray-500 italic'>No data available</td>";
                                echo "</tr>";
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
        function toggleNav(x) {
            document.getElementById("mySidenav").classList.toggle("-translate-x-0");
            document.getElementById("mySidenav").classList.toggle("-translate-x-full");
        }

        function closeNav() {
            document.getElementById("mySidenav").classList.remove("-translate-x-0");
            document.getElementById("mySidenav").classList.add("-translate-x-full");
        }

        function changeEntries(entries) {
            window.location.href = `admin_reports.php?entries=${entries}&page=1`;
        }

        function changePage(page) {
            const entries = document.getElementById('entriesPerPage').value;
            window.location.href = `admin_reports.php?entries=${entries}&page=${page}`;
        }

        function searchTable() {
            const searchValue = document.getElementById('searchInput').value;
            if (searchValue.length > 0) {
                document.forms[0].submit();
            }
            return false;
        }

        $(document).ready(function() {
    $('#reportsTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csv',
                className: 'relative inline-flex items-center px-6 py-2.5 border-2 border-blue-600 font-medium text-blue-600 hover:bg-blue-600 hover:text-white rounded-xl transition-all duration-300 group',
                text: `<span class="absolute w-32 h-32 -top-8 -left-2 bg-blue-600 scale-0 rounded-full group-hover:scale-100 transition-all duration-300 z-0 opacity-30"></span>
                       <span class="relative z-10 flex items-center">
                           <i class="fas fa-file-csv mr-2 transform group-hover:scale-110 transition-transform"></i>
                           Export CSV
                       </span>`,
                filename: 'ccs_laboratory_report',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'excel',
                className: 'relative inline-flex items-center px-6 py-2.5 border-2 border-blue-600 font-medium text-blue-600 hover:bg-blue-600 hover:text-white rounded-xl transition-all duration-300 group',
                text: `<span class="absolute inset-0 bg-blue-600 w-0 group-hover:w-full transition-all duration-300 z-0"></span>
                       <span class="relative z-10 flex items-center">
                           <i class="fas fa-file-excel mr-2 group-hover:animate-bounce"></i>
                           Export Excel
                       </span>`,
                filename: 'ccs_laboratory_report',
                title: 'CCS Laboratory Report',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdf',
                className: 'relative inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/50',
                text: `<span class="flex items-center">
                        <i class="fas fa-file-pdf mr-2 animate-pulse"></i>
                        Export PDF
                    </span>`,
                action: function(e, dt, node, config) {
                    exportToPDF();
                }
            },
            {
                extend: 'print',
                className: 'relative inline-flex items-center px-6 py-2.5 bg-gray-800 text-white rounded-xl transition-all duration-300 group hover:ring-2 hover:ring-offset-2 hover:ring-gray-600',
                text: `<span class="absolute inset-0 h-full w-full bg-white/10 block scale-0 group-hover:scale-100 transition-transform duration-300 rounded-xl"></span>
                       <span class="relative z-10 flex items-center">
                           <i class="fas fa-print mr-2 group-hover:rotate-12 transition-transform"></i>
                           Print Report
                       </span>`,
                action: function(e, dt, node, config) {
                    printTable();
                }
            }
        ],
        paging: false,
        searching: false,
        info: false,
        ordering: false,
        autoWidth: false
    });

    // Apply custom styling
    $('.dt-buttons').addClass('flex flex-wrap gap-4');
    $('.dt-button').addClass('!hover:no-underline');

    function printTable() {
    const printWindow = window.open('', '_blank', 'height=600,width=800');
    const title = 'CCS Laboratory Reports';
    const institutionName = 'UNIVERSITY OF CEBU';
    
    printWindow.document.write(`
        <html>
        <head>
            <title>${title} - Print View</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    padding: 20px; 
                    margin: 0;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 30px;
                }
                .logo {
                    width: 80px;
                    height: 80px;
                    margin: 0 auto 10px auto;
                    display: block.
                }
                .university-name { 
                    font-size: 20px; 
                    font-weight: bold; 
                    margin: 10px 0;
                }
                .college-name { 
                    font-size: 18px; 
                    margin: 5px 0;
                }
                .report-title { 
                    font-size: 16px; 
                    margin: 5px 0 15px 0;
                }
                .date { 
                    font-size: 12px; 
                    color: #666; 
                    margin-bottom: 20px.
                }
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin-top: 20px;
                }
                th, td { 
                    border: 1px solid #ddd; 
                    padding: 8px; 
                    text-align: left; 
                    font-size: 12px; 
                }
                th { 
                    background-color: #f2f2f2; 
                    font-weight: bold.
                }
                tr:nth-child(even) { 
                    background-color: #f9f9f9; 
                }
                .print-button {
                    text-align: center;
                    margin-top: 20px;
                }
                .print-button button {
                    padding: 10px 20px;
                    background-color: #4CAF50;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer.
                }
                @media print {
                    .print-button { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <img src="../logo/uc.png" class="logo" alt="UC Logo">
                <div class="university-name">${institutionName}</div>
                <div class="college-name">College of Computer Studies</div>
                <div class="report-title">Laboratory Reports</div>
                <div class="date">Generated on: ${new Date().toLocaleString()}</div>
            </div>
            
            <table>${document.getElementById('reportsTable').outerHTML}</table>
            
            <div class="print-button">
                <button onclick="window.print();window.close();">
                    Print Document
                </button>
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
}

function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'pt', 'a4');
    
    // Add UC Logo
    const logo = new Image();
    logo.src = '../logo/uc.png';
    
    logo.onload = function() {
        // Add logo
        const pageWidth = doc.internal.pageSize.width;
        const logoWidth = 70;
        const logoX = (pageWidth - logoWidth) / 2;
        doc.addImage(logo, 'PNG', logoX, 30, logoWidth, logoWidth);

        // Add header text
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(16);
        doc.text('UNIVERSITY OF CEBU', pageWidth/2, 120, { align: 'center' });
        
        doc.setFontSize(14);
        doc.setFont('helvetica', 'normal');
        doc.text('College of Computer Studies', pageWidth/2, 140, { align: 'center' });
        doc.text('Laboratory Reports', pageWidth/2, 160, { align: 'center' });
        
        // Add date
        doc.setFontSize(10);
        doc.text(`Generated on: ${new Date().toLocaleString()}`, pageWidth/2, 180, { align: 'center' });

        // Get table data
        const table = document.getElementById('reportsTable');
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent);
        const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr => 
            Array.from(tr.querySelectorAll('td')).map(td => td.textContent)
        );

        // Add table using autoTable
        doc.autoTable({
            head: [headers],
            body: rows,
            startY: 200,
            theme: 'grid',
            headStyles: {
                fillColor: [37, 99, 235], // #2563eb in RGB
                textColor: 255,
                fontSize: 10,
                halign: 'center'
            },
            bodyStyles: {
                fontSize: 9,
                halign: 'center'
            },
            alternateRowStyles: {
                fillColor: [245, 245, 245]
            },
            margin: { top: 200 },
            didDrawPage: function(data) {
                // Add page number at the bottom
                doc.setFontSize(10);
                doc.text(`Page ${doc.internal.getNumberOfPages()}`, data.settings.margin.left, doc.internal.pageSize.height - 10);
            }
        });

        // Save the PDF
        doc.save('ccs_laboratory_report.pdf');
    };
}

});

    </script>
</body>
</html>