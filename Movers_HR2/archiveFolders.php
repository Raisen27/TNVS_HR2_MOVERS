<?php
session_start();  // Start the session

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

include 'Models/config.php'; // Database connection file
$conn = new ConnectionDb();

// Handle unarchiving request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !empty($_POST['id'])) {
    $id = mysqli_real_escape_string($conn->DbConnection(), $_POST['id']);
    $table = isset($_POST['table']) ? $_POST['table'] : '';  // Get table type (screening, onboarding)

    // Set the Archive Status back to ACTIVE for the corresponding table
    if ($table === 'screening') {
        $query = "UPDATE screening_selection SET archive_status = '!ACTIVE' WHERE id = '$id'";
    } elseif ($table === 'onboarding') {
        $query = "UPDATE onboarding_status SET Archive_status = '!ACTIVE' WHERE id = '$id'";
    } elseif ($table === 'trainingperformance') {
        $query = "UPDATE training_performance SET archive_status = '!ACTIVE' WHERE id = '$id'";
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid table specified.']);
        exit();
    }

    if ($conn->DbConnection()->query($query)) {
        echo json_encode(['success' => true, 'message' => 'Record updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update the record.']);
    }
    $conn->DbConnection()->close();
    exit(); // Stop further processing
}
?>




<!DOCTYPE html>
<html>

<head>
    <title>Archive Data</title>
    <link rel="stylesheet" type="text/css" href="css/archive.css">
    <!-- AJAX CDN -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


</head>

<body>

    <div class="header">
        <span>Archive data</span>
        <img src="assets/logo.png" alt="Logo">
    </div>


    <div class="body-div">
        <div class="archive-container">

            <!-- screening and selection -->
            <div class="dash-item1">
                <h3>Screening And Selection</h3>
                <p></p>
            </div>
            <!-- Modal to show the archive applicant -->
            <div id="screeningModal" class="screeningmodal">
                <div class="screening-modal-content">
                    <span class="screening-modal-close" id="closeScreeningModal">&times;</span>
                    <table class="screening-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Applied Position</th>
                                <th>Age</th>
                                <th>Email</th>
                                <th>Resume</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="screeningBody">
                            <?php
                            // Include database connection


                            // Query to fetch applicants with ACTIVE status
                            $query = "SELECT * FROM screening_selection WHERE archive_status = 'ACTIVE'";
                            $result = mysqli_query($conn->DbConnection(), $query);

                            // Check if there are any records
                            if ($result && mysqli_num_rows($result) > 0) {
                                // Loop through the results and display them in table rows
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>
                                <td>" . htmlspecialchars($row['name']) . "</td>
                                <td>" . htmlspecialchars($row['applied_position']) . "</td>
                                <td>" . htmlspecialchars($row['age']) . "</td>
                                <td>" . htmlspecialchars($row['email']) . "</td>
                                <td>
                                    <button class='viewPdf' id='btn_view' value='" . htmlspecialchars($row['document_path']) . "' 
                                    style='border: solid #3d52a0; padding: 5px; text-decoration: none; background-color: #3d52a0; color: white;'>Open</button>
                                </td>
                                <td>" . htmlspecialchars($row['Status']) . "</td>
                                <td>
                                    <button class='btn_remove_archive' data-id='" . htmlspecialchars($row['id']) . "' data-table='screening' 
            style='border: solid red; padding: 5px; background-color: red; color: white;'>Remove Archive</button>
                                </td>
                              </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7'>No records found.</td></tr>";
                            }

                            // Close the database connection
                            mysqli_close($conn->DbConnection());
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>



            <!-- onboarding status -->
            <div class="dash-item2">
                <h3>Onboarding Status</h3>
                <p></p>
            </div>
            <!-- Modal to show onboarding status archive -->
            <div id="onboardingModal" class="onboardingModal">
                <div class="onboarding-modal-content">
                    <span class="onboarding-modal-close" id="closeOnboardingModal">&times;</span>
                    <table class="onboarding-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Interview Status</th>
                                <th>Application Status</th>
                                <th>Onboarding Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="onboardingBody">
                            <?php
                            // Loop through the results and create table rows

                            // Fetch onboarding data
                            $sqlOnboarding = "SELECT * FROM onboarding_status WHERE Archive_status = 'ACTIVE'";
                            $resultOnboarding = $conn->DbConnection()->query($sqlOnboarding);

                            if (!$resultOnboarding) {
                                die("Error fetching onboarding data: " . $conn->DbConnection()->error);
                            }
                            if ($resultOnboarding->num_rows > 0) {
                                while ($row = $resultOnboarding->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['interview_status']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['application_status']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['onboarding_status']) . "</td>";
                                    echo "<td><button class='remove-archive-btn' data-id='" . htmlspecialchars($row['id']) . "' data-table='onboarding'>Remove Archive</button></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No active onboarding records found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Training Performance-->
            <div class="dash-item3">
                <h3>Training Performance</h3>
                <p></p>
            </div>
            <!-- Modal to show Training Performance -->
            <div id="performanceModal" class="performancemodal">
                <div class="performance-modal-content">
                    <span class="performance-modal-close" id="closePerformanceModal">&times;</span>
                    <table class="performance-table">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Training Program</th>
                                <th>Evaluator</th>
                                <th>Development</th>
                                <th>Date Given</th>
                                <th>Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="performanceBody">
                            <?php
                            // Include the database connection

                            // Query to fetch records from the training_performance table
                            $query = "SELECT * FROM training_performance WHERE archive_status = 'ACTIVE'";
                            $result = mysqli_query($conn->DbConnection(), $query);

                            // Check if there are any records
                            if ($result && mysqli_num_rows($result) > 0) {
                                // Loop through the results and display them in table rows
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['employee_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['training_program']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['evaluator']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['development']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['date_given']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['remarks']) . "</td>";
                                    echo "<td>
                    <button class='btn_remove_archive' data-id='" . htmlspecialchars($row['id']) . "' data-table='trainingperformance' 
            style='border: solid red; padding: 5px; background-color: red; color: white;'>Remove Archive</button>
                  </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7'>No training performance records found.</td></tr>";
                            }

                            // Close the database connection
                            $conn->DbConnection()->close();
                            ?>
                        </tbody>

                    </table>
                </div>
            </div>

        </div>
    </div>


    <script>
        // Fetch screening and onboarding data immediately on page load
        document.addEventListener("DOMContentLoaded", () => {
            fetchArchiveData();
        });

        // Function to fetch screening and onboarding and training performance data from the server
        async function fetchArchiveData() {
            try {
                const response = await fetch('./Models/archive_fetching.php');
                if (!response.ok) throw new Error("Network response was not ok");

                const contentType = response.headers.get("content-type");
                if (contentType && contentType.includes("archive/json")) {
                    const data = await response.json();
                    // Continue with the JSON handling code
                } else {
                    throw new Error("Expected JSON but received something else");
                }

                // screening: Update count and populate screeningBody
                const screening = data.screening;
                document.getElementById('screeningCount').innerText = `${screening.length}`;

                const screeningBody = document.getElementById('screeningBody');
                screeningBody.innerHTML = ""; // Clear previous entries
                screening.forEach(screening => {
                    const row = document.createElement('tr');
                    const nameCell = document.createElement('td');
                    nameCell.textContent = screening.name; // Assuming 'name' is the correct field
                    const appliedCell = document.createElement('td');
                    appliedCell.textContent = screening.applied_position; // Assuming 'applied_position' is the correct field
                    const ageCell = document.createElement('td');
                    ageCell.textContent = screening.age; // Assuming 'age' is the correct field
                    const emailCell = document.createElement('td');
                    emailCell.textContent = screening.email; // Assuming 'email' is the correct field
                    const resumeCell = document.createElement('td');
                    resumeCell.textContent = screening.resume; // Assuming 'resume' is the correct field
                    const statusCell = document.createElement('td');
                    statusCell.textContent = screening.status; // Assuming 'status' is the correct field
                    row.appendChild(nameCell);
                    row.appendChild(appliedCell);
                    row.appendChild(ageCell);
                    row.appendChild(emailCell);
                    row.appendChild(resumeCell);
                    row.appendChild(statusCell);
                    screeningBody.appendChild(row);
                });

                // onboarding: Update count and populate onboardingBody
                const onboarding = data.onboarding;
                document.getElementById('onboardingCount').innerText = `${onboarding.length}`;

                const onboardingBody = document.getElementById('onboardingBody');
                onboardingBody.innerHTML = ""; // Clear previous entries
                onboarding.forEach(onboarding => {
                    const row = document.createElement('tr');
                    const nameCell = document.createElement('td');
                    nameCell.textContent = onboarding.name; // Assuming 'name' is the correct field
                    const interviewCell = document.createElement('td');
                    interviewCell.textContent = onboarding.interview_status; // Assuming 'interview_status' is the correct field
                    const applicationCell = document.createElement('td');
                    applicationCell.textContent = onboarding.application_status; // Assuming 'application_status' is the correct field
                    const onboardingsCell = document.createElement('td');
                    onboardingsCell.textContent = onboarding.onboarding_status; // Assuming 'onboarding_status' is the correct field

                    row.appendChild(nameCell);
                    row.appendChild(interviewCell);
                    row.appendChild(applicationCell);
                    row.appendChild(onboardingsCell);
                    onboardingBody.appendChild(row);
                });

                // performance: Update count and populate performanceBody
                const performance = data.performance;
                document.getElementById('performanceCount').innerText = `${performance.length}`;

                const performanceBody = document.getElementById('performanceBody');
                performanceBody.innerHTML = ""; // Clear previous entries
                performance.forEach(performance => {
                    const row = document.createElement('tr');
                    const nameCell = document.createElement('td');
                    nameCell.textContent = performance.employee_name; // Assuming 'name' is the correct field
                    const programCell = document.createElement('td');
                    programCell.textContent = performance.training_program; // Assuming 'training_program' is the correct field
                    const evaluatorCell = document.createElement('td');
                    evaluatorCell.textContent = performance.evaluator; // Assuming 'evaluator' is the correct field
                    const developmentCell = document.createElement('td');
                    developmentCell.textContent = performance.development; // Assuming 'development' is the correct field
                    const dateCell = document.createElement('td');
                    dateCell.textContent = performance.date_given; // Assuming 'date_given' is the correct field
                    const remarksCell = document.createElement('td');
                    remarksCell.textContent = performance.remarks; // Assuming 'remarks' is the correct field
                    row.appendChild(nameCell);
                    row.appendChild(programCell);
                    row.appendChild(evaluatorCell);
                    row.appendChild(developmentCell);
                    row.appendChild(date_givenCell);
                    row.appendChild(remarksCell);
                    performancegBody.appendChild(row);
                });

            } catch (error) {
                console.error("Error fetching archive data:", error);
            }

        }


        // Open screening and selection modal on click
        document.querySelector('.dash-item1').onclick = function() {
            document.getElementById('screeningModal').style.display = "block";
        };

        // Open onboarding modal on click
        document.querySelector('.dash-item2').onclick = function() {
            document.getElementById('onboardingModal').style.display = "block";
        };

        // Open training performance modal on click
        document.querySelector('.dash-item3').onclick = function() {
            document.getElementById('performanceModal').style.display = "block";
        };

        // Close screening and selection modal on close button click
        document.getElementById('closeScreeningModal').onclick = function() {
            document.getElementById('screeningModal').style.display = "none";
        };

        // Close onboarding modal on close button click
        document.getElementById('closeOnboardingModal').onclick = function() {
            document.getElementById('onboardingModal').style.display = "none";
        };

        // Close training performance modal on close button click
        document.getElementById('closePerformanceModal').onclick = function() {
            document.getElementById('performanceModal').style.display = "none";
        };


        // Close modals if clicked outside the content
        window.onclick = function(event) {
            if (event.target == document.getElementById('screeningModal')) {
                document.getElementById('screeningModal').style.display = "none";
            }
            if (event.target == document.getElementById('onboardingModal')) {
                document.getElementById('onboardingModal').style.display = "none";
            }
            if (event.target == document.getElementById('performanceModal')) {
                document.getElementById('performanceModal').style.display = "none";
            }
        };

        $(document).ready(function() {
            $(".btn_remove_archive, .remove-archive-btn").click(function() {
                var id = $(this).data("id");
                var table = $(this).data("table");

                if (confirm("Are you sure you want to remove the archive for this record?")) {
                    $.ajax({
                        url: 'archiveFolders.php',
                        type: 'POST',
                        data: {
                            id: id,
                            table: table
                        },
                        success: function(response) {
                            var result = JSON.parse(response);
                            if (result.success) {
                                alert(result.message);
                                location.reload();
                            } else {
                                alert("Error: " + result.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            alert("An error occurred: " + error);
                        }
                    });
                }
            });
        });
    </script>

</body>

</html>