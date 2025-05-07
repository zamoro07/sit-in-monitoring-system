<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="./logo/ccs.png" type="image/x-icon">
<title>CCS Sit-in Monitoring - Home</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(to bottom right, #1e1b4b, #4c1d95, #701a75);
    min-height: 100vh;
}

.floating-circles {
    position: fixed;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    overflow: hidden;
    z-index: -1;
    opacity: 0.3;
}

.circle {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(45deg, #6366f1, #a855f7);
    opacity: 0.4;
    animation: float-circles 15s infinite;
}

@keyframes float-circles {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(180deg);
    }
}

.glass-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
}

.nav-link {
    position: relative;
    color: white;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.nav-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(to right, #6366f1, #a855f7);
    transition: width 0.3s ease;
}

.nav-link:hover::after {
    width: 100%;
}

.gradient-border {
    position: relative;
    border-radius: 0.5rem;
}

.gradient-border::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, #6366f1, #a855f7);
    border-radius: 0.6rem;
    z-index: -1;
}
</style>
</head>
<body>
<!-- Floating circles background -->
<div class="floating-circles">
    <div class="circle" style="width: 80px; height: 80px; top: 10%; left: 10%;"></div>
    <div class="circle" style="width: 120px; height: 120px; top: 25%; left: 70%;"></div>
    <div class="circle" style="width: 50px; height: 50px; top: 70%; left: 30%;"></div>
    <div class="circle" style="width: 100px; height: 100px; top: 60%; left: 80%;"></div>
</div>

<!-- Navigation -->
<nav class="glass-card fixed w-full z-50 top-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <img src="./logo/ccs.png" class="h-10 w-10" alt="CCS Logo">
                    <span class="text-white text-xl font-bold ml-2">CCS Monitor</span>
                </div>
            </div>
            <div class="hidden md:flex items-center space-x-8">
                <a href="#home" class="nav-link">Home</a>
                <a href="#about" class="nav-link">About</a>
                <a href="#features" class="nav-link">Features</a>
                <a href="#teachers" class="nav-link">Faculties</a>
                <a href="login.php" class="gradient-border px-6 py-2 text-white hover:opacity-90 transition-all">
                    Login
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section id="home" class="pt-32 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="glass-card rounded-2xl p-8 md:p-12">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-white leading-tight">
                CCS Laboratory Sit-In<br>Monitoring System
            </h1>
            <p class="mt-6 text-xl text-gray-200">
                Efficiently manage and monitor student sit-in sessions in the College of Computer Studies laboratories.
            </p>
            <div class="mt-10 flex flex-wrap gap-4">
                <a href="Students.php" class="gradient-border px-6 py-3 text-white font-medium hover:opacity-90 transition-all">
                    Monitor Students
                </a>
                <a href="#features" class="px-6 py-3 rounded-lg bg-white/20 text-white border border-white/30 font-medium backdrop-blur-sm hover:bg-white/30 transition-all">
                    View Features
                </a>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="glass-card rounded-2xl p-8 md:p-12">
            <h2 class="text-3xl font-bold text-white text-center mb-8">About CCS Monitor</h2>
            <div class="grid md:grid-cols-2 gap-8">
                <div class="text-white">
                    <h3 class="text-xl font-semibold mb-4">Our Mission</h3>
                    <p class="text-gray-200">
                        To provide an efficient and reliable system for monitoring laboratory activities
                        and student attendance in the College of Computer Studies.
                    </p>
                </div>
                <div class="text-white">
                    <h3 class="text-xl font-semibold mb-4">What We Offer</h3>
                    <ul class="space-y-2">
                        <li><i class="fas fa-check-circle mr-2 text-purple-400"></i> Real-time monitoring</li>
                        <li><i class="fas fa-check-circle mr-2 text-purple-400"></i> Automated attendance tracking</li>
                        <li><i class="fas fa-check-circle mr-2 text-purple-400"></i> Resource management</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Teachers Section -->
<section id="teachers" class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="glass-card rounded-2xl p-8 md:p-12">
            <h2 class="text-3xl font-bold text-white text-center mb-12">Dean</h2>
            
            <!-- Dean Card (Centered) -->
            <div class="flex justify-center mb-12">
                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Neil Basabe" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Neil Basabe</h3>
                    <p class="text-purple-300 text-sm">Dean</p>
                </div>
            </div>

            <!-- Full-time Teachers Heading -->
            <h3 class="text-2xl font-bold text-white text-center mb-8 border-t border-purple-500/30 pt-8">
                FULL-TIME TEACHERS
            </h3>

            <!-- Teachers Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
                <!-- First row of teachers -->
                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Heubert Ferolino</h3>
                    <p class="text-purple-300 text-sm">hferolino@uc.edu.ph</p>
                </div>

                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Leo Bermudez</h3>
                    <p class="text-purple-300 text-sm">lbermudez@uc.edu.ph</p>
                </div>

                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Jennifer Amores</h3>
                    <p class="text-purple-300 text-sm">jamores@uc.edu.ph</p>
                </div>

                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Franz Josef Caminade</h3>
                    <p class="text-purple-300 text-sm">fjcaminade@uc.edu.ph</p>
                </div>

                <!-- Second row of teachers -->
                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Dennis Durano</h3>
                    <p class="text-purple-300 text-sm">ddurano@uc.edu.ph</p>
                </div>

                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Leah Ybanez</h3>
                    <p class="text-purple-300 text-sm">lybanez@uc.edu.ph</p>
                </div>

                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Jia Nova Montecino</h3>
                    <p class="text-purple-300 text-sm">jnmontecino@uc.edu.ph</p>
                </div>

                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Jose Marcelito Brigoli</h3>
                    <p class="text-purple-300 text-sm">brigolitech3gsc@gmail.com</p>
                </div>

                <!-- Third row (2 teachers) -->
                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Wilson Gayo</h3>
                    <p class="text-purple-300 text-sm">wilschoy78@gmail.com</p>
                </div>

                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Brinda Demeterio</h3>
                    <p class="text-purple-300 text-sm">bbdemeterio@gmail.com</p>
                </div>
            </div>

            <!-- Part-time Teachers Heading -->
            <h3 class="text-2xl font-bold text-white text-center mb-8 border-t border-purple-500/30 pt-8 mt-12">
                PART-TIME TEACHERS
            </h3>

            <!-- Part-time Teachers Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Joaquin Patiño</h3>
                    <p class="text-purple-300 text-sm">jpatiño@uc.edu.ph</p>
                </div>

                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Jeff Salimbangon</h3>
                    <p class="text-purple-300 text-sm">jeff.salimbagon@uc.edu.ph</p>
                </div>

                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Beverly Lahaylahay</h3>
                    <p class="text-purple-300 text-sm">blahaylahay@uc.edu.ph</p>
                </div>

                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Manuel Arranguez</h3>
                    <p class="text-purple-300 text-sm">marranguez@uc.edu.ph</p>
                </div>

                <!-- Additional Part-time Teachers -->
                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Sherwin Bentulan</h3>
                    <p class="text-purple-300 text-sm">sbentulan@uc.edu.ph</p>
                </div>

                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Kent Ivan Nacua</h3>
                    <p class="text-purple-300 text-sm">kentivanunabia0211@gmail.com</p>
                </div>

                <div class="glass-card rounded-xl p-6 text-center transform transition-all hover:-translate-y-2 w-64">
                    <div class="w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden border-4 border-purple-500">
                        <img src="images/image.jpg" alt="Teacher Name" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-2xl font-semibold text-white mb-2">Christian Barral</h3>
                    <p class="text-purple-300 text-sm">cbarral@uc.edu.ph</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-white">Comprehensive Laboratory Management</h2>
            <p class="mt-4 text-xl text-gray-200 max-w-3xl mx-auto">
                Designed specifically for CCS laboratory monitoring and student sit-in tracking.
            </p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="glass-card rounded-xl p-6">
                <div class="w-12 h-12 rounded-lg gradient-border flex items-center justify-center mb-4">
                    <i class="fas fa-users text-white text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-white mb-3">Student Attendance</h3>
                <p class="text-gray-200 mb-4">
                    Track student presence in lab sessions with easy check-in/check-out functionality and automatic reporting.
                </p>
                <a href="Students.php" class="inline-flex items-center text-purple-400 hover:text-purple-600 font-medium">
                    <span>Monitor Students</span>
                    <i class="fas fa-arrow-right ml-2 text-sm"></i>
                </a>
            </div>
            <div class="glass-card rounded-xl p-6">
                <div class="w-12 h-12 rounded-lg gradient-border flex items-center justify-center mb-4">
                    <i class="fas fa-flask text-white text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-white mb-3">Session Management</h3>
                <p class="text-gray-200 mb-4">
                    Create and manage laboratory sessions, assign instructors, and set capacity limits for each lab.
                </p>
                <a href="#" class="inline-flex items-center text-purple-400 hover:text-purple-600 font-medium">
                    <span>Schedule Sessions</span>
                    <i class="fas fa-arrow-right ml-2 text-sm"></i>
                </a>
            </div>
            <div class="glass-card rounded-xl p-6">
                <div class="w-12 h-12 rounded-lg gradient-border flex items-center justify-center mb-4">
                    <i class="fas fa-laptop text-white text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-white mb-3">Equipment Inventory</h3>
                <p class="text-gray-200 mb-4">
                    Keep track of laboratory equipment usage, maintenance schedules, and availability for each session.
                </p>
                <a href="#" class="inline-flex items-center text-purple-400 hover:text-purple-600 font-medium">
                    <span>View Inventory</span>
                    <i class="fas fa-arrow-right ml-2 text-sm"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="glass-card mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid md:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center">
                    <img src="./logo/ccs.png" class="h-8 w-8 mr-2" alt="CCS Logo">
                    <span class="text-white text-xl font-bold">CCS Monitor</span>
                </div>
                <p class="mt-4 text-gray-200">
                    Efficiently manage and monitor student sit-in sessions in the College of Computer Studies laboratories.
                </p>
                <div class="mt-6 flex space-x-4">
                    <a href="#" class="text-gray-200 hover:text-white transition-colors">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="#" class="text-gray-200 hover:text-white transition-colors">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-200 hover:text-white transition-colors">
                        <i class="fab fa-linkedin"></i>
                    </a>
                </div>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider">Features</h3>
                <ul class="mt-4 space-y-2">
                    <li class="text-gray-200">Student Management</li>
                    <li class="text-gray-200">Lab Sessions</li>
                    <li class="text-gray-200">Equipment Tracking</li>
                    <li class="text-gray-200">Reports & Analytics</li>
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider">Resources</h3>
                <ul class="mt-4 space-y-2">
                    <li class="text-gray-200">Documentation</li>
                    <li class="text-gray-200">API Reference</li>
                    <li class="text-gray-200">Help Center</li>
                    <li class="text-gray-200">System Updates</li>
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider">Company</h3>
                <ul class="mt-4 space-y-2">
                    <li class="text-gray-200">About</li>
                    <li class="text-gray-200">Contact</li>
                    <li class="text-gray-200">Careers</li>
                    <li class="text-gray-200">Privacy</li>
                </ul>
            </div>
        </div>

        <!-- Updated border section -->
        <div class="border-t border-gray-800 mt-12 pt-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="text-center md:text-left">
                    <p class="text-sm text-gray-200">&copy; 2025 CCS Monitor. All rights reserved.</p>
                </div>
                <div class="text-center md:text-right">
                    <div class="space-x-4">
                        <a href="#" class="text-sm text-gray-200 hover:text-white transition-colors">Privacy Policy</a>
                        <span class="text-gray-500">|</span>
                        <a href="#" class="text-sm text-gray-200 hover:text-white transition-colors">Terms of Service</a>
                        <span class="text-gray-500">|</span>
                        <a href="#" class="text-sm text-gray-200 hover:text-white transition-colors">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
    // Add floating circles dynamically
    document.addEventListener('DOMContentLoaded', function() {
        const floatingCircles = document.querySelector('.floating-circles');
        for (let i = 0; i < 10; i++) {
            const size = Math.random() * 60 + 20;
            const circle = document.createElement('div');
            circle.classList.add('circle');
            circle.style.width = `${size}px`;
            circle.style.height = `${size}px`;
            circle.style.top = `${Math.random() * 100}%`;
            circle.style.left = `${Math.random() * 100}%`;
            circle.style.animationDuration = `${Math.random() * 10 + 10}s`;
            circle.style.animationDelay = `${Math.random() * 5}s`;
            floatingCircles.appendChild(circle);
        }
    });
</script>
</body>
</html>