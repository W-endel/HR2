// Sample data
let attendanceRecords = [
    { id: 1, name: 'John Doe', date: '2024-04-20', status: 'Present' },
    { id: 2, name: 'Jane Smith', date: '2024-04-20', status: 'Absent' },
    { id: 3, name: 'Alice Johnson', date: '2024-04-20', status: 'Late' },
];

// Function to render attendance records
function renderRecords() {
    const tableBody = document.getElementById('record-table-body');
    tableBody.innerHTML = '';

    attendanceRecords.forEach((record, index) => {
        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td>${index + 1}</td>
            <td>${record.name}</td>
            <td>${record.date}</td>
            <td>${record.status}</td>
            <td class="actions">
                <button onclick="deleteRecord(${record.id})">Delete</button>
            </td>
        `;

        tableBody.appendChild(tr);
    });
}

// Function to add a new record
function addRecord(e) {
    e.preventDefault();

    const nameInput = document.getElementById('name');
    const dateInput = document.getElementById('date');
    const statusInput = document.getElementById('status');

    const name = nameInput.value.trim();
    const date = dateInput.value;
    const status = statusInput.value;

    if (name === '' || date === '' || status === '') {
        alert('Please fill in all fields.');
        return;
    }

    const newRecord = {
        id: attendanceRecords.length ? attendanceRecords[attendanceRecords.length - 1].id + 1 : 1,
        name,
        date,
        status
    };

    attendanceRecords.push(newRecord);
    renderRecords();

    // Clear form
    nameInput.value = '';
    dateInput.value = '';
    statusInput.value = '';
}

// Function to delete a record
function deleteRecord(id) {
    if (confirm('Are you sure you want to delete this record?')) {
        attendanceRecords = attendanceRecords.filter(record => record.id !== id);
        renderRecords();
    }
}

// Event listener for form submission
document.getElementById('attendance-form').addEventListener('submit', addRecord);

// Initial render
renderRecords();
