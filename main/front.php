<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Selection</title>
    <link href="../css/styles.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: black;
            padding: 50px;
        }
        h1 {
            margin-bottom: 30px;
        }
        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 170px;
            justify-content: space-between;
            margin-left: 250px;
            margin-right: 250px;
        }
        .login-button {
            padding: 20px 50px;
            font-size: 18px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .login-button:hover {
            background-color: #0056b3;
        }
        .login-button.admin {
            background-color: #28a745;
        }
        .login-button.admin:hover {
            background-color: #218838;
        }
        .login-button.employee {
            background-color: #ffc107;
        }
        .login-button.employee:hover {
            background-color: #e0a800;
        }
    </style>
</head>
<body>

    <h1 class="text-light">Login as?</h1>
    <div class="button-container">
        <button class="login-button admin mt-4" onclick="location.href='../main/adminlogin.php'">Admin Login</button>
        <button class="login-button employee mt-4" onclick="location.href='../e_portal/employee_login.php'">Employee Login</button>
    </div>

</body>
</html>
