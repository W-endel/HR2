<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Login Attempts</title>
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body class="bg-black">
    <div class="container mt-5">
        <div class="alert alert-dark">
                <!-- Email field (now user types the email directly) -->
            <form method="POST" action="../db/reset_login_attempt.php">
                <div class="mb-3">
                    <label for="email" class="form-label text-dark">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                </div>
            
                <!-- Reset Button Form -->
                <button type="submit" name="reset_login_attempt" class="btn btn-primary">Reset Login Attempt</button>
            </form>
        </div>
    </div>

    <!-- Add Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
