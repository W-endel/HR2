<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Selection</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f0f0f0;
            padding: 50px;
        }
        h1 {
            margin-bottom: 30px;
        }
        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        .login-button {
            padding: 15px 30px;
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

    <h1>Select Login Type</h1>
    <div class="button-container">
        <button class="login-button admin" onclick="location.href='../main/adminlogin.php'">Admin Login</button>
        <button class="login-button employee" onclick="location.href='../e_portal/employee_login.php'">Employee Login</button>
    </div>

</body>
</html>
