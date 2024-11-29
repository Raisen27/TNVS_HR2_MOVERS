<?php
session_start();  // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Redirect the user to the login page if not logged in
    header('Location: login.php');
    exit();
}

// Check if the user is authenticated and their data is in session\

?>

<!DOCTYPE html>
<html>

<head>
    <title>Talent Management</title>
    <link rel="stylesheet" type="text/css" href="css/dashboardstyles.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>

    <?php include  'displayAuthenticatedUser.php';   ?>

    <div class="container">
        <div class="photo">
            <a href="archiveFolders.php" class="hidden">
                <img src="assets/logo.png"></a>
        </div>

        <div class="dashboardSection">
            <a href="dashboard.php" class="dashboard">
                <i class="fas fa-tachometer-alt icon"></i> DASHBOARD
            </a>
        </div>

        <div class="recruitmentSection">
            <a href="./Job_offer.php" class="recruitment">
                <i class="fas fa-user-tie icon"></i> RECRUITMENT AND ONBOARDING
            </a>
        </div>

        <div class="trainingSection">
            <a href="module3.php" class="trainAndDev">
                <i class="fas fa-book-open icon"></i> TRAINING MANAGEMENT
            </a>
        </div>

        <div class="performanceSection">
            <a href="worked-hours.php" class="performance">
                <i class="fas fa-chart-line icon"></i> PERFORMANCE MANAGEMENT
            </a>
        </div>

        <div class="Logout">
            <a href="logout.php" class="logoutbtn">
                <i class="fas fa-sign-out-alt icon"></i> LOG OUT
            </a>
        </div>
    </div>

    <div class="buttonContainer">
        <p class="d-text">Dashboard</p>
    </div>

    <div class="body-div">
        <div class="dashboard-container">

            <!-- Job Offers -->
            <div class="dash-item1">
                <h3>Job Offers</h3>
                <p></p>
            </div>
            <!-- Modal to show job offers -->
            <div id="jobOffersModal" class="jobOffersmodal">
                <div class="job-modal-content">
                    <span class="job-modal-close" id="closeJobModal">&times;</span>
                    <table class="job-table">
                        <thead>
                            <tr>
                                <th>Job Offers</th>
                            </tr>
                        </thead>
                        <tbody id="jobTableBody">
                            <!-- Fetched job offers data will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Total Applicants -->
            <div class="dash-item2">
                <h3>Total Applicants</h3>
                <p></p>
            </div>
            <!-- Modal to show total applicants -->
            <div id="totalApplicantModal" class="totalApplicantModal">
                <div class="applicant-modal-content">
                    <span class="applicant-modal-close" id="closeApplicantModal">&times;</span>
                    <table class="applicant-table">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Position</th>
                            </tr>
                        </thead>
                        <tbody id="applicantTableBody">
                            <!-- Fetched applicant name and job position data will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Total Hired Applicants -->
            <div class="dash-item3">
                <h3>Total Hired Applicants</h3>
                <p></p>
            </div>
            <!-- Modal to show hired applicants -->
            <div id="hiredModal" class="hiredmodal">
                <div class="hired-modal-content">
                    <span class="hired-modal-close" id="closeHiredModal">&times;</span>
                    <table class="hired-table">
                        <thead>
                            <tr>
                                <th>Applicant</th>

                            </tr>
                        </thead>
                        <tbody id="hiredTableBody">
                            <!-- Fetched hired applicant name and job position data will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Total Training Employees -->
            <div class="dash-item4">
                <h3>Employees in Training</h3>
                <p id="totalTrainingCount"></p>
            </div>
            <!-- Modal to show training -->
            <div id="trainingModal" class="trainingmodal">
                <div class="training-modal-content">
                    <span class="training-modal-close" id="closeTrainingModal">&times;</span>
                    <table class="training-table">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Training Program</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                            </tr>
                        </thead>
                        <tbody id="hiredTrainingBody">
                            <!-- Fetched training details will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Fetch job offers and applicant data immediately on page load
        document.addEventListener("DOMContentLoaded", () => {
            fetchDashboardData();
        });

        // Function to fetch job offers and applicants data from the server
        async function fetchDashboardData() {
            try {
                const response = await fetch('./Models/dashboard_fetching.php');
                if (!response.ok) throw new Error("Network response was not ok");

                const data = await response.json();

                // Job Offers: Update count and populate jobTableBody
                const jobOffers = data.jobOffers;
                document.querySelector('.dash-item1 p').innerText = `${jobOffers.length}`;

                const jobTableBody = document.getElementById('jobTableBody');
                jobTableBody.innerHTML = ""; // Clear previous entries
                jobOffers.forEach(job => {
                    const row = document.createElement('tr');
                    const cell = document.createElement('td');
                    cell.textContent = job; // Adjust this if job object has a specific property
                    row.appendChild(cell);
                    jobTableBody.appendChild(row);
                });

                // Applicants: Update count and populate applicantTableBody
                const applicants = data.applicants;
                document.querySelector('.dash-item2 p').innerText = `${applicants.length}`;

                const applicantTableBody = document.getElementById('applicantTableBody');
                applicantTableBody.innerHTML = ""; // Clear previous entries
                applicants.forEach(applicant => {
                    const row = document.createElement('tr');
                    const nameCell = document.createElement('td');
                    nameCell.textContent = applicant.name; // Assuming applicant object has 'name'
                    const positionCell = document.createElement('td');
                    positionCell.textContent = applicant.position; // Assuming 'applied_position' is the correct field
                    row.appendChild(nameCell);
                    row.appendChild(positionCell);
                    applicantTableBody.appendChild(row);
                });

                // Hired Applicants: Update count and populate hiredTableBody
                const hiredApplicants = data.hiredApplicants;
                document.querySelector('.dash-item3 p').innerText = `${hiredApplicants.length}`;

                const hiredTableBody = document.getElementById('hiredTableBody');
                hiredTableBody.innerHTML = ""; // Clear previous entries
                hiredApplicants.forEach(hired => {
                    const row = document.createElement('tr');
                    const nameCell = document.createElement('td');
                    nameCell.textContent = hired.name; // Assuming 'name' is the correct field
                    const positionCell = document.createElement('td');
                    hiredTableBody.appendChild(row);
                });

                // Training Employees: Update count and populate hiredTrainingBody
                const trainingEmployees = data.trainingEmployees;
                document.getElementById('totalTrainingCount').innerText = `${trainingEmployees.length}`;

                const hiredTrainingBody = document.getElementById('hiredTrainingBody');
                hiredTrainingBody.innerHTML = ""; // Clear previous entries
                trainingEmployees.forEach(training => {
                    const row = document.createElement('tr');
                    const nameCell = document.createElement('td');
                    nameCell.textContent = training.name; // Assuming 'name' is the correct field
                    const programCell = document.createElement('td');
                    programCell.textContent = training.training_program; // Assuming 'training_program' is the correct field
                    const startCell = document.createElement('td');
                    startCell.textContent = training.start_date; // Assuming 'start_date' is the correct field
                    const endCell = document.createElement('td');
                    endCell.textContent = training.end_date; // Assuming 'end_date' is the correct field
                    row.appendChild(nameCell);
                    row.appendChild(programCell);
                    row.appendChild(startCell);
                    row.appendChild(endCell);
                    hiredTrainingBody.appendChild(row);
                });

            } catch (error) {
                console.error("Error fetching dashboard data:", error);
            }
        }

        // Open job offers modal on click
        document.querySelector('.dash-item1').onclick = function() {
            document.getElementById('jobOffersModal').style.display = "block";
        };

        // Open applicants modal on click
        document.querySelector('.dash-item2').onclick = function() {
            document.getElementById('totalApplicantModal').style.display = "block";
        };

        // Open hired applicants modal on click
        document.querySelector('.dash-item3').onclick = function() {
            document.getElementById('hiredModal').style.display = "block";
        };

        // Open training employees modal on click
        document.querySelector('.dash-item4').onclick = function() {
            document.getElementById('trainingModal').style.display = "block";
        };

        // Close job offers modal on close button click
        document.getElementById('closeJobModal').onclick = function() {
            document.getElementById('jobOffersModal').style.display = "none";
        };

        // Close applicants modal on close button click
        document.getElementById('closeApplicantModal').onclick = function() {
            document.getElementById('totalApplicantModal').style.display = "none";
        };

        // Close hired applicants modal on close button click
        document.getElementById('closeHiredModal').onclick = function() {
            document.getElementById('hiredModal').style.display = "none";
        };

        // Close training employees modal on close button click
        document.getElementById('closeTrainingModal').onclick = function() {
            document.getElementById('trainingModal').style.display = "none";
        };

        // Close modals if clicked outside the content
        window.onclick = function(event) {
            if (event.target == document.getElementById('jobOffersModal')) {
                document.getElementById('jobOffersModal').style.display = "none";
            }
            if (event.target == document.getElementById('totalApplicantModal')) {
                document.getElementById('totalApplicantModal').style.display = "none";
            }
            if (event.target == document.getElementById('hiredModal')) {
                document.getElementById('hiredModal').style.display = "none";
            }
            if (event.target == document.getElementById('trainingModal')) {
                document.getElementById('trainingModal').style.display = "none";
            }
        };
    </script>



</body>

</html>