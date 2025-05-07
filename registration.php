<?php

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idno = $_POST['Idno'];
    $lastname = $_POST['Lastname'];
    $firstname = $_POST['Firstname'];
    $midname = $_POST['Midname'];
    $course = $_POST['Course'];
    $year_level = $_POST['Year_Level'];
    $username = $_POST['Username'];
    $password = password_hash($_POST['Password'], PASSWORD_DEFAULT);
    $defaultImage = 'image.jpg'; 

    $sql = "INSERT INTO users (IDNO, LAST_NAME, FIRST_NAME, MID_NAME, COURSE, YEAR_LEVEL, USER_NAME, PASSWORD_HASH, UPLOAD_IMAGE) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssss", $idno, $lastname, $firstname, $midname, $course, $year_level, $username, $password, $defaultImage);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Registration successful!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="logo/ccs.png" type="image/x-icon">
    <title>Student Registration</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%); /* Blue gradient background */
            overflow: hidden;
            position: relative;
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

        .form-container {
            position: relative;
            z-index: 1; /* Ensure the form is above the canvas */
        }

        .form-container > div {
            background: #fff !important;
            border-radius: 1.5rem;
            box-shadow: 0 12px 48px 0 rgba(0,0,0,0.10);
            border: none;
        }
        .form-header {
            background: #fff !important;
            color: #111 !important;
            border-top-left-radius: 1.5rem;
            border-top-right-radius: 1.5rem;
            padding-top: 2rem;
            padding-bottom: 1.5rem;
            text-align: center;
        }
        .form-header h2 {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            color: #111;
            margin-bottom: 0.25rem;
        }
        .form-header p {
            color: #444;
            font-size: 1rem;
            font-weight: 400;
        }
        .form-section {
            padding: 2rem 2rem 1.5rem 2rem;
        }
        .form-label {
            display: block;
            font-weight: 600;
            color: #222;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        .form-input, .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 0.75rem;
            font-size: 1rem;
            color: #222;
            background: #fff;
            margin-bottom: 1.25rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-input:focus, .form-select:focus {
            border-color: #2563eb;
        }
        .form-btn {
            width: 100%;
            padding: 0.9rem 0;
            border-radius: 0.75rem;
            border: 2px solid #2563eb;
            background: #fff;
            color: #2563eb;
            font-weight: 700;
            font-size: 1.1rem;
            transition: background 0.2s, color 0.2s, border-color 0.2s;
            margin-top: 0.5rem;
        }
        .form-btn:hover, .form-btn:focus {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }
        .form-footer {
            text-align: center;
            padding-bottom: 1.5rem;
        }
        .form-footer a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: underline;
        }
        .form-footer a:hover {
            color: #1e40af;
        }
    </style>
</head>
<body>
    <canvas id="star-canvas"></canvas>
    <div class="form-container max-w-2xl w-full mx-auto">
        <div>
            <div class="form-header">
                <h2>STUDENT REGISTRATION</h2>                
            </div>
            <div class="form-section">
                <form id="registerForm" method="POST" action="" class="space-y-6">
                    <!-- ID Number with special styling -->
                    <div class="group relative">
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                        <div class="relative bg-white rounded-lg overflow-hidden">
                            <label for="Idno" class="form-label">ID Number</label>
                            <input type="text" id="Idno" name="Idno" class="w-full pr-4 py-3 border-0 focus:outline-none focus:ring-0" placeholder="Enter ID Number" required>
                        </div>
                    </div>
                    
                    <!-- Name Fields with consistent styling -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="group relative">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                            <div class="relative bg-white rounded-lg overflow-hidden">
                                <label for="Lastname" class="form-label">Last Name</label>
                                <input type="text" id="Lastname" name="Lastname" class="w-full pr-4 py-3 border-0 focus:outline-none focus:ring-0" placeholder="Enter Last Name" required>
                            </div>
                        </div>
                        <div class="group relative">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                            <div class="relative bg-white rounded-lg overflow-hidden">
                                <label for="Firstname" class="form-label">First Name</label>
                                <input type="text" id="Firstname" name="Firstname" class="w-full pr-4 py-3 border-0 focus:outline-none focus:ring-0" placeholder="Enter First Name" required>
                            </div>
                        </div>
                        <div class="group relative">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                            <div class="relative bg-white rounded-lg overflow-hidden">
                                <label for="Midname" class="form-label">Middle Name</label>
                                <input type="text" id="Midname" name="Midname" class="w-full pr-4 py-3 border-0 focus:outline-none focus:ring-0" placeholder="Enter Middle Name">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Course and Year Level with enhanced select styling -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="group relative">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                            <div class="relative bg-white rounded-lg overflow-hidden">
                                <label for="Course" class="form-label">Course</label>
                                <select id="Course" name="Course" class="w-full pr-4 py-3 border-0 focus:outline-none focus:ring-0 appearance-none" required>
                                    <option value="" disabled selected>Select a Course</option>
                                    <option value="BS IN ACCOUNTANCY">BS IN ACCOUNTANCY</option>
                                    <option value="BS IN BUSINESS ADMINISTRATION">BS IN BUSINESS ADMINISTRATION</option>
                                    <option value="BS IN CRIMINOLOGY">BS IN CRIMINOLOGY</option>
                                    <option value="BS IN CUSTOMS ADMINISTRATION">BS IN CUSTOMS ADMINISTRATION</option>
                                    <option value="BS IN INFORMATION TECHNOLOGY">BS IN INFORMATION TECHNOLOGY</option>
                                    <option value="BS IN COMPUTER SCIENCE">BS IN COMPUTER SCIENCE</option>
                                    <option value="BS IN OFFICE ADMINISTRATION">BS IN OFFICE ADMINISTRATION</option>
                                    <option value="BS IN SOCIAL WORK">BS IN SOCIAL WORK</option>
                                    <option value="BACHELOR OF SECONDARY EDUCATION">BACHELOR OF SECONDARY EDUCATION</option>
                                    <option value="BACHELOR OF ELEMENTARY EDUCATION">BACHELOR OF ELEMENTARY EDUCATION</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="group relative">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                            <div class="relative bg-white rounded-lg overflow-hidden">
                                <label for="Year_Level" class="form-label">Year Level</label>
                                <select id="Year_Level" name="Year_Level" class="w-full pr-4 py-3 border-0 focus:outline-none focus:ring-0 appearance-none" required>
                                    <option value="" disabled selected>Select a Year Level</option>
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Username and Password with enhanced styling -->
                    <div class="space-y-4">
                        <div class="group relative">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg blur opacity-10 group-hover:opacity-30 transition duration-200"></div>
                            <div class="relative bg-white rounded-lg overflow-hidden">
                                <label for="Username" class="form-label">Username</label>
                                <input type="text" id="Username" name="Username" class="w-full pr-4 py-3 border-0 focus:outline-none focus:ring-0" placeholder="Enter Username" required>
                            </div>
                        </div>
                        
                        <div class="group relative">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-pink-500 to-purple-500 rounded-lg blur opacity-20 group-hover:opacity-40 transition duration-200"></div>
                            <div class="relative bg-white rounded-lg overflow-hidden">
                                <label for="Password" class="form-label">Password</label>
                                <input type="password" id="Password" name="Password" class="w-full pr-10 py-3 border-0 focus:outline-none focus:ring-0" placeholder="Enter Password" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit button with enhanced styling -->
                    <div class="flex flex-col items-center justify-center space-y-6 mt-8">
                        <button type="submit" class="w-full sm:w-auto relative inline-flex items-center justify-center overflow-hidden rounded-lg group bg-gradient-to-br from-purple-600 to-blue-500 p-0.5 text-lg font-medium hover:text-white transition-all duration-300 hover:shadow-lg btn-hover-effect">
                            <span class="relative rounded-md bg-white px-10 py-3.5 transition-all duration-300 ease-in-out group-hover:bg-opacity-0 text-purple-700 font-bold group-hover:text-white flex items-center">
                                <span>Create Account</span>
                            </span>
                        </button>
                        
                        <div class="form-footer">
                            <p>Already have an account? <a href="login.php">Sign In</a></p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('star-canvas');
            const ctx = canvas.getContext('2d');

            function resizeCanvas() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            }

            function drawStars() {
                const starCount = Math.floor((canvas.width * canvas.height) / 8000);
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                for (let i = 0; i < starCount; i++) {
                    const x = Math.random() * canvas.width;
                    const y = Math.random() * canvas.height;
                    const radius = Math.random() * 1.5;
                    ctx.beginPath();
                    ctx.arc(x, y, radius, 0, Math.PI * 2);
                    ctx.fillStyle = 'white';
                    ctx.fill();
                }
            }

            window.addEventListener('resize', () => {
                resizeCanvas();
                drawStars();
            });

            resizeCanvas();
            drawStars();
        });

        document.addEventListener('DOMContentLoaded', function() {
            // ID Number validation
            const idnoInput = document.getElementById('Idno');
            idnoInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 4);
            });
            
            // Name fields validation - letters only
            const nameInputs = ['Lastname', 'Firstname', 'Midname'];
            nameInputs.forEach(function(id) {
                const input = document.getElementById(id);
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
                });
            });

            // Toggle password visibility
            const togglePassword = document.querySelector('.toggle-password');
            togglePassword.addEventListener('click', function() {
                const passwordInput = document.getElementById('Password');
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

            // Form submission
            const form = document.getElementById('registerForm');
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                
                // Check ID number validation only
                if (idnoInput.value.length !== 4) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid ID Number',
                        text: 'ID Number must be exactly 4 digits.'
                    });
                    return;
                }
                
                // Continue with form submission
                const formData = new FormData(this);
                
                fetch('registration.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Registration Successful!',
                            text: 'You can now login to your account.',
                            showConfirmButton: true,
                            confirmButtonText: 'Go to Login',
                            confirmButtonColor: '#6366F1'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'login.php';
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Registration Failed',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Something went wrong',
                        text: 'Please try again later.'
                    });
                });
            });
            
            // Login link redirection
            const loginLink = document.querySelector("a[href='login.php']");
            loginLink.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = 'login.php';
            });
            
            // Add animation to form elements on load
            const formElements = document.querySelectorAll('input, select, button');
            formElements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    element.style.transition = 'all 0.5s ease';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, 100 + (index * 50));
            });
        });
    </script>
</body>
</html>