<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";        
$password = "";            
$dbname = "hr2";           

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['update_success'])) {
    echo '<script>alert("' . htmlspecialchars($_SESSION['update_success']) . '");</script>';
    unset($_SESSION['update_success']);
}

// Fetch user info
$adminId = $_SESSION['a_id'];
$sql = "SELECT a_id, firstname, middlename, lastname, birthdate, email, role, position, department, phone_number, address, pfp FROM admin_register WHERE a_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$userInfo = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="User Profile Dashboard" />
    <meta name="author" content="Your Name" />
    <title>User Profile - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="bg-secondary" id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sidenav navbar navbar-dark bg-dark">
                <div class="container-fluid">
                    <div class="big text-light">Hello, <?php echo htmlspecialchars($userInfo['firstname'] . ' ' . $userInfo['middlename'] . ' ' . $userInfo['lastname']); ?></div>
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="../main/front.php" onclick="confirmLogout(event)">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4 bg-dark">
                    <h1 class="big mt-4 text-light">Your Profile</h1>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card mb-4 border border-light">
                                <div class="card-header bg-secondary">
                                    <h5 class="card-title text-center text-light">Profile Picture</h5>
                                </div>
                                <div class="card-body text-center bg-dark">
                                    <img src="<?php echo !empty($userInfo['pfp']) ? htmlspecialchars($userInfo['pfp']) : '../img/defaultpfp.png'; ?>" class="img-fluid rounded-circle border border-light" width="200" height="200" alt="Profile Picture">
                                    <a href="javascript:void(0);" id="editPictureButton">
                                        <i class="text-light me-0 fas fa-edit"></i>
                                    </a>
                                    <input type="file" id="profilePictureInput" name="profile_picture" style="display:none;" accept="image/*">
                                    <table class="table text-light mt-3 text-start">
                                        <tr>
                                            <td>Name:</td>
                                            <td><?php echo htmlspecialchars($userInfo['firstname'] . ' ' . $userInfo['middlename'] . ' ' . $userInfo['lastname']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>ID:</td>
                                            <td>#<?php echo htmlspecialchars($userInfo['a_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Role:</td>
                                            <td><?php echo htmlspecialchars($userInfo['role']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Position:</td>
                                            <td><?php echo htmlspecialchars($userInfo['position']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Department:</td>
                                            <td><?php echo htmlspecialchars($userInfo['department']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Email:</td>
                                            <td><?php echo htmlspecialchars($userInfo['email']); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card mb-4 border border-light">
                                <div class="card-header bg-secondary">
                                    <h5 class="card-title text-center text-light">Your Information</h5>
                                </div>
                                <div class="card-body bg-dark">
                                    <form id="infoForm" action="../main/update_profile.php" method="post">
                                        <div class="row mb-3">
                                            <label for="inputfName" class="col-sm-2 col-form-label text-light">First Name:</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control bg-dark text-light border border-light" id="inputfName" name="firstname" value="<?php echo htmlspecialchars($userInfo['firstname']); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="inputmName" class="col-sm-2 col-form-label text-light">Middle Name:</label>
                                            <div class="col-sm-9"> 
                                                <input type="text" class="form-control bg-dark text-light border border-light" id="inputmName" name="middlename" value="<?php echo htmlspecialchars($userInfo['middlename']); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="inputlName" class="col-sm-2 col-form-label text-light">Last Name:</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control bg-dark text-light border border-light" id="inputlName" name="lastname" value="<?php echo htmlspecialchars($userInfo['lastname']); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="inputbirth" class="col-sm-2 col-form-label text-light">Birthdate:</label>
                                            <div class="col-sm-9">
                                                <input type="date" class="form-control bg-dark text-light border border-light" id="inputbirth" name="birthdate" value="<?php echo htmlspecialchars($userInfo['birthdate']); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="inputEmail" class="col-sm-2 col-form-label text-light">Email Address:</label>
                                            <div class="col-sm-9">
                                                <input type="email" class="form-control bg-dark text-light border border-light" id="inputEmail" name="email" value="<?php echo htmlspecialchars($userInfo['email']); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="inputPhone" class="col-sm-2 col-form-label text-light">Phone Number:</label>
                                            <div class="col-sm-9">
                                                <input type="number" class="form-control bg-dark text-light border border-light" id="inputPhone" name="phone_number" value="<?php echo htmlspecialchars($userInfo['phone_number']); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="inputAddress" class="col-sm-2 col-form-label text-light">Address:</label>
                                            <div class="mb-3 col-sm-9">
                                                <textarea class="form-control border bg-dark text-light border-light" id="inputAddress" name="address" rows="1" readonly><?php echo htmlspecialchars($userInfo['address']); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <button type="submit" class="btn btn-secondary border border-light d-none">Save Changes</button>
                                            <button type="button" id="editButton" class="btn btn-secondary border border-light">Update Information</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto bg-dark">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2024</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms & Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>
    <script src="../js/profile.js"></script>
</body>
</html>

