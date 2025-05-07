<?php
session_start();
require '../db.php'; // Updated path

// Initialize pagination variables
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $entries_per_page;

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
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.css">
    <!-- SweetAlert2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.all.min.js"></script>
    <title>History</title>
    <style>
        /* Add gradient text class for the footer */
        .gradient-text {
            background: linear-gradient(to right, #2563eb, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
        
        /* Custom animation for the sidebar */
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        
        .animate-slide-in {
            animation: slideIn 0.3s ease-out forwards;
        }
        
        /* Frosted glass effect */
        .frosted-glass {
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.15);
        }

        .shadow-custom {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .shadow-hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }
        
        .bg-gradient-custom {
            background: linear-gradient(to bottom right, #2563eb, #3b82f6);
        }

        .btn-gradient {
            background: linear-gradient(to bottom right, #2563eb, #3b82f6);
        }

        .accent-gradient {
            background: linear-gradient(to right, #2563eb, #3b82f6);
        }

        .hover\:bg-blue-600:hover {
            --tw-bg-opacity: 1;
            background-color: rgb(37, 99, 235);
        }

        .bg-blue-500 {
            --tw-bg-opacity: 1;
            background-color: rgb(59, 130, 246);
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif']
                    },
                    boxShadow: {
                        'custom': '0 4px 20px rgba(0, 0, 0, 0.1)',
                        'hover': '0 8px 30px rgba(0, 0, 0, 0.15)'
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen font-poppins" style="background: white">
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
                <a href="dashboard.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-home w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">HOME</span>
                </a>
                <a href="profile.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-user w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">PROFILE</span>
                </a>
                <a href="edit.php" class="group px-3 py-2 text-white/90 hover:bg-white/10 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-edit w-5 mr-2 text-center"></i>
                    <span class="font-medium group-hover:translate-x-1 transition-transform">EDIT</span>
                </a>
                <a href="history.php" class="group px-3 py-2 text-white/90 bg-white/20 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-history w-5 mr-2 text-center"></i>
                    <span class="font-medium">HISTORY</span>
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

    <!-- History Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white/95 rounded-2xl shadow-custom backdrop-blur-sm overflow-hidden border border-white/20 transition-shadow duration-300 hover:shadow-hover">
            <!-- Keep the original header design as requested -->
            <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">History Information</h2>
            </div>
            
            <div class="p-6">
                <!-- Redesigned controls -->
                <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                    <div class="flex items-center bg-gray-50 rounded-lg p-2 shadow-sm">
                        <label class="text-gray-600 mr-2 text-sm">Show</label>
                        <select id="entriesPerPage" onchange="changeEntries(this.value)" class="bg-white border border-gray-200 rounded-md px-3 py-1.5 shadow-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none">
                            <option value="10" <?php echo $entries_per_page == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo $entries_per_page == 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $entries_per_page == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $entries_per_page == 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                        <span class="text-gray-600 ml-2 text-sm">entries</span>
                    </div>
                    
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search records..." 
                            class="w-full md:w-64 pl-10 pr-4 py-2 border border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none"
                            onkeypress="if(event.key === 'Enter') { event.preventDefault(); searchTable(); }">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <!-- Redesigned table -->
                <div class="overflow-x-auto rounded-xl shadow-sm border border-gray-100">
                    <table class="min-w-full">
                        <thead>
                            <tr style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)" class="text-white">
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">ID Number</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Purpose</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Laboratory</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Time In</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Time Out</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                        <?php
                        // Get user's IDNO
                        $getUserQuery = "SELECT IDNO FROM users WHERE STUD_NUM = ?";
                        $stmt = $conn->prepare($getUserQuery);
                        $stmt->bind_param("i", $_SESSION['user_id']);
                        $stmt->execute();
                        $userResult = $stmt->get_result();
                        $userData = $userResult->fetch_assoc();
                        $stmt->close();

                        if ($userData) {
                            $userIdno = $userData['IDNO'];
                            
                            // Get total count of records for this user
                            $total_query = "SELECT COUNT(*) as total FROM curr_sitin WHERE IDNO = ?";
                            $stmt = $conn->prepare($total_query);
                            $stmt->bind_param("i", $userIdno);
                            $stmt->execute();
                            $total_result = $stmt->get_result();
                            $total_row = $total_result->fetch_assoc();
                            $total_entries = $total_row['total'];
                            $stmt->close();

                            // Fetch sit-in history with pagination
                            $query = "SELECT * FROM curr_sitin WHERE IDNO = ? ORDER BY DATE DESC, TIME_IN DESC LIMIT ? OFFSET ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("iii", $userIdno, $entries_per_page, $offset);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                $rowNum = 0;
                                while ($row = $result->fetch_assoc()) {
                                    $rowClass = $rowNum % 2 === 0 ? "bg-white" : "bg-gray-50";
                                    $rowNum++;
                                    
                                    echo "<tr class='$rowClass hover:bg-indigo-50 transition-colors'>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800'>" . htmlspecialchars($row['IDNO']) . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-700'>" . htmlspecialchars($row['FULL_NAME']) . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-700'>" . htmlspecialchars($row['PURPOSE']) . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-700'>" . htmlspecialchars($row['LABORATORY']) . "</td>";
                                    
                                    // Convert time format
                                    $timeIn = date('h:i A', strtotime($row['TIME_IN']));
                                    $timeOut = $row['TIME_OUT'] ? date('h:i A', strtotime($row['TIME_OUT'])) : '-';
                                    
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-700'>" . $timeIn . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-700'>" . $timeOut . "</td>";
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-700'>" . htmlspecialchars($row['DATE']) . "</td>";
                                    
                                    // Status with custom styling
                                    $statusClass = '';
                                    if ($row['STATUS'] == 'completed') {
                                        $statusClass = 'bg-green-100 text-green-800';
                                    } elseif ($row['STATUS'] == 'pending') {
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                    } elseif ($row['STATUS'] == 'cancelled') {
                                        $statusClass = 'bg-red-100 text-red-800';
                                    } else {
                                        $statusClass = 'bg-blue-100 text-blue-800';
                                    }
                                    
                                    echo "<td class='px-6 py-4 whitespace-nowrap'>";
                                    echo "<span class='px-2 py-1 text-xs font-medium rounded-full $statusClass'>" . htmlspecialchars($row['STATUS']) . "</span>";
                                    echo "</td>";
                                    
                                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm'>";
                                    echo "<button onclick=\"openFeedbackModal(" . $row['SITIN_ID'] . ", '" . $row['LABORATORY'] . "')\" 
                                            class='btn-gradient text-white px-3 py-1.5 rounded-lg text-sm font-medium transition duration-200 flex items-center'>
                                            <i class='fas fa-comment-alt mr-1.5'></i> Feedback
                                          </button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' class='px-6 py-10 text-center'>";
                                echo "<div class='flex flex-col items-center justify-center'>";
                                echo "<i class='fas fa-history text-5xl text-gray-300 mb-3'></i>";
                                echo "<div class='text-gray-500 font-medium text-lg'>No sit-in records found</div>";
                                echo "<div class='text-sm text-gray-400 mt-1 max-w-md'>Your sit-in history will appear here once you start using the facilities</div>";
                                echo "</div>";
                                echo "</td></tr>";
                            }
                            $stmt->close();
                        } else {
                            echo "<tr><td colspan='9' class='px-6 py-10 text-center'>";
                            echo "<div class='flex flex-col items-center justify-center'>";
                            echo "<i class='fas fa-user-plus text-5xl text-gray-300 mb-3'></i>";
                            echo "<div class='text-gray-500 font-medium text-lg'>Welcome new user!</div>";
                            echo "<div class='text-sm text-gray-400 mt-1 max-w-md'>Your sit-in history will be displayed here after your first facility use</div>";
                            echo "</div>";
                            echo "</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>

                <!-- Redesigned pagination -->
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
                                echo "<button class=\"px-3.5 py-2 text-sm bg-blue-500 text-white border border-blue-500\">$i</button>";
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
    </div>

    <!-- Redesigned Feedback Modal -->
    <div id="feedbackModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md mx-4 shadow-xl transform transition-all duration-300 scale-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Submit Feedback</h2>
                <button onclick="closeFeedbackModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="w-full h-1 accent-gradient rounded-full mb-5"></div>
            
            <form id="feedbackForm" onsubmit="submitFeedback(event)">
                <input type="hidden" id="sitinId" name="sitinId">
                <input type="hidden" id="laboratory" name="laboratory">
                <input type="hidden" id="rating" name="rating" value="0">
                
                <!-- Star Rating -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rate your experience:</label>
                    <div class="flex items-center space-x-1" id="starRating">
                        <i class="far fa-star text-2xl cursor-pointer text-yellow-400 hover:text-yellow-500" data-rating="1"></i>
                        <i class="far fa-star text-2xl cursor-pointer text-yellow-400 hover:text-yellow-500" data-rating="2"></i>
                        <i class="far fa-star text-2xl cursor-pointer text-yellow-400 hover:text-yellow-500" data-rating="3"></i>
                        <i class="far fa-star text-2xl cursor-pointer text-yellow-400 hover:text-yellow-500" data-rating="4"></i>
                        <i class="far fa-star text-2xl cursor-pointer text-yellow-400 hover:text-yellow-500" data-rating="5"></i>
                    </div>
                </div>
                
                <div class="mb-5">
                    <label for="feedbackText" class="block text-sm font-medium text-gray-700 mb-2">Your feedback:</label>
                    <textarea id="feedbackText" name="feedback" rows="4" 
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none" 
                        placeholder="Share your experience with this lab session..." 
                        required></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeFeedbackModal()" 
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 btn-gradient text-white rounded-lg hover:bg-blue-600 font-medium transition-colors flex items-center">
                        <i class="fas fa-paper-plane mr-2"></i> Submit Feedback
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
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

        function openFeedbackModal(sitinId, laboratory) {
            const modal = document.getElementById('feedbackModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('sitinId').value = sitinId;
            document.getElementById('laboratory').value = laboratory;
            
            // Add entrance animation
            const modalContent = modal.querySelector('div');
            modalContent.classList.add('scale-100');
            modalContent.classList.remove('scale-95');
        }

        function closeFeedbackModal() {
            const modal = document.getElementById('feedbackModal');
            const modalContent = modal.querySelector('div');
            
            // Add exit animation
            modalContent.classList.add('scale-95');
            modalContent.classList.remove('scale-100');
            
            // Delay hiding the modal to allow animation to complete
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function submitFeedback(event) {
            event.preventDefault();
            const formData = new FormData(document.getElementById('feedbackForm'));
            const rating = document.getElementById('rating').value;
            
            if (rating === '0') {
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
                    icon: 'warning',
                    title: 'Please select a rating',
                    background: '#F59E0B'
                });
                return;
            }

            fetch('feedback_submit.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
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
                        title: 'Feedback submitted successfully',
                        background: '#10B981'
                    }).then(() => {
                        closeFeedbackModal();
                    });
                } else {
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
                        icon: 'error',
                        title: data.message || 'Error submitting feedback',
                        background: '#EF4444'
                    });
                }
            })
            .catch(error => {
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
                    icon: 'error',
                    title: 'Network error. Please try again',
                    background: '#EF4444'
                });
            });
        }

        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.querySelector('table');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) { // Start from 1 to skip header row
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
                        <td colspan="9" class="px-6 py-10 text-center">
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

        function changeEntries(entries) {
            window.location.href = `history.php?entries=${entries}&page=1`;
        }

        function changePage(page) {
            const entries = document.getElementById('entriesPerPage').value;
            window.location.href = `history.php?entries=${entries}&page=${page}`;
        }

        // Add this to your existing JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            const starContainer = document.getElementById('starRating');
            const stars = starContainer.getElementsByTagName('i');
            const ratingInput = document.getElementById('rating');

            // Handle star rating
            Array.from(stars).forEach(star => {
                star.addEventListener('mouseover', function() {
                    const rating = this.dataset.rating;
                    highlightStars(rating);
                });

                star.addEventListener('click', function() {
                    const rating = this.dataset.rating;
                    ratingInput.value = rating;
                    setStars(rating);
                });
            });

            starContainer.addEventListener('mouseout', function() {
                const currentRating = ratingInput.value;
                if (currentRating > 0) {
                    setStars(currentRating);
                } else {
                    resetStars();
                }
            });
        });

        function highlightStars(rating) {
            const stars = document.querySelectorAll('#starRating i');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.remove('far');
                    star.classList.add('fas');
                } else {
                    star.classList.remove('fas');
                    star.classList.add('far');
                }
            });
        }

        function setStars(rating) {
            highlightStars(rating);
            document.getElementById('rating').value = rating;
        }

        function resetStars() {
            const stars = document.querySelectorAll('#starRating i');
            stars.forEach(star => {
                star.classList.remove('fas');
                star.classList.add('far');
            });
        }
    </script>
</body>
</html>