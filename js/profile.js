//EDIT INFORMATION
let originalValues = []; // Declare this outside the event listener

        document.getElementById('editButton').addEventListener('click', function() {
            const inputs = document.querySelectorAll('#infoForm input, #infoForm textarea');
            const saveButton = document.querySelector('#infoForm button[type="submit"]');

            // Check if we are entering edit mode
            if (saveButton.classList.contains('d-none')) {
                // Store the original values
                originalValues = Array.from(inputs).map(input => input.value);
                
                // Enable editing
                inputs.forEach(input => {
                    input.readOnly = false; // Make inputs editable
                });
                
                saveButton.classList.remove('d-none'); // Show save button
                this.textContent = 'Cancel'; // Change button text to 'Cancel'
            } else {
                // If canceling, reset to original values
                inputs.forEach((input, index) => {
                    input.value = originalValues[index]; // Restore original value
                    input.readOnly = true; // Keep inputs readonly
                });
                
                saveButton.classList.add('d-none'); // Hide save button
                this.textContent = 'Update Information'; // Change button text back
            }
        });

        


        