<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require '../db/pass_conn.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'microfinancehr2@gmail.com';
        $mail->Password = 'yjla pidq jfdr qbnz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $checkEmailSql = "SELECT * FROM admin_register WHERE email = ?";
        $checkStmt = $db->prepare($checkEmailSql);
        $checkStmt->execute([$email]);

        if ($checkStmt->rowCount() > 0) {
            date_default_timezone_set('Asia/Manila');
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$email, $token, $expiresAt]);

            $mail->setFrom('microfinancehr2@gmail.com', 'Microfinance');
            $mail->addAddress($email);
            $mail->Subject = 'Password Reset Request';
            $resetLink = "http://localhost/HR2/main/reset_password.php?token=$token";
            $mail->isHTML(true);
            $mail->Body = 'Hello, <br><br>To reset your password, click the following link: <a href="' . $resetLink . '">Reset Password</a>';
            //$mail->addAttachment('../img/thirdy.jpg'); // Adjust path and name as needed
            $mail->send();
            $message = '<div class="alert alert-success text-center">Password reset link sent successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger text-center">Email does not exist.</div>';
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger text-center">Message could not be sent. Mailer Error: ' . $mail->ErrorInfo . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Password Recovery</title>
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="bg-black">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center mt-5">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-3 bg-dark">
                                <div class="card-header border-bottom border-2 border-warning">
                                    <h3 class="text-center text-light font-weight-light my-4">Password Recovery</h3>
                                    <?php if (!empty($message)) echo $message; ?>
                                </div>
                                <div class="card-body">
                                    <div class="small mb-3 text-light">Enter your email address and we will send you a
                                        link to reset your password.</div>
                                    <form method="POST" action="">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputEmail" name="email" type="email" placeholder="name@example.com" required />
                                            <label for="inputEmail">Email address</label>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-end mt-4 mb-2">
                                            <button type="submit" class="btn btn-primary align-items-center w-100">Send Link</button>
                                        </div>
                                        <div class="text-center mt-3 mb-2"> <a class="btn border-secondary w-100 text-light border border-2" href="../main/adminlogin.php">Back</a></div>
                                    </form>
                                </div>
                                <div class="card-footer text-center border-top border-2 border-warning">
                                    <div class="text-center text-muted">Human Resource 2</a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <div id="layoutAuthentication_footer">
            <footer class="py-4 bg-dark mt-auto">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="/js/scripts.js"></script>
</body>
</html>
