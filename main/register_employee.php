<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Register - SB Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="bg-dark">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-7">
                            <div class="card shadow-lg border-0 rounded-lg mt-5 my-5">
                                <div class="card-header">
                                    <h3 class="text-center font-weight-light my-4">Create Employee Account</h3>
                                    <div id="form-feedback" class="alert text-center" style="display: none;"></div>
                                </div>
                                <div class="card-body">
                                    <form id="registrationForm">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input class="form-control" id="inputFirstName" type="text"
                                                        name="firstname" placeholder="Enter your first name" required />
                                                    <label for="inputFirstName">First name</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating"> 
                                                    <input class="form-control" id="inputLastName" type="text"
                                                        name="lastname" placeholder="Enter your last name" required />
                                                    <label for="inputLastName">Last name</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputEmail" type="email"
                                                name="email" placeholder="name@example.com" required />
                                            <label for="inputEmail">Email address</label>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input class="form-control" id="inputPassword" type="password"
                                                        name="password" placeholder="Create a password" required />
                                                    <label for="inputPassword">Password</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input class="form-control" id="inputPasswordConfirm"
                                                        type="password" name="confirm_password" placeholder="Confirm password" required />
                                                    <label for="inputPasswordConfirm">Confirm Password</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <select id="inputRole" name="role" class="form-select" required>
                                                        <option value="" disabled selected></option>
                                                        <option value="Admin">Admin</option>
                                                        <option value="Employee">Employee</option>
                                                    </select>
                                                    <label for="inputRole">Select Role</label>
                                                </div>
                                            </div>
                                         <div class="col-md-6">
                                            <div class="form-floating mb-3 mb-md-0">
                                                    <select id="inputPosition" name="position" class="form-select" required>
                                                        <option value="" disabled selected></option>
                                                        <option value="Chief Executive Officer (CEO)">Chief Executive Officer (CEO)</option>
                                                        <option value="Chief Financial Officer (CFO)">Chief Financial Officer (CFO)</option>
                                                        <option value="Operations Manager">Operations Manager</option>
                                                        <option value="Loan Officer">Loan Officer</option>
                                                        <option value="Credit Analyst">Credit Analyst</option>
                                                        <option value="Field Officer">Field Officer</option>
                                                        <option value="Client Relationship Manager">Client Relationship Manager</option>
                                                        <option value="Marketing Manager">Marketing Manager</option>
                                                        <option value="Financial Educator">Financial Educator</option>
                                                        <option value="Compliance Officer">Compliance Officer</option>
                                                        <option value="IT Manager">IT Manager</option>
                                                        <option value="Human Resources Manager">Human Resources Manager</option>
                                                        <option value="Data Analyst">Data Analyst</option>
                                                        <option value="Risk Manager">Risk Manager</option>
                                                        <option value="Administrative Assistant">Administrative Assistant</option>
                                                    </select>
                                                    <label for="inputPosition">Select Position</label>
                                                </div>
                                        </div>
                                            </div>
                                            <div class="form-floating mb-3">
                                                    <select id="inputDepartment" name="department" class="form-select" required>
                                                        <option value="" disabled selected></option>
                                                        <option value="Finance Department">Finance Department</option>
                                                        <option value="Operations Department">Operations Department</option>
                                                        <option value="Credit and Loan Services Department">Credit and Loan Services Department</option>
                                                        <option value="Risk Management Department">Risk Management Department</option>
                                                        <option value="Marketing Department">Marketing Department</option>
                                                        <option value="Client Services Department">Client Services Department</option>
                                                        <option value="Compliance Department">Compliance Department</option>
                                                        <option value="Human Resources Department">Human Resources Department</option>
                                                        <option value="IT Department">IT Department</option>
                                                        <option value="Data Analysis Department">Data Analysis Department</option>
                                                        <option value="Training and Development Department">Training and Development Department</option>
                                                    </select>
                                                    <label for="inputDepartment">Select Department</label>
                                            </div>                                               
                                        <div class="mt-4 mb-0">
                                            <div class="d-grid">
                                                <button class="btn btn-primary btn-block" type="submit">Create Account</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center py-3">
                                    <div class="small"><a href="../main/employee_login.php">Have an account? Go to login</a></div>
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
    <script src="../js/employee_register.js"></script>
</body>

</html>
