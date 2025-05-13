<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
    
}

$firstName = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Guest';
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($userId) {
    $stmt = $conn->prepare("SELECT IDNO, LAST_NAME, FIRST_NAME, MID_NAME, COURSE, YEAR_LEVEL, EMAIL, ADDRESS, UPLOAD_IMAGE FROM users WHERE STUD_NUM = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($idNo, $lastName, $dbFirstName, $midName, $course, $yearLevel, $email, $address, $userImage);
    $stmt->fetch();
    $stmt->close();
    
    $profileImage = !empty($userImage) ? '../images/' . $userImage : "../images/image.jpg";
} else {
    $profileImage = "../images/image.jpg";
    $idNo = '';
    $lastName = '';
    $dbFirstName = '';
    $midName = '';
    $course = '';
    $yearLevel = '';
    $email = '';
    $address = '';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle profile update
    if (isset($_POST['action']) && $_POST['action'] == 'update_profile') {
        $idno = $_POST['Idno'];
        $lastname = $_POST['Lastname'];
        $firstname = $_POST['Firstname'];
        $midname = $_POST['Midname'];
        $course = $_POST['Course'];
        $year_level = $_POST['Year_Level'];
        $email = $_POST['Email'];
        $address = $_POST['Address'];
        
        $uploadImagePath = $userImage; // Keep existing image by default
        
        // Handle image upload
        if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] == 0) {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileType = finfo_file($fileInfo, $_FILES['profileImage']['tmp_name']);
            finfo_close($fileInfo);
            
            if (in_array($fileType, $allowedTypes)) {
                // Create images directory if it doesn't exist
                $targetDir = "../images/";
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                
                // Generate unique filename
                $fileName = uniqid() . '_' . basename($_FILES["profileImage"]["name"]);
                $targetFile = $targetDir . $fileName;
                
                if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $targetFile)) {
                    $uploadImagePath = $fileName;
                    $_SESSION['profile_image'] = $fileName;
                    
                    // Delete old image if it exists and is not the default image
                    if (!empty($userImage) && $userImage != "image.jpg" && file_exists($targetDir . $userImage)) {
                        unlink($targetDir . $userImage);
                    }
                }
            }
        }

        try {
            $stmt = $conn->prepare("UPDATE users SET IDNO = ?, LAST_NAME = ?, FIRST_NAME = ?, MID_NAME = ?, COURSE = ?, YEAR_LEVEL = ?, EMAIL = ?, ADDRESS = ?, UPLOAD_IMAGE = ? WHERE STUD_NUM = ?");
            $stmt->bind_param("sssssssssi", $idno, $lastname, $firstname, $midname, $course, $year_level, $email, $address, $uploadImagePath, $userId);
            
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Profile updated successfully.",
                    "image" => $uploadImagePath
                ]);
            } else {
                throw new Exception("Failed to update profile");
            }
            
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
        
        exit;
    } 
    // Handle password change
    elseif (isset($_POST['action']) && $_POST['action'] == 'change_password') {
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];
        $confirmPassword = $_POST['confirmPassword'];
        
        // Password policy validation
        $errors = [];
        if (strlen($newPassword) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }
        if (!preg_match('/[A-Z]/', $newPassword)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        if (!preg_match('/[a-z]/', $newPassword)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        if (!preg_match('/[0-9]/', $newPassword)) {
            $errors[] = "Password must contain at least one number";
        }
        if (!preg_match('/[^A-Za-z0-9]/', $newPassword)) {
            $errors[] = "Password must contain at least one special character";
        }

        if (!empty($errors)) {
            echo json_encode([
                "status" => "error",
                "message" => "Password requirements not met:",
                "errors" => $errors
            ]);
            exit;
        }
        
        // Verify passwords match
        if ($newPassword !== $confirmPassword) {
            echo json_encode([
                "status" => "error",
                "message" => "New passwords do not match."
            ]);
            exit;
        }
        
        try {
            // First verify current password
            $stmt = $conn->prepare("SELECT PASSWORD_HASH FROM users WHERE STUD_NUM = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->bind_result($hashedPassword);
            $stmt->fetch();
            $stmt->close();
            
            // Verify current password
            if (!password_verify($currentPassword, $hashedPassword)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Current password is incorrect."
                ]);
                exit;
            }
            
            // Hash the new password
            $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update the password
            $stmt = $conn->prepare("UPDATE users SET PASSWORD_HASH = ? WHERE STUD_NUM = ?");
            $stmt->bind_param("si", $newHashedPassword, $userId);
            
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Password updated successfully."
                ]);
            } else {
                throw new Exception("Failed to update password");
            }
            
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
        
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="icon" href="../logo/ccs.png" type="image/x-icon">
    <title>Edit</title>
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
         body {
            font-family: 'Poppins', sans-serif;
            background: white;
            min-height: 100vh;
        }
        /* Add gradient text class for the footer */
        .gradient-text {
            background: linear-gradient(to right, #ec4899, #a855f7, #6366f1);
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
        .tab-button.active {
            color: #2563eb;
            border-bottom: 2px solid #2563eb;
        }
        .btn-gradient {
            background: linear-gradient(to bottom right, #2563eb, #3b82f6);
        }
        .accent-gradient {
            background: linear-gradient(to right, #2563eb, #3b82f6);
        }
        /* Header nav items */
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
        /* Mobile menu styles */
        @media (max-width: 768px) {
            .nav-item span {
                display: none;
            }
            .nav-item i {
                margin-right: 0;
            }
        }
        /* Responsive adjustments */
        @media (max-width: 1024px) {
            header .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
    </style>
</head>
<body class="min-h-screen font-poppins" style="background: white;">
    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-lg py-4 px-6">
        <div class="container mx-auto flex items-center justify-between">
            <!-- Logo/Title Section -->
            <div class="flex items-center">
                <h1 class="text-2xl font-bold">CCS SIT-IN MONITORING SYSTEM</h1>
            </div>

            <!-- Navigation Items -->
            <div class="flex items-center space-x-6">
                <!-- Nav Links -->
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

                    <!-- View Dropdown -->
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

                    <!-- User Profile -->
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

                <!-- Mobile Menu Button -->
                <button class="md:hidden" @click="mobileMenu = !mobileMenu">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </header>

        <!-- Form Container - Replace the existing container div with this -->
        <div class="mx-auto my-8 max-w-4xl">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="text-white p-4 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                    <h2 class="text-xl font-bold tracking-wider uppercase relative z-10">EDIT STUDENT INFORMATION</h2>
                </div>

                <!-- Tabs Navigation -->
                <div class="flex justify-center border-b border-gray-200">
                    <button type="button" 
                            class="tab-button px-6 py-3 font-medium text-sm active text-blue-600 border-b-2 border-blue-600" 
                            data-tab="edit-profile">
                        Edit Profile
                    </button>
                    <button type="button" 
                            class="tab-button px-6 py-3 font-medium text-sm text-gray-500" 
                            data-tab="change-password">
                        Change Password
                    </button>
                </div>

                <!-- Edit Profile Tab Content -->
                <div id="edit-profile" class="tab-content p-6 block">
                    <div class="relative group mx-auto w-[150px] h-[150px] mb-8">
                        <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full blur opacity-25 group-hover:opacity-75 transition duration-300"></div>
                        <img src="<?php echo htmlspecialchars($profileImage); ?>" 
                            alt="Student Image" 
                            class="w-[150px] h-[150px] rounded-full object-cover mx-auto block cursor-pointer border-2 border-white shadow-lg relative group-hover:scale-105 transition-all duration-300"
                            id="profileImage"
                            title="Click to change profile picture">
                        <div class="absolute bottom-1 right-1 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full p-2 shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <i class="fas fa-camera text-white text-sm"></i>
                        </div>
                    </div>
                    <input type="file" id="fileInput" name="profileImage" accept="image/*" class="hidden" form="editForm">
                    
                    <form id="editForm" method="POST" action="" class="mt-6">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- ID Number -->
                            <div class="relative col-span-1 md:col-span-2 group">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                                <div class="relative bg-white rounded-lg overflow-hidden">
                                    <input type="text" 
                                           id="Idno" 
                                           name="Idno" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-md cursor-not-allowed bg-gray-50" 
                                           value="<?php echo htmlspecialchars($idNo); ?>" 
                                           readonly>
                                    <span class="absolute top-1/2 -translate-y-1/2 right-3 text-xs font-semibold text-gray-500">ID Number</span>
                                </div>
                            </div>

                            <!-- Name Fields -->
                            <div class="mb-0 col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="group relative">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                                    <div class="relative bg-white rounded-lg overflow-hidden">
                                        <input type="text" id="Lastname" name="Lastname" class="w-full px-4 py-3 border border-gray-300 rounded-md" placeholder="Last Name" value="<?php echo htmlspecialchars($lastName); ?>" required>
                                    </div>
                                </div>
                                <div class="group relative">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                                    <div class="relative bg-white rounded-lg overflow-hidden">
                                        <input type="text" id="Firstname" name="Firstname" class="w-full px-4 py-3 border border-gray-300 rounded-md" placeholder="First Name" value="<?php echo htmlspecialchars($dbFirstName); ?>" required>
                                    </div>
                                </div>
                                <div class="group relative">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                                    <div class="relative bg-white rounded-lg overflow-hidden">
                                        <input type="text" id="Midname" name="Midname" class="w-full px-4 py-3 border border-gray-300 rounded-md" placeholder="Middle Name" value="<?php echo htmlspecialchars($midName); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Course -->
                            <div class="group relative">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                                <div class="relative bg-white rounded-lg overflow-hidden">
                                    <select id="Course" name="Course" class="w-full px-4 py-3 border border-gray-300 rounded-md" required>
                                        <option value="" disabled>Select a Course</option>
                                        <option value="BS IN ACCOUNTANCY" <?php if ($course == 'BS IN ACCOUNTANCY') echo 'selected'; ?>>BS IN ACCOUNTANCY</option>
                                        <option value="BS IN BUSINESS ADMINISTRATION" <?php if ($course == 'BS IN BUSINESS ADMINISTRATION') echo 'selected'; ?>>BS IN BUSINESS ADMINISTRATION</option>
                                        <option value="BS IN CRIMINOLOGY" <?php if ($course == 'BS IN CRIMINOLOGY') echo 'selected'; ?>>BS IN CRIMINOLOGY</option>
                                        <option value="BS IN CUSTOMS ADMINISTRATION" <?php if ($course == 'BS IN CUSTOMS ADMINISTRATION') echo 'selected'; ?>>BS IN CUSTOMS ADMINISTRATION</option>
                                        <option value="BS IN INFORMATION TECHNOLOGY" <?php if ($course == 'BS IN INFORMATION TECHNOLOGY') echo 'selected'; ?>>BS IN INFORMATION TECHNOLOGY</option>
                                        <option value="BS IN COMPUTER SCIENCE" <?php if ($course == 'BS IN COMPUTER SCIENCE') echo 'selected'; ?>>BS IN COMPUTER SCIENCE</option>
                                        <option value="BS IN OFFICE ADMINISTRATION" <?php if ($course == 'BS IN OFFICE ADMINISTRATION') echo 'selected'; ?>>BS IN OFFICE ADMINISTRATION</option>
                                        <option value="BS IN SOCIAL WORK" <?php if ($course == 'BS IN SOCIAL WORK') echo 'selected'; ?>>BS IN SOCIAL WORK</option>
                                        <option value="BACHELOR OF SECONDARY EDUCATION" <?php if ($course == 'BACHELOR OF SECONDARY EDUCATION') echo 'selected'; ?>>BACHELOR OF SECONDARY EDUCATION</option>
                                        <option value="BACHELOR OF ELEMENTARY EDUCATION" <?php if ($course == 'BACHELOR OF ELEMENTARY EDUCATION') echo 'selected'; ?>>BACHELOR OF ELEMENTARY EDUCATION</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Year Level -->
                            <div class="group relative">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                                <div class="relative bg-white rounded-lg overflow-hidden">
                                    <select id="Year_Level" name="Year_Level" class="w-full px-4 py-3 border border-gray-300 rounded-md" required>
                                        <option value="" disabled>Select a Year Level</option>
                                        <option value="1st Year" <?php if ($yearLevel == '1st Year') echo 'selected'; ?>>1st Year</option>
                                        <option value="2nd Year" <?php if ($yearLevel == '2nd Year') echo 'selected'; ?>>2nd Year</option>
                                        <option value="3rd Year" <?php if ($yearLevel == '3rd Year') echo 'selected'; ?>>3rd Year</option>
                                        <option value="4th Year" <?php if ($yearLevel == '4th Year') echo 'selected'; ?>>4th Year</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Email and Address -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Email -->
                                <div class="group relative">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                                    <div class="relative bg-white rounded-lg overflow-hidden">
                                        <input type="email" 
                                            id="Email" 
                                            name="Email" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-md" 
                                            placeholder="Email Address" 
                                            value="<?php echo htmlspecialchars($email); ?>" 
                                            required>
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="group relative">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                                    <div class="relative bg-white rounded-lg overflow-hidden">
                                        <input type="text" 
                                            id="Address" 
                                            name="Address" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-md" 
                                            placeholder="Complete Address" 
                                            value="<?php echo htmlspecialchars($address); ?>" 
                                            required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-10 mb-4">
                                <button type="submit" class="relative inline-flex items-center justify-center overflow-hidden rounded-lg group bg-gradient-to-br from-purple-600 to-blue-500 p-0.5 text-sm font-medium hover:text-white">
                                <span class="relative rounded-md bg-white px-8 py-3 transition-all duration-300 ease-in-out group-hover:bg-opacity-0 text-purple-700 font-bold group-hover:text-white">
                                    Save Profile Changes
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Tab Content -->
                <div id="change-password" class="tab-content p-6 hidden">
                    <div class="max-w-md mx-auto">
                        <div class="text-center mb-6">
                            <h3 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-600">Secure Your Account</h3>
                            <p class="text-gray-500 text-sm mt-2">Update your password regularly for better security</p>
                        </div>
                        
                        <form id="passwordForm" method="POST" action="" class="mt-8">
                            <input type="hidden" name="action" value="change_password">
                            
                            <!-- Password Fields with Updated Design -->
                            <div class="space-y-5">
                                <!-- Current Password -->
                                <div class="relative group">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-pink-600 to-purple-600 rounded-lg blur opacity-20 group-hover:opacity-40 transition duration-300"></div>
                                    <div class="relative bg-white rounded-lg">
                                        <input type="password" id="currentPassword" name="currentPassword" 
                                            class="w-full px-4 py-3 border-0 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-400 focus:outline-none" 
                                            placeholder="Current Password" required>
                                        <button type="button" class="toggle-password absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600" data-target="currentPassword">
                                            Show
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- New Password -->
                                <div class="relative group">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-pink-600 to-purple-600 rounded-lg blur opacity-20 group-hover:opacity-40 transition duration-300"></div>
                                    <div class="relative bg-white rounded-lg">
                                        <input type="password" id="newPassword" name="newPassword" 
                                            class="w-full px-4 py-3 border-0 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-400 focus:outline-none" 
                                            placeholder="New Password" required>
                                        <button type="button" class="toggle-password absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600" data-target="newPassword">
                                            Show
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Confirm New Password -->
                                <div class="relative group">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-pink-600 to-purple-600 rounded-lg blur opacity-20 group-hover:opacity-40 transition duration-300"></div>
                                    <div class="relative bg-white rounded-lg">
                                        <input type="password" id="confirmPassword" name="confirmPassword" 
                                            class="w-full px-4 py-3 border-0 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-400 focus:outline-none" 
                                            placeholder="Confirm New Password" required>
                                        <button type="button" class="toggle-password absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600" data-target="confirmPassword">
                                            Show
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Password strength indicator -->
                            <div class="mt-4">
                                <p class="text-xs text-gray-500 mb-1">Password strength:</p>
                                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div id="passwordStrength" class="h-full w-0 bg-red-500 transition-all duration-300"></div>
                                </div>
                                <p id="strengthText" class="text-xs text-gray-500 mt-1">Enter a new password</p>
                            </div>
                            
                            <!-- Password requirements -->
                            <div class="mt-4 bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-gray-500 mb-2">Password must include:</p>
                                <ul class="space-y-1 text-xs">
                                    <li id="req-length" class="text-gray-500">• At least 8 characters</li>
                                    <li id="req-uppercase" class="text-gray-500">• At least one uppercase letter</li>
                                    <li id="req-lowercase" class="text-gray-500">• At least one lowercase letter</li>
                                    <li id="req-number" class="text-gray-500">• At least one number</li>
                                    <li id="req-special" class="text-gray-500">• At least one special character</li>
                                </ul>
                            </div>
                            
                            <div class="text-center mt-8">
                                <button type="submit" class="relative inline-flex items-center justify-center overflow-hidden rounded-lg group bg-gradient-to-br from-purple-600 to-blue-500 p-0.5 text-sm font-medium hover:text-white">
                                    <span class="relative rounded-md bg-white px-8 py-3 transition-all duration-300 ease-in-out group-hover:bg-opacity-0 text-purple-700 font-bold group-hover:text-white">
                                        Update Password
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <!-- Main content continues -->
    <script>
        function toggleNav(x) {
            document.getElementById("mySidenav").classList.toggle("-translate-x-0");
            document.getElementById("mySidenav").classList.toggle("-translate-x-full");
        }

        function closeNav() {
            document.getElementById("mySidenav").classList.remove("-translate-x-0");
            document.getElementById("mySidenav").classList.add("-translate-x-full");
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching functionality
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active', 'text-blue-600', 'border-b-2', 'border-blue-600');
                        btn.classList.add('text-gray-500');
                    });
                    
                    // Add active class to clicked button
                    this.classList.add('active', 'text-blue-600', 'border-b-2', 'border-blue-600');
                    this.classList.remove('text-gray-500');
                    
                    // Hide all tab contents
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                        content.classList.remove('block');
                    });
                    
                    // Show the target tab content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.remove('hidden');
                    document.getElementById(tabId).classList.add('block');
                });
            });

            const idnoInput = document.getElementById('Idno');
            idnoInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 8);
            });

            const nameInputs = ['Lastname', 'Firstname', 'Midname'];
            nameInputs.forEach(function(id) {
                const input = document.getElementById(id);
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
                });
            });

            const logoutLink = document.querySelector("a[href='../logout.php']");
            if (logoutLink) {
                logoutLink.addEventListener("click", function(e) {
                    e.preventDefault();
                    fetch("../login.php", {
                        method: "POST"
                    })
                    .then(response => {
                        if (response.ok) {
                            window.location.href = "../login.php";
                        } else {
                            console.error("Logout failed");
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                    });
                });
            }
        });

        // Add image upload handler
        document.getElementById('profileImage').addEventListener('click', function() {
            document.getElementById('fileInput').click();
        });

        document.getElementById('fileInput').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profileImage').src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Update profile form submission
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
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
                        title: data.message,
                        background: '#10B981'
                    }).then(() => {
                        // Update the profile image in the sidebar if it was changed
                        if (data.image) {
                            const sidebarImage = document.querySelector('#mySidenav img');
                            if (sidebarImage) {
                                sidebarImage.src = '../images/' + data.image;
                            }
                        }
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
                        title: data.message,
                        background: '#EF4444'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
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
                    title: 'An unexpected error occurred',
                    background: '#EF4444'
                });
            });
        });

        // Password form submission
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Client-side password policy validation
            const errors = [];
            if (newPassword.length < 8) {
                errors.push("Password must be at least 8 characters long");
            }
            if (!/[A-Z]/.test(newPassword)) {
                errors.push("Password must contain at least one uppercase letter");
            }
            if (!/[a-z]/.test(newPassword)) {
                errors.push("Password must contain at least one lowercase letter");
            }
            if (!/[0-9]/.test(newPassword)) {
                errors.push("Password must contain at least one number");
            }
            if (!/[^A-Za-z0-9]/.test(newPassword)) {
                errors.push("Password must contain at least one special character");
            }
            
            if (errors.length > 0) {
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
                    title: 'Password requirements not met',
                    background: '#EF4444'
                });
                return;
            }
            
            if (newPassword !== confirmPassword) {
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
                    title: 'Passwords do not match',
                    background: '#EF4444'
                });
                return;
            }
            
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
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
                        title: data.message,
                        background: '#10B981'
                    }).then(() => {
                        this.reset();
                        document.getElementById('passwordStrength').style.width = '0%';
                        document.getElementById('strengthText').textContent = 'Enter a new password';
                        document.getElementById('strengthText').className = 'text-xs text-gray-500 mt-1';
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
                        title: data.message,
                        background: '#EF4444'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
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
                    title: 'An unexpected error occurred',
                    background: '#EF4444'
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
    // Password toggle visibility
        const toggleButtons = document.querySelectorAll('.toggle-password');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
        
        // Password strength meter
        const newPasswordInput = document.getElementById('newPassword');
        const strengthBar = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('strengthText');
        
        const reqLength = document.getElementById('req-length');
        const reqUppercase = document.getElementById('req-uppercase');
        const reqLowercase = document.getElementById('req-lowercase');
        const reqNumber = document.getElementById('req-number');
        const reqSpecial = document.getElementById('req-special');
        
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Check length
            if (password.length >= 8) {
                strength += 20;
                reqLength.classList.remove('text-gray-500');
                reqLength.classList.add('text-green-500');
                reqLength.querySelector('i').classList.remove('fa-circle');
                reqLength.querySelector('i').classList.add('fa-check-circle');
            } else {
                reqLength.classList.remove('text-green-500');
                reqLength.classList.add('text-gray-500');
                reqLength.querySelector('i').classList.remove('fa-check-circle');
                reqLength.querySelector('i').classList.add('fa-circle');
            }
            
            // Check uppercase
            if (/[A-Z]/.test(password)) {
                strength += 20;
                reqUppercase.classList.remove('text-gray-500');
                reqUppercase.classList.add('text-green-500');
                reqUppercase.querySelector('i').classList.remove('fa-circle');
                reqUppercase.querySelector('i').classList.add('fa-check-circle');
            } else {
                reqUppercase.classList.remove('text-green-500');
                reqUppercase.classList.add('text-gray-500');
                reqUppercase.querySelector('i').classList.remove('fa-check-circle');
                reqUppercase.querySelector('i').classList.add('fa-circle');
            }
            
            // Check lowercase
            if (/[a-z]/.test(password)) {
                strength += 20;
                reqLowercase.classList.remove('text-gray-500');
                reqLowercase.classList.add('text-green-500');
                reqLowercase.querySelector('i').classList.remove('fa-circle');
                reqLowercase.querySelector('i').classList.add('fa-check-circle');
            } else {
                reqLowercase.classList.remove('text-green-500');
                reqLowercase.classList.add('text-gray-500');
                reqLowercase.querySelector('i').classList.remove('fa-check-circle');
                reqLowercase.querySelector('i').classList.add('fa-circle');
            }
            
            // Check numbers
            if (/[0-9]/.test(password)) {
                strength += 20;
                reqNumber.classList.remove('text-gray-500');
                reqNumber.classList.add('text-green-500');
                reqNumber.querySelector('i').classList.remove('fa-circle');
                reqNumber.querySelector('i').classList.add('fa-check-circle');
            } else {
                reqNumber.classList.remove('text-green-500');
                reqNumber.classList.add('text-gray-500');
                reqNumber.querySelector('i').classList.remove('fa-check-circle');
                reqNumber.querySelector('i').classList.add('fa-circle');
            }
            
            // Check special characters
            if (/[^A-Za-z0-9]/.test(password)) {
                strength += 20;
                reqSpecial.classList.remove('text-gray-500');
                reqSpecial.classList.add('text-green-500');
                reqSpecial.querySelector('i').classList.remove('fa-circle');
                reqSpecial.querySelector('i').classList.add('fa-check-circle');
            } else {
                reqSpecial.classList.remove('text-green-500');
                reqSpecial.classList.add('text-gray-500');
                reqSpecial.querySelector('i').classList.remove('fa-check-circle');
                reqSpecial.querySelector('i').classList.add('fa-circle');
            }
            
            // Update the strength bar
            strengthBar.style.width = strength + '%';
            
            // Update color and text based on strength
            if (strength < 40) {
                strengthBar.classList.remove('bg-yellow-500', 'bg-green-500');
                strengthBar.classList.add('bg-red-500');
                strengthText.textContent = 'Weak password';
                strengthText.className = 'text-xs text-red-500 mt-1';
            } else if (strength < 80) {
                strengthBar.classList.remove('bg-red-500', 'bg-green-500');
                strengthBar.classList.add('bg-yellow-500');
                strengthText.textContent = 'Medium password';
                strengthText.className = 'text-xs text-yellow-600 mt-1';
            } else {
                strengthBar.classList.remove('bg-red-500', 'bg-yellow-500');
                strengthBar.classList.add('bg-green-500');
                strengthText.textContent = 'Strong password';
                strengthText.className = 'text-xs text-green-500 mt-1';
            }
            
            // If password is empty
            if (password === '') {
                strengthBar.style.width = '0%';
                strengthText.textContent = 'Enter a new password';
                strengthText.className = 'text-xs text-gray-500 mt-1';
            }
        });
        
        // Check if passwords match
        const confirmPasswordInput = document.getElementById('confirmPassword');
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== newPasswordInput.value) {
                this.classList.add('border-red-300');
                this.classList.remove('border-green-300');
            } else {
                this.classList.remove('border-red-300');
                this.classList.add('border-green-300');
            }
        });
    });
    </script>
    
</body>
</html>