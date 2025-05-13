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

// Dropdown values
$labs = ['Lab 517', 'Lab 524', 'Lab 526', 'Lab 528', 'Lab 530', 'Lab 542'];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$selectedLab = isset($_GET['lab']) ? (strpos($_GET['lab'], 'Lab') === 0 ? $_GET['lab'] : 'Lab ' . $_GET['lab']) : 'Lab 517';
$selectedDay = isset($_GET['day']) ? $_GET['day'] : 'Monday';

// Fetch schedules from database for the selected lab and day
$query = "SELECT * FROM lab_schedule WHERE LABORATORY = ? AND DAY = ? ORDER BY TIME_START ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $selectedLab, $selectedDay);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../logo/ccs.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
        /* Dropdown styling to match the image */
        .modern-dropdown {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: #fff;
            border: none;
            box-shadow: 0 2px 6px 0 rgba(37,99,235,0.08);
            padding: 0.5rem 2.5rem 0.5rem 1.25rem;
            border-radius: 9999px;
            font-size: 1.1rem;
            font-weight: 500;
            color: #222;
            outline: none;
            min-width: 140px;
            transition: box-shadow 0.2s;
        }
        .modern-dropdown:focus {
            box-shadow: 0 0 0 2px #2563eb33;
        }
        .dropdown-arrow {
            pointer-events: none;
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #222;
            font-size: 1rem;
        }
        /* Card and table styling for consistency */
        .modern-card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(37,99,235,0.08);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #4f6ef7 0%, #4f6ef7 100%);
            border-bottom: none;
            position: relative;
            overflow: hidden;
        }
        .card-header::before {
            content: "";
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -75px;
            right: -75px;
        }
        .card-header::after {
            content: "";
            position: absolute;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            bottom: -50px;
            left: -50px;
        }
        .schedule-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        .schedule-table th {
            background: #2563eb;
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 14px 16px;
            border: none;
            white-space: nowrap;
        }
        .schedule-table th:first-child {
            border-top-left-radius: 10px;
        }
        .schedule-table th:last-child {
            border-top-right-radius: 10px;
        }
        .schedule-table tr {
            transition: all 0.2s ease;
        }
        .schedule-table tbody tr:nth-child(odd) {
            background-color: rgba(243, 244, 246, 0.5);
        }
        .schedule-table tbody tr:hover {
            background-color: rgba(219, 234, 254, 0.7);
        }
        .schedule-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        .subject-name {
            font-weight: 500;
            color: #1e40af;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 250px;
        }
        .subject-desc {
            font-size: 0.875rem;
            color: #6b7280;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 250px;
        }
        /* Hide default arrow for select */
        select.modern-dropdown::-ms-expand { display: none; }
        select.modern-dropdown::-webkit-inner-spin-button,
        select.modern-dropdown::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    </style>
</head>
<body class="bg-gradient-to-br min-h-screen font-poppins" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)">
    <!-- Header (from profile.php) -->
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
    <!-- Main Content Container -->
    <div class="container mx-auto px-4 py-8">
        <div class="modern-card">
            <div class="card-header text-white p-5 flex items-center justify-center relative overflow-hidden">
                <h2 class="text-xl font-bold tracking-wider uppercase relative z-10 font-sans">Laboratory Management</h2>
            </div>
            <div class="p-6">
                <!-- Centered Dropdowns -->
                <form action="" method="GET" class="flex flex-col md:flex-row items-center justify-center gap-4 mb-8">
                    <div class="relative">
                        <select name="lab" class="modern-dropdown pr-10" onchange="this.form.submit()">
                            <?php foreach ($labs as $lab): ?>
                                <option value="<?php echo $lab; ?>" <?php if ($selectedLab == $lab) echo 'selected'; ?>>
                                    <?php echo $lab; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="dropdown-arrow absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </div>
                    <div class="relative">
                        <select name="day" class="modern-dropdown pr-10" onchange="this.form.submit()">
                            <?php foreach ($days as $day): ?>
                                <option value="<?php echo $day; ?>" <?php if ($selectedDay == $day) echo 'selected'; ?>>
                                    <?php echo $day; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="dropdown-arrow absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </div>
                </form>
                <!-- Schedule Table -->
                <div class="mt-6 overflow-auto">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th width="25%">Time Slot</th>
                                <th width="45%">Course Details</th>
                                <th width="30%">Professor</th>
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
                                                <div class="subject-desc">Course Code</div>
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
                                                    $bgClass = "bg-" . $color . "-100";
                                                    $textClass = "text-" . $color . "-700";
                                                ?>
                                                <div class="w-8 h-8 rounded-full <?php echo $bgClass; ?> flex items-center justify-center mr-3">
                                                    <span class="<?php echo $textClass; ?> font-medium"><?php echo $initials; ?></span>
                                                </div>
                                                <?php echo htmlspecialchars($schedule['PROFESSOR']); ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-6 text-gray-500 italic">No schedules found for this day and laboratory</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-sm text-gray-500">
                    <p>Note: Schedule may change without prior notice. Please check regularly for updates.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="py-4 px-6 bg-white/95 backdrop-blur-sm mt-8 relative">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500"></div>
        <p class="text-center text-sm text-gray-600">
            &copy; 2025 CCS Sit-in Monitoring System | <span class="gradient-text font-medium">UC - College of Computer Studies</span>
        </p>
    </div>
</body>
</html>