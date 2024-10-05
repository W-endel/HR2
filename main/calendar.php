<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Non-Working Days</title>
</head>
<body>
    <div class="container mt-5">
        <h2>Set Non-Working Days</h2>
        <form id="nonWorkingDayForm">
            <div class="form-group">
                <label for="date">Date:</label>
                <input type="date" class="form-control" id="date" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <input type="text" class="form-control" id="description" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Non-Working Day</button>
        </form>

        <hr>

        <h3>Existing Non-Working Days</h3>
        <table class="table" id="nonWorkingDaysTable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Existing non-working days will be populated here -->
            </tbody>
        </table>
    </div>

    <script>
        document.getElementById('nonWorkingDayForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const date = document.getElementById('date').value;
            const description = document.getElementById('description').value;

            // AJAX call to save the non-working day
            fetch('nowork_days.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ date, description }),
            })
            .then(response => response.json())
            .then(data => {
                console.log(data); // Log the response for debugging
                if (data.status === 'success') {
                    alert('Non-working day added successfully!');
                    // Optionally refresh the table or clear the form
                    document.getElementById('nonWorkingDayForm').reset();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred.');
            });
        });
    </script>
</body>
</html>
