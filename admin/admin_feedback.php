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
    <title>Feedback</title>
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
            <nav class="flex flex-col space-y-1">
                <a href="admin_dashboard.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-home w-5 mr-2 text-center"></i>
                    <span class="font-medium">HOME</span>
                </a>
                <a href="admin_search.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-search w-5 mr-2 text-center"></i>
                    <span class="font-medium">SEARCH</span>
                </a>
                <a href="admin_sitin.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-user-check w-5 mr-2 text-center"></i>
                    <span class="font-medium">SIT-IN</span>
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
                    
                    <div x-show="open" class="pl-7 mt-2 space-y-1">
                        <a href="admin_sitinrec.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-book w-5 mr-2 text-center"></i>
                            <span class="font-medium">Sit-in Records</span>
                        </a>
                        <a href="admin_studlist.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-list w-5 mr-2 text-center"></i>
                            <span class="font-medium">List of Students</span>
                        </a>
                        <a href="admin_feedback.php" class="group px-3 py-2 text-white/90 bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-comments w-5 mr-2 text-center"></i>
                            <span class="font-medium">Feedbacks</span>
                        </a>
                        <a href="#" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-chart-pie w-5 mr-2 text-center"></i>
                            <span class="font-medium">Daily Analytics</span>
                        </a>
                    </div>
                </div>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center justify-between">
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
                        
                        <a href="admin_resources.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-box-open w-5 mr-2 text-center"></i>
                            <span class="font-medium">Resources</span>
                        </a>
                        
                        <a href="admin_lab_schedule.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-calendar-alt w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Schedule</span>
                        </a>
                        
                        <a href="admin_lab_usage.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                            <i class="fas fa-chart-bar w-5 mr-2 text-center"></i>
                            <span class="font-medium">Lab Usage Point</span>
                        </a>
                    </div>
                </div>

                <a href="admin_reports.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-chart-line w-5 mr-2 text-center"></i>
                    <span class="font-medium">SIT-IN REPORT</span>
                </a>

                <a href="admin_reservation.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-calendar-check w-5 mr-2 text-center"></i>
                    <span class="font-medium">RESERVATION/APPROVAL</span>
                </a>
                
                <div class="border-t border-white/10 my-2"></div>
                
                <a href="../logout.php" class="group px-3 py-2 text-white/90 hover:bg-red-500/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-sign-out-alt w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">LOG OUT</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Add the feedback table container -->
    <div class="content-container w-11/12 mx-auto my-8 bg-white p-6 rounded-lg shadow-lg overflow-hidden border border-gray-200">
        <?php
        // Get total count of feedbacks first
        $total_query = "SELECT COUNT(*) as total FROM feedback";
        $total_result = $conn->query($total_query);
        $total_row = $total_result->fetch_assoc();
        $total_entries = $total_row['total'];

        // Get average rating
        $avg_query = "SELECT AVG(rating) as avg_rating FROM feedback WHERE rating > 0";
        $avg_result = $conn->query($avg_query);
        $avg_row = $avg_result->fetch_assoc();
        $avg_rating = number_format($avg_row['avg_rating'], 1);

        // Get rating distribution
        $dist_query = "SELECT rating, COUNT(*) as count FROM feedback WHERE rating > 0 GROUP BY rating ORDER BY rating DESC";
        $dist_result = $conn->query($dist_query);
        $total_ratings = 0;
        $ratings_dist = array();
        while($row = $dist_result->fetch_assoc()) {
            $total_ratings += $row['count'];
            $ratings_dist[$row['rating']] = $row['count'];
        }
        ?>
        
        <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
             style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
            <i class="fas fa-comments text-2xl mr-4 relative z-10"></i>
            <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Student Feedbacks</h2>
        </div>

        <!-- Statistics Section Moved Here -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Average Rating Card -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium uppercase">Average Rating</h3>
                    <span class="text-white"><i class="fas fa-star text-2xl"></i></span>
                </div>
                <div class="mt-4 flex items-baseline">
                    <p class="text-4xl font-bold"><?php echo $avg_rating; ?></p>
                    <p class="ml-2 text-lg">/5.0</p>
                </div>
                <p class="mt-2 text-sm">Based on <?php echo $total_ratings; ?> ratings</p>
            </div>

            <!-- Rating Distribution -->
            <div class="bg-gradient-to-br from-blue-600 to-blue-700 text-white p-6 rounded-lg shadow-lg md:col-span-2">
                <h3 class="text-sm font-medium uppercase mb-4">Rating Distribution</h3>
                <?php
                for ($i = 5; $i >= 1; $i--) {
                    $count = isset($ratings_dist[$i]) ? $ratings_dist[$i] : 0;
                    $percentage = $total_ratings > 0 ? ($count / $total_ratings) * 100 : 0;
                    ?>
                    <div class="flex items-center mb-3">
                        <span class="text-sm w-16"><?php echo $i; ?> stars</span>
                        <div class="flex-1 mx-3 h-3 rounded-full bg-white/20">
                            <div class="h-3 rounded-full bg-yellow-300" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                        <span class="text-sm"><?php echo number_format($percentage, 1); ?>%</span>
                    </div>
                <?php } ?>
            </div>

            <!-- Total Feedback Card -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium uppercase">Total Feedback</h3>
                    <span class="text-white"><i class="fas fa-comments text-2xl"></i></span>
                </div>
                <p class="mt-4 text-4xl font-bold"><?php echo $total_entries; ?></p>
                <p class="mt-2 text-sm">All time feedback count</p>
            </div>
        </div>

        <div class="p-6">
            <!-- Redesigned entries and search controls -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <!-- Entries per page selector -->
                    <div class="flex items-center bg-gray-50 rounded-lg p-2 shadow-sm">
                        <label class="text-gray-600 mr-2 text-sm">Show</label>
                        <select id="entriesSelect" onchange="changeEntries(this.value)" 
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
                            placeholder="Search feedbacks..." 
                            class="w-64 pl-10 pr-4 py-2 border border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none"
                            onkeypress="if(event.key === 'Enter') { event.preventDefault(); searchTable(); }">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <?php
                    // UPDATED: Sort by DATE DESC to get latest feedback first
                    $query = "SELECT * FROM feedback ORDER BY DATE DESC, FEEDBACK_ID DESC LIMIT $entries_per_page OFFSET $offset";
                    $result = $conn->query($query);
                    ?>
                     <thead style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);" class="text-white">
                        <tr>
                            <th class="px-6 py-3 text-left">Student ID</th>
                            <th class="px-6 py-3 text-left">Laboratory</th>
                            <th class="px-6 py-3 text-left">Date</th>
                            <th class="px-6 py-3 text-left">Rating</th>
                            <th class="px-6 py-3 text-left">Feedback</th>
                            <th class="px-6 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch all feedbacks
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr class='hover:bg-gray-50 feedback-row'>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['IDNO']) . "</td>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['LABORATORY']) . "</td>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['DATE']) . "</td>";
                                // Add rating display with stars
                                echo "<td class='px-6 py-4'>";
                                for($i = 1; $i <= 5; $i++) {
                                    $starClass = $i <= $row['RATING'] ? 'fas fa-star text-yellow-400' : 'far fa-star text-gray-300';
                                    echo "<i class='$starClass'></i>";
                                }
                                echo "</td>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['FEEDBACK']) . "</td>";
                                echo "<td class='px-6 py-4'>";
                                echo "<button onclick=\"deleteFeedback(" . $row['FEEDBACK_ID'] . ")\" 
                                        class='text-red-500 hover:text-red-700 transition-colors text-lg'>
                                        <i class='fas fa-trash-alt'></i>
                                      </button>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='px-6 py-8 text-center text-gray-500'>";
                            echo "<div class='flex flex-col items-center justify-center'>";
                            echo "<i class='fas fa-comments text-4xl mb-2 text-gray-400'></i>";
                            echo "<p class='text-lg font-medium'>No feedbacks found</p>";
                            echo "<p class='text-sm'>Student feedbacks will appear here once they are submitted.</p>";
                            echo "</div></td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Updated pagination controls -->
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mt-6 gap-4">
                <div class="text-gray-600 text-sm">
                    <?php
                    $start_entry = $total_entries > 0 ? $offset + 1 : 0;
                    $end_entry = min($offset + $entries_per_page, $total_entries);
                    echo "Showing <span class='font-semibold'>$start_entry</span> to <span class='font-semibold'>$end_entry</span> of <span class='font-semibold'>$total_entries</span> entries";
                    ?>
                </div>
                <div class="inline-flex rounded-lg shadow-sm">
                    <?php
                    $total_pages = ceil($total_entries / $entries_per_page);
                    
                    // First page button
                    echo "<button onclick=\"changePage(1)\" " . ($current_page == 1 ? 'disabled' : '') . " 
                          class=\"px-3.5 py-2 text-sm bg-white border border-gray-300 rounded-l-lg hover:bg-gray-50 text-gray-500" . 
                          ($current_page == 1 ? ' opacity-50 cursor-not-allowed' : '') . "\">
                          <i class=\"fas fa-angle-double-left\"></i>
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
                          <i class=\"fas fa-angle-double-right\"></i>
                      </button>";
                    ?>
                </div>
            </div>

        </div>
    </div>

     <!-- Footer -->
    <div class="py-4 px-6 bg-white/95 backdrop-blur-sm mt-8 relative">
        <div class="absolute inset-x-0 top-0 h-1" 
             style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)"></div>
        <p class="text-center text-sm text-gray-600">
            &copy; 2025 CCS Sit-in Monitoring System | <span class="gradient-text font-medium">UC - College of Computer Studies</span>
        </p>
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

        function deleteFeedback(feedbackId) {
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
                    fetch('delete_feedback.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'feedback_id=' + feedbackId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
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
                                title: 'Feedback deleted successfully',
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
                                title: data.message || 'Failed to delete feedback',
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
                            title: 'Error deleting feedback',
                            background: '#EF4444'
                        });
                    });
                }
            });
        }

        function changeEntries(entries) {
            window.location.href = `admin_feedback.php?entries=${entries}&page=1`;
        }

        function changePage(page) {
            const entries = document.getElementById('entriesSelect').value;
            window.location.href = `admin_feedback.php?entries=${entries}&page=${page}`;
        }

        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.querySelector('table');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    const cellText = cells[j].textContent || cells[j].innerText;
                    if (cellText.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }

                rows[i].style.display = found ? '' : 'none';
            }

            // Show "No results found" message if no matches
            const noResults = document.getElementById('noResults');
            const hasVisibleRows = Array.from(rows).slice(1).some(row => row.style.display !== 'none');
            
            if (!hasVisibleRows && filter !== '') {
                if (!noResults) {
                    const tbody = table.querySelector('tbody');
                    const messageRow = document.createElement('tr');
                    messageRow.id = 'noResults';
                    messageRow.innerHTML = `
                        <td colspan="6" class="px-6 py-10 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-search text-4xl text-gray-300 mb-3"></i>
                                <div class="text-gray-500 font-medium">No matching records found</div>
                                <div class="text-sm text-gray-400 mt-1">Try adjusting your search criteria</div>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(messageRow);
                }
            } else if (noResults) {
                noResults.remove();
            }
        }
    </script>
</body>
</html>