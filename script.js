document.addEventListener("DOMContentLoaded", function() {
    const registerForm = document.getElementById("registerForm");
    if (registerForm) {
        registerForm.addEventListener("submit", function (e) {
            e.preventDefault(); 

            let formData = new FormData(this);

            fetch("registration.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    title: data.status === "success" ? "Success!" : "Error!",
                    text: data.message,
                    icon: data.status,
                    timer: data.status === "success" ? 2000 : null,
                    showConfirmButton: true, 
                    confirmButtonText: "Okay",
                    allowOutsideClick: false,
                    scrollbarPadding: false,
                    heightAuto: false
                }).then(() => {
                    if (data.status === "success") {
                        registerForm.reset();
                        document.getElementById("signup").style.display = "none";
                        document.getElementById("signIn").style.display = "block";
                    }
                });
            })
            .catch(error => {
                console.error("Error:", error);  
            });
        });
    }

    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            e.preventDefault(); 
            let formData = new FormData(this);

            fetch("login.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    title: data.status === "success" ? "Success!" : "Error!",
                    text: data.message,
                    icon: data.status,
                    showConfirmButton: true, 
                    confirmButtonText: "Okay",
                    allowOutsideClick: false,
                    scrollbarPadding: false,
                    heightAuto: false
                }).then((result) => {
                    if (result.isConfirmed && data.status === "success") {
                        if (formData.get('Username') === 'admin') {
                            window.location.href = "admin/admin_dashboard.php";  
                        } else {
                            window.location.href = "user/dashboard.php";  
                        }
                    }
                });
            })
            .catch(error => {
                console.error("Error:", error);  
            });
        });
    }

    const logoutLink = document.querySelector("a[href='../login.php']");
    if (logoutLink) {
        logoutLink.addEventListener("click", function(e) {
            e.preventDefault();
            fetch("logout.php", {
                method: "POST"
            })
            .then(response => {
                if (response.ok) {
                    window.location.href = "login.php";
                } else {
                    console.error("Logout failed");
                }
            })
            .catch(error => {
                console.error("Error:", error);
            });
        });
    }

    const editForm = document.getElementById("editForm");
    if (editForm) {
        const profileImage = document.querySelector(".profile-image");
        const fileInput = document.getElementById("fileInput");

        profileImage.addEventListener("click", function() {
            fileInput.click();
        });

        fileInput.addEventListener("change", function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        editForm.addEventListener("submit", function (e) {
            e.preventDefault(); 

            let formData = new FormData(this);

            fetch("edit.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    title: data.status === "success" ? "Success!" : "Error!",
                    text: data.message,
                    icon: data.status,
                    showConfirmButton: true, 
                    confirmButtonText: "Okay",
                    allowOutsideClick: false,
                    scrollbarPadding: false,
                    heightAuto: false
                }).then(() => {
                    if (data.status === "success") {
                        window.location.href = "profile.php";
                    }
                });
            })
            .catch(error => {
                console.error("Error:", error);  
            });
        });
    }

    // Password visibility toggle for registration form
    const registerPasswordToggle = document.getElementById("registerPasswordToggle");
    const registerPasswordInput = document.getElementById("registerPassword");
    if (registerPasswordToggle && registerPasswordInput) {
        registerPasswordToggle.addEventListener("click", function() {
            const type = registerPasswordInput.getAttribute("type") === "password" ? "text" : "password";
            registerPasswordInput.setAttribute("type", type);
            this.classList.toggle("fa-eye-slash");
        });
    }

    // Password visibility toggle for login form
    const loginPasswordToggle = document.getElementById("loginPasswordToggle");
    const loginPasswordInput = document.getElementById("loginPassword");
    if (loginPasswordToggle && loginPasswordInput) {
        loginPasswordToggle.addEventListener("click", function() {
            const type = loginPasswordInput.getAttribute("type") === "password" ? "text" : "password";
            loginPasswordInput.setAttribute("type", type);
            this.classList.toggle("fa-eye-slash");
        });
    }
});

