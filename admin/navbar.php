    <style>
        .collapse {
            transition: width 3s ease;
        }

        #searchInput.collapsing {
            width: 0;
        }

        #searchInput.collapse.show {
            width: 250px; /* Adjust the width as needed */
        }

        .search-bar {
            position: relative;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        #search-results {
            position: absolute;
            width: 100%;
            z-index: 1000;
            display: none; /* Hidden by default */
        }

        #search-results a {
            text-decoration: none;
        }

        .form-control:focus + #search-results {
            display: block; /* Show the results when typing */
        }
    </style>

    <nav class="sb-topnav navbar navbar-expand navbar-dark border-bottom border-1 border-secondary bg-dark">
        <a class="navbar-brand ps-3 text-muted" href="../admin/dashboard.php">Admin Portal</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars text-light"></i></button>  
        <div class="d-flex ms-auto me-0 me-md-3 my-2 my-md-0 align-items-center">
            <div class="d-none d-md-inline-block form-inline text-light me-3 p-2 rounded shadow-sm bg-light" id="currentTimeContainer">
                <span class="d-flex align-items-center fw-bold">
                    <span class="pe-2 text-dark">
                        <i class="fas fa-clock"></i> 
                        <span id="currentTime">00:00:00</span>
                    </span>
                    <button class="btn btn-outline-dark btn-sm ms-2" title="Calendar" type="button" onclick="toggleCalendar()">
                        <i class="fas fa-calendar-alt"></i>
                        <span id="currentDate">00/00/0000</span>
                    </button>
                </span>
            </div>
            <div class="dropdown search-container" style="position: relative;">
                <form class="d-none d-md-inline-block form-inline">
                    <div class="input-group">
                        <!-- Search Input -->
                        <input class="form-control collapse" id="searchInput" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" data-bs-toggle="dropdown" aria-expanded="false" />
                        <button class="btn btn-light text-dark" id="btnNavbarSearch" type="button" title="Search" data-bs-toggle="collapse" data-bs-target="#searchInput" aria-expanded="false" aria-controls="searchInput">
                            <i id="searchIcon" class="fas fa-search"></i> <!-- Initial Icon -->
                        </button>
                    </div>
                    <ul id="searchResults" class="dropdown-menu list-group bg-transparent border-none border-0"></ul>
                </form>
            </div>
        </div>
    </nav>

    <script>
        // GENERAL SEARCH
        const features = [
            { name: "Dashboard", link: "../admin/dashboard.php", path: "Admin Dashboard" },
            { name: "Attendance Scanner", link: "../admin/attendance.php", path: "Time and Attendance/Attendance Scanner" },
            { name: "Timesheet", link: "../admin/timesheet.php", path: "Time and Attendance/Timesheet" },
            { name: "Leave Request", link: "../admin/leave_request.php", path: "Leave Management/Leave Request" },
            { name: "File Leave", link: "../admin/leave_requests.php", path: "Leave Management/Leave Request" },
            { name: "Leave History", link: "../admin/leave_history.php", path: "Leave Management/Leave History" },
            { name: "Evaluation Ratings", link: "../admin/evaluation.php", path: "Performance Management/Evaluation" },
            { name: "View Your Rating", link: "../admin/social_recognition.php", path: "Social Recognition/View Your Rating" },
        ];

        document.getElementById('searchInput').addEventListener('input', function () {
            let input = this.value.toLowerCase();
            let results = '';

            if (input) {
                // Filter the features based on the search input
                const filteredFeatures = features.filter(feature => 
                    feature.name.toLowerCase().includes(input)
                );

                if (filteredFeatures.length > 0) {
                    // Generate the HTML for the filtered results
                    filteredFeatures.forEach(feature => {
                        results += `                   
                            <a href="${feature.link}" class="list-group-item list-group-item-action">
                                ${feature.name}
                                <br>
                                <small class="text-muted">${feature.path}</small>
                            </a>`;
                    });
                } else {
                    // If no matches found, show "No result found"
                    results = `<li class="list-group-item list-group-item-action">No result found</li>`;
                }
            }

            // Update the search results with the filtered features+
            document.getElementById('searchResults').innerHTML = results;
            
            if (!input) {
                document.getElementById('searchResults').innerHTML = ''; // Clears the dropdown if input is empty
            }
        });


        const searchInputElement = document.getElementById('searchInput');
        searchInputElement.addEventListener('hidden.bs.collapse', function () {
            searchInputElement.value = '';
            document.getElementById('searchResults').innerHTML = ''; 
        });
    </script>