<?php
session_start();

// Assume $adminId is fetched from the database during login
if ($loginSuccessful) {
    $_SESSION['e_id'] = $employeeId; // Set admin ID in session
    // Redirect to dashboard or another page
    header("Location: ../e_portal/employee_dashboard.php");
    exit();
} else {
    echo "Login failed. Please try again.";
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Login - HR2</title>
    <link href="../css/styles.css" rel="stylesheet" />
</head>

<body class="bg-dark">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header">
                                    <h3 class="text-center font-weight-light my-4">Employee Login</h3>
                                    <i class="fa-solid fa-house"></i>
                                    <!-- Display error message if it exists -->
                                    <?php if (isset($_GET['error'])): ?>
                                        <div class="alert alert-danger text-center" role="alert">
                                            <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <form action="../e_portal/employeelogin_conn.php" method="post">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputEmail" type="email" name="email"
                                                placeholder="name@example.com" required />  
                                                <label for="inputEmail">Email address:</label>                                          
                                        </div>
                                        <div class="form-floating mb-3">

                                            <input class="form-control" id="inputPassword" type="password" name="password"
                                                placeholder="Password" required />
                                                <label for="inputPassword">Password:</label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" id="inputRememberPassword" type="checkbox"
                                                name="remember" value="" />
                                            <label class="form-check-label" for="inputRememberPassword">Remember
                                                Password</label>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                            <a class="small" href="../main/password.php">Forgot Password?</a>
                                            <button type="submit" class="btn btn-primary">Login</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <div id="layoutAuthentication_footer">
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
    <script src="../js/scripts.js"></script>
</body>

</html>
