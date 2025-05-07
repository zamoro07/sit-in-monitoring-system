<?php

session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['Username'];
    $password = $_POST['Password'];

    // Check if admin is logging in
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['admin'] = true;
        echo json_encode(["status" => "success", "message" => "Admin login successful!"]);
        exit;
    }

    $sql = "SELECT STUD_NUM, PASSWORD_HASH, FIRST_NAME FROM users WHERE USER_NAME = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($userId, $password_hash, $first_name);
    $stmt->fetch();

    header('Content-Type: application/json');
    if (password_verify($password, $password_hash)) {
        $_SESSION['user_id'] = $userId; 
        $_SESSION['first_name'] = $first_name; 
        echo json_encode(["status" => "success", "message" => "Login successful!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid username or password."]);
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="logo/ccs.png" type="image/x-icon">
    <title>CCS Sit-in Monitoring - Login</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="script.js" defer></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            overflow: hidden;
        }
        #star-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 0;
            pointer-events: none;
        }
        .login-card {
            box-shadow: 0 12px 48px 0 rgba(0,0,0,0.22), 0 0 0 6px #fff;
            border: 4px solid #2563eb;
            background: #fff;
            border-radius: 2rem;
            padding: 0;
        }
        .glass-effect {
            background: white;
            box-shadow: none;
            border: none;
        }
        .header-gradient {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            border-top-left-radius: 2rem;
            border-top-right-radius: 2rem;
        }
        .input-highlight {
            border: 2px solid #e5e7eb;
            border-radius: 1rem;
            background: #f9fafb;
            transition: box-shadow 0.2s, border-color 0.2s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .input-highlight:focus-within {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.10);
            background: #fff;
        }
        input[type="text"], input[type="password"] {
            border-radius: 1rem;
            background: transparent;
            font-size: 1rem;
        }
        .login-btn {
            border: 2px solid #2563eb;
            color: #2563eb;
            background: #fff;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: background 0.2s, color 0.2s, transform 0.15s;
            box-shadow: 0 2px 8px rgba(37,99,235,0.08);
        }
        .login-btn:hover, .login-btn:focus {
            background: linear-gradient(90deg, #2563eb 0%, #3b82f6 100%);
            color: #fff;
            transform: scale(1.03);
            border-color: #2563eb;
        }
        .login-title {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            color: #111;
            text-shadow: none;
        }
        .login-subtitle {
            color: #111;
            font-size: 1rem;
            font-weight: 400;
            margin-top: 0.5rem;
        }
        .login-label {
            color: #111;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.03em;
        }
        .login-link {
            color: #111;
            font-weight: 600;
            transition: color 0.2s;
        }
        .login-link:hover {
            color: #2563eb;
            text-decoration: underline;
        }
        @media (max-width: 640px) {
            .login-card {
                border-radius: 1.2rem;
            }
            .header-gradient {
                border-top-left-radius: 1.2rem;
                border-top-right-radius: 1.2rem;
            }
        }
    </style>
</head>
<body class="h-screen flex items-center justify-center" style="position:relative; overflow:hidden;">
    <!-- Starry background canvas -->
    <canvas id="star-canvas"></canvas>
    <div class="login-card glass-effect w-11/12 max-w-md rounded-3xl overflow-hidden" style="z-index:1; position:relative;">
        <!-- Header -->
        <div class="pt-6 pb-8 px-6" style="background: none;">
            <div class="flex justify-center mb-3">
                <div class="flex space-x-3 p-3 rounded-2xl bg-white">
                        <img src="logo/uc.png" alt="UC Logo" class="w-14 h-14 object-contain">
                        <img src="logo/ccs.png" alt="CCS Logo" class="w-14 h-14 object-contain">
                </div>
            </div>
            <div class="text-center">
                <div class="inline-block relative">
                    <h1 class="login-title">CCS SIT-IN MONITORING</h1>
                </div>
                <p class="login-subtitle">Enter your credentials to access the system</p>
            </div>
        </div>
        <div class="h-1.5" style="background: #e5e7eb;"></div>
        <div class="px-8 py-8 relative overflow-hidden bg-white">
            <form id="loginForm" method="POST" action="" class="relative z-10 space-y-6">
                <!-- Username input -->
                <div class="input-container">
                    <label for="Username" class="login-label mb-1.5 pl-2">USERNAME</label>
                    <div class="relative group">
                        <div class="input-highlight flex items-center rounded-xl overflow-hidden">
                            <input type="text" id="Username" name="Username" 
                                   class="w-full py-3 px-4 border-none focus:ring-0 focus:outline-none input-effect" 
                                   placeholder="Enter your username" required>
                        </div>
                    </div>
                </div>
                <!-- Password input -->
                <div class="input-container">
                    <label for="Password" class="login-label mb-1.5 pl-2">PASSWORD</label>
                    <div class="relative group">
                        <div class="input-highlight flex items-center rounded-xl overflow-hidden">
                            <input type="password" id="Password" name="Password" 
                                   class="w-full py-3 px-4 border-none focus:ring-0 focus:outline-none input-effect" 
                                   placeholder="Enter your password" required>
                            <button type="button" id="togglePassword" class="pr-4 text-gray-400 hover:text-blue-600 transition-colors">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Login button -->
                <div class="pt-2 flex justify-center">
                    <button type="submit" class="login-btn w-full sm:w-auto px-10 py-3.5">
                        <span>Login</span>
                    </button>
                </div>
                <!-- Registration link -->
                <div class="text-center mt-6">
                    <p class="text-black text-sm">
                        Don't have an account? 
                        <a href="registration.php" class="login-link">Create Account</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Starry background script with twinkle
        function drawStars() {
            const canvas = document.getElementById('star-canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            // Store stars for twinkle
            window._stars = [];
            const starCount = Math.floor((canvas.width * canvas.height) / 1800);
            for (let i = 0; i < starCount; i++) {
                const x = Math.random() * canvas.width;
                const y = Math.random() * canvas.height;
                const r = Math.random() * 0.8 + 0.3;
                const twinkle = Math.random() < 0.25; // 25% twinkle
                window._stars.push({x, y, r, twinkle, phase: Math.random() * Math.PI * 2});
                ctx.beginPath();
                ctx.arc(x, y, r, 0, 2 * Math.PI);
                ctx.fillStyle = '#111';
                ctx.globalAlpha = 1;
                ctx.fill();
            }
            ctx.globalAlpha = 1;
        }
        function animateStars() {
            const canvas = document.getElementById('star-canvas');
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (!window._stars) return;
            for (const star of window._stars) {
                let alpha = 1;
                if (star.twinkle) {
                    alpha = 0.5 + 0.5 * Math.sin(Date.now() / 700 + star.phase);
                }
                ctx.beginPath();
                ctx.arc(star.x, star.y, star.r, 0, 2 * Math.PI);
                ctx.fillStyle = '#111';
                ctx.globalAlpha = alpha;
                ctx.fill();
            }
            ctx.globalAlpha = 1;
            requestAnimationFrame(animateStars);
        }
        window.addEventListener('resize', () => { drawStars(); });
        window.addEventListener('DOMContentLoaded', function() {
            drawStars();
            animateStars();
            // Password toggle
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('Password');
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                // Toggle icon
                const icon = this.querySelector('i');
                if (type === 'password') {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
        });
    </script>
</body>
</html>