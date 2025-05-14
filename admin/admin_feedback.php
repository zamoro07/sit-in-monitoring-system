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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../logo/ccs.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <title>Admin Feedback</title>
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
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <img src="../logo/ccs.png" alt="Logo" class="w-10 h-10">
                    <h1 class="font-bold text-xl">CCS SIT-IN MONITORING SYSTEM</h1>
                </div>
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
        <?php
        $total_query = "SELECT COUNT(*) as total FROM feedback";
        $total_result = $conn->query($total_query);
        $total_row = $total_result->fetch_assoc();
        $total_entries = $total_row['total'];

        $avg_query = "SELECT AVG(rating) as avg_rating FROM feedback WHERE rating > 0";
        $avg_result = $conn->query($avg_query);
        $avg_row = $avg_result->fetch_assoc();
        $avg_rating = number_format($avg_row['avg_rating'], 1);

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
            <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Student Feedbacks</h2>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-4">
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
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr class='hover:bg-gray-50 feedback-row'>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['IDNO']) . "</td>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['LABORATORY']) . "</td>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['DATE']) . "</td>";
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
                    
                    echo "<button onclick=\"changePage(1)\" " . ($current_page == 1 ? 'disabled' : '') . " 
                          class=\"px-3.5 py-2 text-sm bg-white border border-gray-300 rounded-l-lg hover:bg-gray-50 text-gray-500" . 
                          ($current_page == 1 ? ' opacity-50 cursor-not-allowed' : '') . "\">
                          <i class=\"fas fa-angle-double-left\"></i>
                      </button>";
                    $prev_page = max(1, $current_page - 1);
                    echo "<button onclick=\"changePage($prev_page)\" " . ($current_page == 1 ? 'disabled' : '') . " 
                          class=\"px-3.5 py-2 text-sm bg-white border-t border-b border-l border-gray-300 hover:bg-gray-50 text-gray-500" . 
                          ($current_page == 1 ? ' opacity-50 cursor-not-allowed' : '') . "\">
                          <i class=\"fas fa-angle-left\"></i>
                      </button>";
                    for($i = 1; $i <= $total_pages; $i++) {
                        if($i == $current_page) {
                            echo "<button class=\"px-3.5 py-2 text-sm bg-blue-600 text-white border border-blue-600\">$i</button>";
                        } else {
                            echo "<button onclick=\"changePage($i)\" 
                                  class=\"px-3.5 py-2 text-sm bg-white border border-gray-300 hover:bg-gray-50 text-gray-700\">$i</button>";
                        }
                    }
                    $next_page = min($total_pages, $current_page + 1);
                    echo "<button onclick=\"changePage($next_page)\" " . ($current_page == $total_pages ? 'disabled' : '') . "
                          class=\"px-3.5 py-2 text-sm bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 text-gray-500" . 
                          ($current_page == $total_pages ? ' opacity-50 cursor-not-allowed' : '') . "\">
                          <i class=\"fas fa-angle-right\"></i>
                      </button>";
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
   
    <script>
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