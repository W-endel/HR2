<?php
require '../db/pass_conn.php'; // Ensure this is the correct file for the database connection

$expiresAt = null;
$message = ''; // Initialize a variable to hold the message

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Query expiration time of the token
    $sql = "SELECT expires_at FROM password_resets WHERE token = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$token]);
    $expiresAt = $stmt->fetchColumn();
    
    if (!$expiresAt) {
        echo "Invalid or expired token.";
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newPassword = $_POST['new_password'];
    $confirmNewPassword = $_POST['confirm_new_password'];
    
    // Check if passwords match
    if ($newPassword === $confirmNewPassword) {
        // Proceed with password update
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $updateSql = "UPDATE admin_register SET password = ? WHERE email = (SELECT email FROM password_resets WHERE token = ?)";
        $updateStmt = $db->prepare($updateSql);
        $updateStmt->execute([$hashedPassword, $token]);

        $message = "<div class='alert alert-success text-center'>Password reset successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger text-center'>Passwords do not match.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Reset Password</title>
    <link href="../css/styles.css" rel="stylesheet" />
</head>
<body class="bg-black">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-lg-5">
                <div class="card shadow-lg border-0 rounded-lg mt-5 bg-dark">
                    <div class="card-header border-bottom border-2 border-warning">
                        <h3 class="text-center text-light font-weight-light my-4">Reset Your Password</h3>
                        <?php if (!empty($message)) echo $message; ?>
                    </div>
                        <div class="card-body">
                            <p class="small mb-3 text-light">Token expires in: <span id="countdown"></span></p>
                            <p class="small text-info text-center">Change your password immediately before the token expire.</p>
                                <form method="POST" action="" onsubmit="return validatePasswords()">
                                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>" />
                        
                                    <div class="form-floating mb-3">
                                        <input class="form-control" id="new_password" name="new_password" type="password" placeholder="New Password" required />
                                        <label for="new_password">New Password</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input class="form-control" id="confirm_new_password" name="confirm_new_password" type="password" placeholder="Confirm New Password" required />
                                        <label for="confirm_new_password">Confirm New Password</label>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                                    </div>
                                    <div class="text-center">
                                            <div class="text-center mt-3 mb-0"> <a class="btn border-secondary w-100 text-light border border-2" href="../main/adminlogin.php">Back</a></div>
                                    </div>
                                </form>
                        </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var expiresAt = new Date("<?php echo $expiresAt; ?>").getTime();
            var countdownElement = document.getElementById('countdown');

            var countdownInterval = setInterval(function () {
                var now = new Date().getTime();
                var distance = expiresAt - now;

                if (distance < 0) {
                    clearInterval(countdownInterval);
                    countdownElement.innerHTML = "Token expired.";
                } else {
                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    countdownElement.innerHTML = minutes + "m " + seconds + "s ";
                }
            }, 1000);
        });

        function validatePasswords() {
            const newPassword = document.getElementById("new_password").value;
            const confirmPassword = document.getElementById("confirm_new_password").value;

            if (newPassword !== confirmPassword) {
                alert("Passwords do not match.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
