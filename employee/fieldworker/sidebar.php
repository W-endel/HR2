<div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading text-center text-muted">Home</div>
                        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                            <li class="nav-item dropdown text">
                                <a class="nav-link dropdown-toggle text-light d-flex justify-content-center ms-4" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php
                                    // Check if a custom profile picture exists
                                    if (!empty($employeeInfo['pfp']) && $employeeInfo['pfp'] !== 'defaultpfp.jpg') {
                                        // Display the custom profile picture
                                        echo '<img src="' . htmlspecialchars($employeeInfo['pfp']) . '" class="rounded-circle border border-light" width="80" height="80" alt="Profile Picture" />';
                                    } else {
                                        // Generate initials from the first name and last name
                                        $firstName = $employeeInfo['first_name'] ?? '';
                                        $lastName = $employeeInfo['last_name'] ?? '';
                                        $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

                                        // Display the initials in a circular container
                                        echo '<div class="rounded-circle border border-light d-flex justify-content-center align-items-center" style="width: 80px; height: 80px; background-color: rgba(16, 17, 18); color: white; font-size: 24px; font-weight: bold;">' . $initials . '</div>';
                                    }
                                    ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item loading" href="../../employee/fieldworker/profile.php">Profile</a></li>
                                    <li><hr class="dropdown-divider border-black" /></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a></li>
                                </ul>
                            </li>
                            
                            <li class="nav-item text-light d-flex ms-3 flex-column align-items-center text-center">
                                <span class="big text-light mb-1">
                                    <?php
                                        if ($employeeInfo) {
                                        echo htmlspecialchars($employeeInfo['first_name'] . ' ' . $employeeInfo['last_name']);
                                        } else {
                                        echo "employee information not available.";
                                        }
                                    ?>
                                </span>      
                                <span class="big text-light">
                                    <?php
                                        if ($employeeInfo) {
                                        echo htmlspecialchars($employeeInfo['role']);
                                        } else {
                                        echo "User information not available.";
                                        }
                                    ?>
                                </span>
                            </li>
                        </ul>          
                        <div class="sb-sidenav-menu-heading text-center text-muted border-top border-secondary mt-3">Employee Dashboard</div> 
                        <a class="nav-link text-light loading" href="../../employee/fieldworker/dashboard.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>          
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseTAD" aria-expanded="false" aria-controls="collapseTAD">
                            <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div>
                            Time and Attendance
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseTAD" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light loading " href="../../employee/fieldworker/timesheet.php">
                                    <i class="fas fa-clock me-2"> </i>Timesheet
                                </a>
                            </nav> 
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLM" aria-expanded="false" aria-controls="collapseLM">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                            Leave Management
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLM" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light loading" href="../../employee/fieldworker/leave_file.php">
                                    <i class="fas fa-file me-2"></i> File Leave
                                </a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePM" aria-expanded="false" aria-controls="collapsePM">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                            Performance Management
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapsePM" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light loading" href="../../employee/fieldworker/performance_rating.php">
                                    <i class="fas fa-bar-chart me-2"></i> Performance Rating
                                </a>
                                <a class="nav-link text-light loading" href="../../employee/fieldworker/evaluation.php">
                                    <i class="fas fa-users me-2"></i> Evaluation
                                </a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSR" aria-expanded="false" aria-controls="collapseSR">
                            <div class="sb-nav-link-icon"><i class="fas fa-award"></i></div>
                            Social Recognition
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseSR" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light loading" href="../../employee/fieldworker/awardee.php">
                                    <i class="fa fa-trophy me-2"></i> Awardee
                                </a>
                            </nav>
                        </div> 
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-black border-top border-1 border-secondary">
                    <div class="small text-light">Logged in as: <?php echo htmlspecialchars($employeeInfo['role']); ?></div>
                </div>
            </nav>
        </div>


        <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-transparent border-0">
                    <div class="modal-body d-flex flex-column align-items-center justify-content-center">
                        <!-- Bouncing coin spinner -->
                        <div class="coin-spinner"></div>
                        <div class="mt-3 text-light fw-bold">Please wait...</div>
                    </div>
                </div>
            </div>
        </div>
        