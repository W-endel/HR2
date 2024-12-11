<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Selection</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- External CSS file -->
    <style>
    /* Make the buttons long */
    #admin-login-btn, #employee-login-btn {
        width: 700px; /* Set button width to 300px for both */
        height: 100px;
        padding: 15px 0; /* Increase vertical padding */
        font-size: 40px;    
        text-align: center;
    }
</style>

</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-black text-light">

<div class="container-fluid">
    <!-- Header text at the top -->
    <div class="text-center mt-5">
        <h1 class="display-4 mb-3">Select Your Login</h1>
        <p class="lead">Please choose the appropriate login option to access your portal.</p>
    </div>

    <!-- Center the buttons in the middle of the screen -->
    <div class="d-flex justify-content-center align-items-center" style="height: 60vh;">
        <div class="d-flex justify-content-center gap-4">
            <a href="../admin/login.php" class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-lg" id="admin-login-btn">Admin Login</a>
            <a href="../employee/login.php" class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-lg" id="employee-login-btn">Employee Login</a>
        </div>
    </div>
</div>



    </div>


</body>
</html>
