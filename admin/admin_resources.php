<?php
session_start();
require '../db.php'; // Add database connection

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Handle form submission for adding a new resource
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resource_submit'])) {
    // Get form data
    $title = $_POST['title'];
    $professor = $_POST['professor']; // Added professor field
    $description = $_POST['description'];
    $link = $_POST['link'];
    
    // Debug - print received data
    // echo "Title: $title, Professor: $professor, Description: $description, Link: $link";
    
    // Handle file upload if image is provided
    $imageData = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
        
        // Make sure we actually got the image data
        if (!$imageData) {
            $_SESSION['toast'] = [
                'status' => 'error',
                'message' => 'Failed to read image file'
            ];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
    
    // Debugging - check if we're reaching this point
    // echo "About to execute query";
    
    // Prepare and execute query - updated to include PROFESSOR column
    $stmt = $conn->prepare("INSERT INTO resources (RESOURCES_NAME, PROFESSOR, DESCRIPTION, RESOURCES_LINK, RESOURCES_IMAGE) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        $_SESSION['toast'] = [
            'status' => 'error',
            'message' => 'Failed to prepare statement: ' . $conn->error
        ];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    $stmt->bind_param("sssss", $title, $professor, $description, $link, $imageData);
    
    if ($stmt->execute()) {
        // Set success message
        $_SESSION['toast'] = [
            'status' => 'success',
            'message' => 'Resource added successfully!'
        ];
    } else {
        // Set error message
        $_SESSION['toast'] = [
            'status' => 'error',
            'message' => 'Failed to add resource: ' . $stmt->error
        ];
    }
    
    $stmt->close();
    
    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch resources from database - updated to include PROFESSOR field
$resources = [];
$result = $conn->query("SELECT RESOURCES_ID, RESOURCES_NAME, PROFESSOR, DESCRIPTION, RESOURCES_LINK, RESOURCES_IMAGE, CREATED_AT FROM resources ORDER BY CREATED_AT DESC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $resources[] = $row;
    }
}

// Display toast notifications if any
if (isset($_SESSION['toast'])) {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', functizon() {
            showToast('<?php echo $_SESSION['toast']['status']; ?>', '<?php echo $_SESSION['toast']['message']; ?>');
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../logo/ccs.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <title>Lab Resources</title>
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

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">Lab Resources Management</h2>
            </div>
            <div class="p-6">
                <!-- Modern Futuristic Header -->
                <div class="flex flex-wrap items-center justify-between mb-8">
                    <div class="flex items-center mt-4 md:mt-0">
                        <!-- Modern Search Field -->
                        <div class="relative mr-4">
                            <input type="text" placeholder="Find resources..." 
                                   class="pl-10 pr-4 py-2.5 w-60 rounded-full border-0 bg-white/80 backdrop-blur-md
                                         shadow-inner focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-indigo-500">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        <!-- Add Resource Button -->
                        <button onclick="toggleModal('addResourceModal')" 
                                class="bg-gradient-to-br from-indigo-600 to-purple-700 text-white font-medium py-2.5 px-5 rounded-full hover:shadow-md transition-all">
                            <i class="fas fa-plus mr-2"></i> Add Resource
                        </button>
                    </div>
                </div>
                
                <!-- Resource Cards Grid with 3D Effect -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                    <!-- Display resources from database -->
                    <?php if (empty($resources)): ?>
                        <div class="col-span-full text-center py-12">
                            <div class="bg-indigo-50 rounded-xl p-8 max-w-md mx-auto">
                                <i class="fas fa-folder-open text-6xl text-indigo-300 mb-4"></i>
                                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Resources Found</h3>
                                <p class="text-gray-600">Start adding educational resources to build your collection</p>
                                <button onclick="toggleModal('addResourceModal')" 
                                        class="mt-6 bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition-colors">
                                    <i class="fas fa-plus mr-2"></i> Add First Resource
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Resource cards from database -->
                        <?php foreach ($resources as $index => $resource): ?>
                            <?php 
                            // Determine card style based on index for variety
                            $cardClass = "";
                            $cardType = $index % 4;
                            switch($cardType) {
                                case 0:
                                    $cardClass = "resource-card-3d";
                                    break;
                                case 1:
                                    $cardClass = "glass-card";
                                    break;
                                case 2:
                                    $cardClass = "gradient-border";
                                    break;
                                case 3:
                                    $cardClass = "resource-card-float";
                                    break;
                            }
                            ?>
                            <div class="relative bg-gradient-to-br from-blue-50 to-white rounded-xl p-6 hover:shadow-md transition-all">
                                <?php if ($cardType === 0): ?>
                                    <div class="card-shine rounded-xl"></div>
                                <?php endif; ?>
                                
                                <div class="h-40 overflow-hidden">
                                    <?php if ($resource['RESOURCES_IMAGE']): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($resource['RESOURCES_IMAGE']); ?>" 
                                             alt="<?php echo htmlspecialchars($resource['RESOURCES_NAME']); ?>" 
                                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                                            <i class="fas fa-book-open text-4xl text-indigo-300"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="p-5 bg-white">
                                    <h3 class="font-bold text-gray-800 text-lg mb-1"><?php echo htmlspecialchars($resource['RESOURCES_NAME']); ?></h3>
                                    
                                    <!-- Display professor name if available -->
                                    <?php if (!empty($resource['PROFESSOR'])): ?>
                                    <div class="text-sm text-gray-500 mb-2">
                                        <i class="fas fa-user-tie mr-1 text-indigo-500"></i> 
                                        <?php echo htmlspecialchars($resource['PROFESSOR']); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <p class="text-gray-600 text-sm mb-4 line-clamp-2 <?php echo $cardType === 3 ? 'floating-label' : ''; ?>">
                                        <?php echo htmlspecialchars($resource['DESCRIPTION']); ?>
                                    </p>
                                    
                                    <!-- Modified to center the Open link and remove edit/delete buttons -->
                                    <div class="<?php echo $cardType === 3 ? 'floating-actions' : ''; ?> flex justify-center items-center">
                                        <a href="<?php echo htmlspecialchars($resource['RESOURCES_LINK']); ?>" target="_blank" 
                                           class="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center group/link">
                                            <span class="group-hover/link:mr-2 transition-all duration-300">Open</span>
                                            <i class="fas fa-external-link-alt transform group-hover/link:translate-x-1 transition-transform"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Recently Added Section -->
                <div class="bg-white rounded-xl p-6 shadow-md mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-gray-800 text-lg">Recently Added</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead style="background: white" class="text-white">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resource</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Professor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php 
                                // Show only up to 5 recent resources
                                $recentResources = array_slice($resources, 0, 5);
                                if (empty($recentResources)): 
                                ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        No resources added yet.
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($recentResources as $resource): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-indigo-100 rounded-md flex items-center justify-center mr-3">
                                                    <i class="fas fa-file-alt text-indigo-600"></i>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($resource['RESOURCES_NAME']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-500">
                                                <?php echo !empty($resource['PROFESSOR']) ? htmlspecialchars($resource['PROFESSOR']) : '-'; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 max-w-xs">
                                            <div class="text-sm text-gray-500 truncate">
                                                <?php echo htmlspecialchars($resource['DESCRIPTION']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('Y-m-d H:i', strtotime($resource['CREATED_AT'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex space-x-2 justify-end">
                                                <a href="<?php echo htmlspecialchars($resource['RESOURCES_LINK']); ?>" target="_blank" 
                                                   class="text-indigo-600 hover:text-indigo-900">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                                <button class="text-blue-600 hover:text-blue-900">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="text-red-600 hover:text-red-900"
                                                        onclick="confirmDeleteResource(<?php echo $resource['RESOURCES_ID']; ?>)">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

 

    <!-- Modern Add Resource Modal (Hidden by default) - Redesigned to be wider with two columns -->
    <div id="addResourceModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full p-0 transform scale-95 opacity-0 transition-all duration-300">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-2xl px-6 py-4 text-white">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <div class="bg-white/20 p-2 rounded-lg backdrop-blur-sm">
                            <i class="fas fa-file-upload"></i>
                        </div>
                        <h3 class="text-xl font-bold">New Resource</h3>
                    </div>
                    <button onclick="toggleModal('addResourceModal')" class="text-white/80 hover:text-white hover:bg-white/20 p-1 rounded-full transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Modal Body - Redesigned as a two-column layout -->
            <div class="p-6">
                <form method="post" action="" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-5">
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="image">
                                    Cover Image
                                </label>
                                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center bg-gray-50 h-[180px] flex items-center justify-center">
                                    <input type="file" id="image" name="image" class="hidden" accept="image/*">
                                    <label for="image" class="cursor-pointer">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center mb-3">
                                                <i class="fas fa-cloud-upload-alt text-xl text-indigo-600"></i>
                                            </div>
                                            <p class="text-sm text-gray-600 font-medium">Drop files here or click to upload</p>
                                            <p class="text-xs text-gray-500 mt-1">PNG, JPG up to 5MB</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="description">
                                    Description
                                </label>
                                <textarea id="description" name="description" rows="4" 
                                         class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="link">
                                    Resource Link
                                </label>
                                <input type="url" id="link" name="link" 
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600" required>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="space-y-5">
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="title">
                                    Resource Title
                                </label>
                                <input type="text" id="title" name="title" 
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600" required>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="professor">
                                    Professor
                                </label>
                                <input type="text" id="professor" name="professor" 
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                            </div>
                            
                            <!-- Preview Section -->
                            <div class="bg-gray-50 rounded-xl p-4 mt-4 border border-gray-200">
                                <h4 class="font-medium text-gray-700 mb-2 flex items-center">
                                    <i class="fas fa-eye mr-2 text-indigo-500"></i> Resource Preview
                                </h4>
                                <div class="bg-white rounded-lg p-4 shadow-inner min-h-[180px]">
                                    <div id="preview-title" class="font-bold text-gray-800 text-lg mb-1">Resource Title</div>
                                    <div id="preview-professor" class="text-sm text-gray-500 mb-2">
                                        <i class="fas fa-user-tie mr-1 text-indigo-500"></i> 
                                        <span>Professor Name</span>
                                    </div>
                                    <p id="preview-description" class="text-gray-600 text-sm mb-3">Resource description will appear here as you type...</p>
                                    <div class="text-xs text-indigo-500">
                                        <i class="fas fa-link mr-1"></i>
                                        <span id="preview-link">https://example.com</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 mt-8 border-t border-gray-100 pt-5">
                        <button type="button" onclick="toggleModal('addResourceModal')" 
                                class="px-5 py-2.5 border border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" name="resource_submit"
                                class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-medium hover:shadow-lg transition-all">
                            <i class="fas fa-save mr-2"></i>Save Resource
                        </button>
                    </div>
                </form>
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
        
        function toggleModal(id) {
            const modal = document.getElementById(id);
            const modalContent = modal.querySelector('div');
            
            if (modal.classList.contains('hidden')) {
                // Open modal with animation
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                
                // Animate the modal opening
                setTimeout(() => {
                    modalContent.classList.remove('scale-95', 'opacity-0');
                    modalContent.classList.add('scale-100', 'opacity-100');
                }, 10);
            } else {
                // Close modal with animation
                modalContent.classList.remove('scale-100', 'opacity-100');
                modalContent.classList.add('scale-95', 'opacity-0');
                
                // Wait for animation to complete before hiding
                setTimeout(() => {
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }, 300);
            }
        }

        // Show success toast
        function showToast(status, message) {
            Swal.fire({
                toast: true,
                icon: status,
                title: message,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    popup: 'colored-toast'
                },
                background: status === 'success' ? '#10B981' : '#EF4444'
            });
        }
        
        // Confirm delete resource
        function confirmDeleteResource(resourceId) {
            Swal.fire({
                title: 'Delete Resource',
                text: 'Are you sure you want to delete this resource?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX request to delete the resource
                    fetch('delete_resource.php?id=' + resourceId, {
                        method: 'GET'
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data === 'success') {
                            showToast('success', 'Resource deleted successfully');
                            // Reload page after short delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            showToast('error', 'Failed to delete resource');
                        }
                    })
                    .catch(error => {
                        showToast('error', 'An error occurred');
                        console.error('Error:', error);
                    });
                }
            });
        }
        
        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Apply special effects for cards
            const cards = document.querySelectorAll('.resource-card-3d');
            
            cards.forEach(card => {
                card.addEventListener('mousemove', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    
                    const angleY = (x - centerX) / 20;
                    const angleX = (centerY - y) / 20;
                    
                    this.style.transform = `rotateY(${angleY}deg) rotateX(${angleX}deg)`;
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'rotateY(0deg) rotateX(0deg)';
                });
            });
            
            // Live preview functionality
            const titleInput = document.getElementById('title');
            const professorInput = document.getElementById('professor');
            const descriptionInput = document.getElementById('description');
            const linkInput = document.getElementById('link');
            
            const previewTitle = document.getElementById('preview-title');
            const previewProfessor = document.getElementById('preview-professor');
            const previewDescription = document.getElementById('preview-description');
            const previewLink = document.getElementById('preview-link');
            
            titleInput.addEventListener('input', function() {
                previewTitle.textContent = this.value || 'Resource Title';
            });
            
            professorInput.addEventListener('input', function() {
                const professorSpan = previewProfessor.querySelector('span');
                professorSpan.textContent = this.value || 'Professor Name';
            });
            
            descriptionInput.addEventListener('input', function() {
                previewDescription.textContent = this.value || 'Resource description will appear here as you type...';
            });
            
            linkInput.addEventListener('input', function() {
                previewLink.textContent = this.value || 'https://example.com';
            });
        });
    </script>
</body>
</html>