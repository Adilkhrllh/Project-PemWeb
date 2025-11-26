    <!-- LOGIN.PHP -->
    <?php
    session_start();
    include("config/koneksi.php");
    require "components/components.php";
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <?= head("Login Page") ?>
    </head>

    <body>
        <div class="foto"></div>
        <div class="container" id="container">
            <div class="form-container sign-up">
                <form action="logic/auth.php" method="POST">
                    <h1>Create Account</h1>
                    
                    <?php
                    if (isset($_GET['status'])) {
                        echo "<br>";

                        $register_errors = ["gagal_mendaftar", "password_tidak_sama", "email_terdaftar", "username_terdaftar"];

                        if (in_array($_GET['status'], $register_errors)) {
                            listAlert($_GET['status']);
                        }
                    }
                    ?>

                    <div class="social-icons">
                        <a href="#" class="icon"><i class="fa-brands fa-google-plus-g" style="color: #1e3050"></i></a>
                        <a href="#" class="icon"><i class="fa-brands fa-facebook-f" style="color: #1e3050"></i></a>
                        <a href="#" class="icon"><i class="fa-brands fa-github" style="color: #1e3050"></i></a>
                        <a href="#" class="icon"><i class="fa-brands fa-linkedin-in" style="color: #1e3050"></i></a>
                    </div>
                    <span>or use your email for registeration</span>
                    <input type="text" id="username" name="username" class="form-control border-0 ps-0" placeholder="Username" required>
                    <input type="email" id="email" name="email" class="form-control border-0 ps-0" placeholder="Email" required>
                    <input type="password" id="password" name="password" class="form-control border-0 ps-0" placeholder="Password" required>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control border-0 ps-0" placeholder="Confirm Password" required>
                    <button type="submit" name="register">Sign Up</button>
                </form>
            </div>
            <div class="form-container sign-in">
                <form action="logic/auth.php" method="POST">
                    <h1>Sign In</h1>

                    <?php
                    if (isset($_GET['status'])) {
                        echo "<br>";

                        $signin_success = ["berhasil_logout","berhasil_mendaftar", "login_berhasil", "gagal_login", "login_dulu"];

                        if (in_array($_GET['status'], $signin_success)) {
                            listAlert($_GET['status']);
                        }
                    }
                    ?>

                    <div class="social-icons">
                        <a href="#" class="icon"><i class="fa-brands fa-google-plus-g" style="color: #1e3050"></i></a>
                        <a href="#" class="icon"><i class="fa-brands fa-facebook-f" style="color: #1e3050"></i></a>
                        <a href="#" class="icon"><i class="fa-brands fa-github" style="color: #1e3050"></i></a>
                        <a href="#" class="icon"><i class="fa-brands fa-linkedin-in" style="color: #1e3050"></i></a>
                    </div>
                    <span>or use your email / username, password</span>
                    <input type="text" name="username" placeholder="Email or Username">
                    <input type="password" name="password" placeholder="Password">
                    <a href="#">Forget Your Password?</a>
                    <button type="submit" name="login">Sign In</button>
                </form>
            </div>
            <div class="toggle-container">
                <div class="toggle">
                    <div class="toggle-panel toggle-left">
                        <h1>Welcome Back!</h1>
                        <p>Enter your personal details to use all of site features</p>
                        <button class="hidden" id="login">Sign In</button>
                    </div>
                    <div class="toggle-panel toggle-right">
                        <h1>Hello, Friend!</h1>
                        <p>Register with your personal details to use all of site features</p>
                        <button class="hidden" id="register">Sign Up</button>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="js/script.js"></script>
        <script>
        const url = new URLSearchParams(window.location.search);
        if (url.get('signin') === "1") {
            document.getElementById('container').classList.remove("active");
        }

        if (url.get('register') === "1") {
            document.getElementById('container').classList.add("active");
        }
        </script>
    </body>
    </html>
