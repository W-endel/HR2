<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Recognition</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .star {
            font-size: 2rem;
            color: #ccc;
            cursor: pointer;
            transition: color 0.3s;
        }
        .star.full {
            color: #f39c12;
        }
        .star:hover,
        .star.selected {
            color: #f39c12;
        }
        .employee-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
            padding: 15px;
            text-align: center;
            background-color: #fff;
        }
        .employee-card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ddd;
            margin-bottom: 10px;
        }
        .employee-card h4 {
            margin: 10px 0;
        }
        .employee-card .rating-display {
            margin-top: 10px;
        }
    </style>
</head>
<body class="bg-dark">
    <div class="container mt-5">
        <h2 class="mb-4 text-center text-light">Social Recognition</h2>

        <form id="employee-form" class="mb-4">
            <div class="form-group text-light">
                <label for="employee-name">Name:</label>
                <input type="text" id="employee-name" class="form-control" required>
            </div>
            <div class="form-group text-light">
                <label for="employee-image">Image:</label>
                <input type="file" id="employee-image" class="form-control-file" accept="image/*" required>
            </div>
            <div class="form-group text-light">
                <label for="employee-rating">Rating:</label>
                <div class="star-rating" id="star-rating-input">
                    <span class="star" data-value="1">&#9733;</span>
                    <span class="star" data-value="2">&#9733;</span>
                    <span class="star" data-value="3">&#9733;</span>
                    <span class="star" data-value="4">&#9733;</span>
                    <span class="star" data-value="5">&#9733;</span>
                </div>
                <input type="hidden" id="employee-rating" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Employee</button>
        </form>

        <div id="employee-container" class="row">
            <!-- Employee cards will be added here dynamically -->
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const employeeContainer = document.getElementById('employee-container');
            const employeeForm = document.getElementById('employee-form');
            const starRatingInput = document.getElementById('star-rating-input');
            const ratingHiddenInput = document.getElementById('employee-rating');

            // Function to create and display an employee card
            function createEmployeeCard(name, imageUrl, rating) {
                const card = document.createElement('div');
                card.className = 'col-md-4';
                card.innerHTML = `
                    <div class="employee-card">
                        <img src="${imageUrl}" alt="${name}">
                        <h4>${name}</h4>
                        <p class="rating">Rating: ${rating}</p>
                        <div class="star-rating" id="star-rating-${name.replace(/\s+/g, '')}">
                            <span class="star" data-value="1">&#9733;</span>
                            <span class="star" data-value="2">&#9733;</span>
                            <span class="star" data-value="3">&#9733;</span>
                            <span class="star" data-value="4">&#9733;</span>
                            <span class="star" data-value="5">&#9733;</span>
                        </div>
                        <div class="rating-display mt-2">
                            Your rating: <span id="rating-value-${name.replace(/\s+/g, '')}">0</span> stars
                        </div>
                    </div>
                `;
                employeeContainer.appendChild(card);

                // Function to update stars based on rating
                function updateStars(ratingValue) {
                    const stars = card.querySelectorAll('.star');
                    const ratingDisplay = card.querySelector('.rating-display span');
                    stars.forEach(star => {
                        star.classList.remove('selected');
                        const value = parseFloat(star.getAttribute('data-value'));
                        if (value <= ratingValue) {
                            star.classList.add('full');
                        }
                    });
                    ratingDisplay.textContent = ratingValue;
                }

                // Update stars based on initial rating
                updateStars(parseFloat(rating));

                // Add event listeners to the stars
                const stars = card.querySelectorAll('.star');
                stars.forEach(star => {
                    star.addEventListener('click', () => {
                        const value = parseFloat(star.getAttribute('data-value'));
                        updateStars(value);
                        card.querySelector('.rating-display span').textContent = value;
                    });
                });
            }

            // Handle form submission
            employeeForm.addEventListener('submit', (event) => {
                event.preventDefault();

                const name = document.getElementById('employee-name').value;
                const rating = ratingHiddenInput.value || '0'; // Default to '0' if no rating
                const imageInput = document.getElementById('employee-image');
                const imageFile = imageInput.files[0];
                const imageUrl = URL.createObjectURL(imageFile);

                createEmployeeCard(name, imageUrl, rating);

                // Reset form fields
                employeeForm.reset();
                // Reset star rating input
                ratingHiddenInput.value = '';
                // Reset star rating UI
                const stars = starRatingInput.querySelectorAll('.star');
                stars.forEach(star => star.classList.remove('selected', 'full'));
            });

            // Add event listeners to the star rating input
            const stars = starRatingInput.querySelectorAll('.star');
            stars.forEach(star => {
                star.addEventListener('click', () => {
                    const value = parseFloat(star.getAttribute('data-value'));
                    stars.forEach(s => {
                        const sValue = parseFloat(s.getAttribute('data-value'));
                        if (sValue <= value) {
                            s.classList.add('selected');
                        } else {
                            s.classList.remove('selected');
                        }
                    });
                    ratingHiddenInput.value = value;
                });
            });
        });
    </script>
</body>
</html>
