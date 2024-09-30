
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Tracker</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            background-color: #333;
        }
        .container {
            background-color: #444;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        .text-light {
            color: #fff;
        }
        .table {
            color: #fff;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #555;
        }
        .table-striped tbody tr:nth-of-type(even) {
            background-color: #666;
        }
        .form-control {
            background-color: #555;
            color: #fff;
            border: 1px solid #666;
        }
        .form-control:focus {
            background-color: #666;
            border: 1px solid #fff;
        }
        .btn-primary {
            background-color: #ffff00; /* Yellow */
            color: #000; /* Black */
            border: 1px solid #000; /* Black */
        }
        .btn-primary:hover {
            background-color: #ffff66; /* Light Yellow */
            color: #000; /* Black */
            border: 1px solid #000; /* Black */
        }
        .modal-content {
            background-color: #444;
        }
        .modal-header {
            background-color: #555;
            border-bottom: 1px solid #666;
        }
        .modal-footer {
            background-color: #555;
            border-top: 1px solid #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: #ffff00;">Leave Tracker</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="color: #ffff00;">Employee ID</th>
                    <th style="color: #ffff00;">Name</th>
                    <th style="color: #ffff00;">Role</th>
                    <th style="color: #ffff00;">Department</th>
                    <th style="color: #ffff00;">Remaining Leave</th>
                </tr>
            </thead>
            <tbody id="leave-table">
                <!-- leave data will be displayed here -->
            </tbody>
        </table>

        <script>
            let employees = [
                { id: 1, name: "John Doe", role: "Software Engineer", department: "IT", leaves: 10 },
                { id: 2, name: "Jane Doe", role: "Marketing Manager", department: "Marketing", leaves: 10 },
                // Add more employees here
            ];

            let leaveTable = document.getElementById("leave-table");

            employees.forEach((employee) => {
                let newRow = `
                    <tr>
                        <td>${employee.id}</td>
                        <td>${employee.name}</td>
                        <td>${employee.role}</td>
                        <td>${employee.department}</td>
                        <td>${employee.leaves} remaining</td>
                    </tr>
                `;
                leaveTable.innerHTML += newRow;
            });
        </script>
    </div>
</body>
</html>