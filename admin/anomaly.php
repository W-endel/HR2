<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Review Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="../css/styles.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- TensorFlow.js and Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #3a86ff;
            --secondary-color: #8338ec;
            --success-color: #38b000;
            --warning-color: #ffbe0b;
            --danger-color: #ff006e;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --bg-dark: rgba(33, 37, 41) !important;
            --bg-black: rgba(16, 17, 18) !important;
            --card-bg: rgba(33, 37, 41) !important;
        }

        body {
            background-color: rgba(16, 17, 18) !important;
        }

        .loading-spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .chart-container {
            position: relative;
            margin: auto;
            height: 400px;
            width: 100%;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
        }

        .dashboard-header {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            transition: transform 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .nav-tabs {
            border-bottom: none;
            margin-bottom: 20px;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 30px;
            margin-right: 10px;
        }

        .nav-tabs .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .tab-content {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }

        .anomaly-card {
            border-left: 4px solid var(--danger-color);
            transition: transform 0.3s ease;
        }

        .anomaly-card:hover {
            transform: translateY(-5px);
        }

        .badge-anomaly {
            background-color: var(--danger-color);
            color: white;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 30px;
        }

        .no-anomalies {
            text-align: center;
            padding: 40px 0;
        }

        .no-anomalies i {
            font-size: 4rem;
            color: var(--success-color);
            margin-bottom: 20px;
        }

        .tooltip-inner {
            max-width: 300px;
            padding: 10px 15px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-bar-chart-line-fill me-2"></i>
                Performance Review Dashboard
            </a>
            <div class="d-flex text-white">
                <small>Last updated: <span id="lastUpdated"></span></small>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Dashboard Summary -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card bg-dark text-light">
                    <div class="stat-icon text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="stat-value" id="totalEmployees">-</div>
                    <div class="stat-label">Total Employees</div>
                    <small class="text-muted">Analyzed in this report</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card bg-dark text-light">
                    <div class="stat-icon text-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="stat-value" id="anomalyCount">-</div>
                    <div class="stat-label">Anomalies Detected</div>
                    <small class="text-muted">Employees with unusual patterns</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card bg-dark text-light">
                    <div class="stat-icon text-success">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="stat-value" id="normalCount">-</div>
                    <div class="stat-label">Normal Performance</div>
                    <small class="text-muted">Employees with expected patterns</small>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="visualization-tab" data-bs-toggle="tab" data-bs-target="#visualization" type="button" role="tab" aria-controls="visualization" aria-selected="true">
                    <i class="bi bi-graph-up me-2"></i>Visualization
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="anomalies-tab" data-bs-toggle="tab" data-bs-target="#anomalies" type="button" role="tab" aria-controls="anomalies" aria-selected="false">
                    <i class="bi bi-exclamation-circle me-2"></i>Anomalies
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content bg-dark" id="myTabContent">
            <!-- Visualization Tab -->
            <div class="tab-pane fade show active" id="visualization" role="tabpanel" aria-labelledby="visualization-tab">
                <h4 class="mb-3 text-light">Performance Anomaly Detection</h4>
                <p class="text-muted mb-4">Reconstruction error for each employee. Higher values indicate potential anomalies.</p>
                <div id="loadingSpinner" class="loading-spinner"></div>
                <div class="chart-container">
                    <canvas id="anomalyChart"></canvas>
                </div>
            </div>

            <!-- Anomalies Tab -->
            <div class="tab-pane fade" id="anomalies" role="tabpanel" aria-labelledby="anomalies-tab">
                <h4 class="mb-3 text-light">Detected Anomalies</h4>
                <p class="text-muted mb-4">Employees with unusual performance patterns that may require attention.</p>
                <div id="anomaliesLoading" class="loading-spinner"></div>
                <div id="output" class="row g-4"></div>
                <div id="noAnomalies" class="no-anomalies d-none">
                    <i class="bi bi-check-circle-fill"></i>
                    <h4>No Anomalies Detected</h4>
                    <p class="text-muted">All employees are showing expected performance patterns.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Set last updated date
        document.getElementById('lastUpdated').textContent = new Date().toLocaleDateString();

        // Fetch data from the backend
        async function fetchData() {
            const response = await fetch('/HR2/db/ml.php');
            const data = await response.json();
            return data;
        }

        // Normalize data
        function normalizeData(data) {
            const normalizedData = [];
            for (const [employee_id, records] of Object.entries(data)) {
                const { attendance, admin_evaluation, ptp_evaluation } = records;

                // Convert attendance status to numerical values
                const attendanceFeatures = [
                    attendance['present'] || 0,
                    attendance['absent'] || 0,
                    attendance['late'] || 0,
                    attendance['undertime'] || 0,
                    attendance['half-day'] || 0,
                    attendance['overtime'] || 0
                ];

                // Combine admin and PTP evaluation scores
                const performanceFeatures = [
                    admin_evaluation?.avg_quality || 0,
                    admin_evaluation?.avg_teamwork || 0,
                    admin_evaluation?.avg_communication || 0,
                    admin_evaluation?.avg_initiative || 0,
                    admin_evaluation?.avg_punctuality || 0,
                    ptp_evaluation?.avg_quality || 0,
                    ptp_evaluation?.avg_teamwork || 0,
                    ptp_evaluation?.avg_communication || 0,
                    ptp_evaluation?.avg_initiative || 0,
                    ptp_evaluation?.avg_punctuality || 0
                ];

                // Combine all features
                const features = [...attendanceFeatures, ...performanceFeatures];
                normalizedData.push({ employee_id, features });
            }

            return normalizedData;
        }

        // Build and train an autoencoder model
        async function trainModel(data) {
            const features = data.map(d => d.features);
            const tensorData = tf.tensor2d(features);

            // Normalize the data
            const min = tensorData.min();
            const max = tensorData.max();
            const normalizedData = tensorData.sub(min).div(max.sub(min));

            // Define the autoencoder model
            const model = tf.sequential();
            model.add(tf.layers.dense({ units: 8, activation: 'relu', inputShape: [features[0].length] }));
            model.add(tf.layers.dense({ units: 4, activation: 'relu' }));
            model.add(tf.layers.dense({ units: 8, activation: 'relu' }));
            model.add(tf.layers.dense({ units: features[0].length, activation: 'sigmoid' }));

            // Compile the model
            model.compile({ optimizer: 'adam', loss: 'meanSquaredError' });

            // Train the model
            await model.fit(normalizedData, normalizedData, {
                epochs: 50,
                batchSize: 32,
                shuffle: true
            });

            return { model, min, max };
        }

        // Detect anomalies using the trained model (now returns percentages)
        function detectAnomalies(data, model, min, max) {
            const anomalies = [];
            const threshold = 20; // 20% threshold (0.20 * 100)

            for (const { employee_id, features } of data) {
                const inputTensor = tf.tensor2d([features]);
                const normalizedInput = inputTensor.sub(min).div(max.sub(min));
                const outputTensor = model.predict(normalizedInput);
                const reconstructionError = tf.losses.meanSquaredError(normalizedInput, outputTensor).dataSync()[0] * 100; // Convert to percentage

                if (reconstructionError > threshold) {
                    anomalies.push({ 
                        employee_id, 
                        reconstructionError,
                        rawError: reconstructionError / 100 // Store original for calculations if needed
                    });
                }
            }

            return anomalies;
        }

        // Create a bar chart using Chart.js with percentages
        function createChart(employeeIds, reconstructionErrors, anomalies) {
            const ctx = document.getElementById('anomalyChart').getContext('2d');
            const anomalyIndices = new Set(anomalies.map(a => employeeIds.indexOf(a.employee_id)));
            const threshold = 20; // 20% threshold
            
            // Convert errors to percentages
            const reconstructionErrorsPercent = reconstructionErrors.map(error => error * 100);

            // Create a gradient for the bars
            const normalGradient = ctx.createLinearGradient(0, 0, 0, 400);
            normalGradient.addColorStop(0, 'rgba(58, 134, 255, 0.8)');
            normalGradient.addColorStop(1, 'rgba(58, 134, 255, 0.2)');

            const anomalyGradient = ctx.createLinearGradient(0, 0, 0, 400);
            anomalyGradient.addColorStop(0, 'rgba(255, 0, 110, 0.8)');
            anomalyGradient.addColorStop(1, 'rgba(255, 0, 110, 0.2)');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: employeeIds,
                    datasets: [
                        {
                            label: 'Reconstruction Error (%)',
                            data: reconstructionErrorsPercent,
                            backgroundColor: reconstructionErrorsPercent.map((error, index) =>
                                anomalyIndices.has(index) ? anomalyGradient : normalGradient
                            ),
                            borderColor: reconstructionErrorsPercent.map((error, index) =>
                                anomalyIndices.has(index) ? 'rgba(255, 0, 110, 1)' : 'rgba(58, 134, 255, 1)'
                            ),
                            borderWidth: 1
                        },
                        {
                            label: 'Threshold (20%)',
                            data: Array(employeeIds.length).fill(threshold),
                            type: 'line',
                            borderColor: 'rgba(255, 190, 11, 0.8)',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            pointRadius: 0,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Reconstruction Error (%)',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Employee ID',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 15,
                            callbacks: {
                                title: (context) => {
                                    const employeeId = employeeIds[context[0].dataIndex];
                                    return `Employee ID: ${employeeId}`;
                                },
                                label: (context) => {
                                    if (context.dataset.label === 'Threshold (20%)') {
                                        return `Anomaly Threshold: ${threshold}%`;
                                    }
                                    
                                    const error = context.raw;
                                    const isAnomaly = error > threshold;
                                    return [
                                        `Reconstruction Error: ${error.toFixed(1)}%`,
                                        `Status: ${isAnomaly ? 'Anomaly Detected' : 'Normal'}`
                                    ];
                                }
                            }
                        }
                    }
                }
            });
        }

        // Display anomalies with percentages
        function displayAnomalies(anomalies) {
            const output = document.getElementById('output');
            const noAnomalies = document.getElementById('noAnomalies');
            
            if (anomalies.length === 0) {
                output.innerHTML = '';
                noAnomalies.classList.remove('d-none');
                return;
            }
            
            noAnomalies.classList.add('d-none');
            output.innerHTML = '';
            
            anomalies.forEach(anomaly => {
                // Calculate severity based on percentage thresholds
                let severityClass = 'bg-warning';
                let severityText = 'Medium';
                
                if (anomaly.reconstructionError > 30) { // >30% is high severity
                    severityClass = 'bg-danger';
                    severityText = 'High';
                } else if (anomaly.reconstructionError < 25) { // <25% is low severity
                    severityClass = 'bg-warning text-dark';
                    severityText = 'Low';
                }
                
                output.innerHTML += `
                    <div class="col-md-6">
                        <div class="card anomaly-card shadow-sm mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Employee ID: ${anomaly.employee_id}</h5>
                                    <span class="badge badge-anomaly">Anomaly</span>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1">Reconstruction Error:</p>
                                        <p class="fw-bold fs-4">${anomaly.reconstructionError.toFixed(1)}%</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1">Severity:</p>
                                        <span class="badge ${severityClass} px-3 py-2">${severityText}</span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <p class="text-muted mb-1">Recommendation:</p>
                                    <p>Review employee performance and attendance records for potential issues.</p>
                                </div>
                                <div class="progress mt-3" style="height: 8px;">
                                    <div class="progress-bar bg-danger" role="progressbar" 
                                        style="width: ${Math.min(anomaly.reconstructionError, 100)}%;" 
                                        aria-valuenow="${Math.min(anomaly.reconstructionError, 100)}" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        // Main function
        async function main() {
            const spinner = document.getElementById('loadingSpinner');
            const anomaliesLoading = document.getElementById('anomaliesLoading');
            spinner.style.display = 'block';
            anomaliesLoading.style.display = 'block';

            try {
                const rawData = await fetchData();
                const normalizedData = normalizeData(rawData);
                const { model, min, max } = await trainModel(normalizedData);
                const anomalies = detectAnomalies(normalizedData, model, min, max);

                // Update dashboard stats
                document.getElementById('totalEmployees').textContent = normalizedData.length;
                document.getElementById('anomalyCount').textContent = anomalies.length;
                document.getElementById('normalCount').textContent = normalizedData.length - anomalies.length;

                // Prepare data for the chart - convert to percentages
                const employeeIds = normalizedData.map(d => d.employee_id);
                const reconstructionErrors = normalizedData.map(d => {
                    const inputTensor = tf.tensor2d([d.features]);
                    const normalizedInput = inputTensor.sub(min).div(max.sub(min));
                    const outputTensor = model.predict(normalizedInput);
                    return tf.losses.meanSquaredError(normalizedInput, outputTensor).dataSync()[0];
                });

                // Create the chart with percentages
                createChart(employeeIds, reconstructionErrors, anomalies);

                // Display anomalies
                displayAnomalies(anomalies);
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('visualization').innerHTML += `
                    <div class="alert alert-danger mt-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Error loading data. Please try again later.
                    </div>
                `;
            } finally {
                spinner.style.display = 'none';
                anomaliesLoading.style.display = 'none';
            }
        }

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            main();
        });
    </script>
</body>
</html>