<?php
session_start(); // Start the session

include 'Models/dbconnect.php';


// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Redirect the user to the login page if not logged in
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debugging: Log request details
    error_log("POST Request Received: " . json_encode($_POST));

    $action = $_POST['action'] ?? null;
    $name = $_POST['name'] ?? null;
    $id = $_POST['id'] ?? null;

    if (!$action || !$name || !$id) {
        error_log("Invalid POST data. Missing action, name, or id.");
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
        exit();
    }

    if ($action == 'accept') {
        $insert_sql = "INSERT INTO onboarding_status (name, interview_status, application_status, onboarding_status, Archive_status) VALUES (?, 'INITIAL INTERVIEW', 'ON PROCESS', 'PRE-BOARDING', '!Active')";
        $update_sql = "UPDATE screening_selection SET Status = 'ONGOING' WHERE id = ?";

        // Prepare statements
        $stmt = $conn->prepare($insert_sql);
        $update_stmt = $conn->prepare($update_sql);

        if ($stmt && $update_stmt) {
            $stmt->bind_param("s", $name);
            $update_stmt->bind_param("i", $id);

            if ($stmt->execute() && $update_stmt->execute()) {
                error_log("Applicant accepted successfully: Name = $name, ID = $id");
                echo json_encode(['success' => true, 'message' => 'Applicant has been accepted.']);
            } else {
                error_log("Database Error: " . $conn->error);
                echo json_encode(['success' => false, 'message' => 'Database operation failed.']);
            }
        } else {
            error_log("Error preparing statements: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Database operation failed.']);
        }
    } elseif ($action == 'reject') {
        $update_sql = "UPDATE screening_selection SET Status='REJECTED' WHERE id=?";
        $update_stmt = $conn->prepare($update_sql);

        if ($update_stmt) {
            $update_stmt->bind_param("i", $id);

            if ($update_stmt->execute()) {
                error_log("Applicant rejected successfully: ID = $id");
                echo json_encode(['success' => true, 'message' => 'Applicant has been rejected.']);
            } else {
                error_log("Database Error: " . $conn->error);
                echo json_encode(['success' => false, 'message' => 'Database operation failed.']);
            }
        } else {
            error_log("Error preparing reject statement: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Database operation failed.']);
        }
    }
}

// Close the connection
$conn->close();
?>


<!DOCTYPE html>
<html>

<head>
    <title>Talent Management</title>
    <link rel="stylesheet" type="text/css" href="css/screeningstyles.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<style>
    /* Modal styles */
    .modal {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        z-index: 1;
        /* Sit on top */
        left: 0;
        top: 0;
        width: 100%;
        /* Full width */
        height: 100%;
        /* Full height */
        overflow: auto;
        /* Enable scroll if needed */
        background-color: rgb(0, 0, 0);
        /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4);
        /* Black w/ opacity */
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 50%;
        /* Could be more or less, depending on screen size */
        border-radius: 10px;
        text-align: center;
    }

    .modal-content input {
        padding: 10px;
        outline: none;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
</style>

<body>

    <?php include 'displayAuthenticatedUser.php'; ?>

    <div class="container">
        <div class="photo">
            <img src="assets/logo.png">
        </div>

        <div class="dashboardSection">
            <a href="dashboard.php" class="dashboard"><i class="fas fa-tachometer-alt icon"></i>Dashboard</a>
        </div>

        <div class="recruitmentSection">
            <a href="./Job_offer.php" class="m-recruitment"><i class="fas fa-user-plus icon"></i>RECRUITMENT AND ONBOARDING</a>
        </div>

        <div class="trainingSection">
            <a href="module3.php" class="trainAndDev"><i class="fas fa-chalkboard-teacher icon"></i>TRAINING MANAGEMENT</a>
        </div>

        <div class="performanceSection">
            <a href="worked-hours.php" class="performance"><i class="fas fa-chart-line icon"></i>PERFORMANCE MANAGEMENT</a>
        </div>

        <div class="Logout">
            <a href="logout.php" class="logoutbtn">
                <i class="fas fa-sign-out-alt icon"></i> LOG OUT
            </a>
        </div>
    </div>

    <div class="buttonContainer">
        <a href="Job_offer.php" class="btn1"><i class="fas fa-briefcase icon"></i> Job Offers</a>
        <a href="screening-selection.php" class="screening-btn2"><i class="fas fa-users icon"></i> Screening and Selection</a>
        <a href="onboarding-status.php" class="btn3"><i class="fas fa-user-check icon"></i> Onboarding Status</a>
    </div>

    <section class="div2">
        <div class="searchdiv">
            <input type="search" id="search" class="searchBar" placeholder=" Search..." data-search>
            <button class="searchbtn" type="submit"> <i class="fas fa-search icon"></i></button>
        </div>

    </section>

    <section class="table_body">
        <table class="screening-container">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Applied Position</th>
                    <th>Age</th>
                    <th>Email</th>
                    <th>Resume</th>
                    <th>Status</th>
                    <th>Action</th> <!-- Added Action Column -->
                </tr>
            </thead>
            <tbody>
                <?php

                include './Models/Apply.php';
                $list = new Apply();
                $list->DisplayList_screening();

                ?>
            </tbody>
        </table>
    </section>


    <!-- This div is where the PDF will be displayed on the same page -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>View Resume</h2>
            <div id="pdfViewer"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        //search
        const search = document.querySelector('.searchdiv .searchBar'),
            table_rows = document.querySelectorAll('tbody tr'),
            table_headings = document.querySelectorAll('thead th');

        // Event listener for input on search bar
        search.addEventListener('input', searchTable);

        function searchTable() {
            table_rows.forEach((row, i) => {
                let table_data = row.textContent.toLowerCase(),
                    search_data = search.value.toLowerCase();

                row.classList.toggle('hide', table_data.indexOf(search_data) < 0);
                row.style.setProperty('--delay', i / 25 + 's');
            });

            document.querySelectorAll('tbody tr:not(.hide)').forEach((visible_row, i) => {
                visible_row.style.backgroundColor = (i % 2 == 0) ? 'transparent' : '#f8fafc';
            });
        }
        //end of the search



        $(document).ready(function() {
            // Handle Accept button click
            $('.btn_accept').click(function() {
                var applicantName = $(this).data('name');
                var applicantId = $(this).data('id');

                $.ajax({
                    url: 'screening-selection.php',
                    type: 'POST',
                    data: {
                        action: 'accept',
                        name: applicantName,
                        id: applicantId
                    },
                    success: function(response) {
                        alert('Applicant ' + applicantName + ' has been accepted.');
                        location.reload(); // Reload page to see updated status
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });

            // Handle Reject button click
            $('.btn_reject').click(function() {
                var applicantName = $(this).data('name');
                var applicantId = $(this).data('id');

                $.ajax({
                    url: 'screening-selection.php',
                    type: 'POST',
                    data: {
                        action: 'reject',
                        name: applicantName,
                        id: applicantId
                    },
                    success: function(response) {
                        alert('Applicant ' + applicantName + ' has been rejected.');
                        location.reload(); // Reload page to see updated status
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        });
    </script>



    <script>
        $(document).ready(function() {
            $(document).on('click', '#btn_view', function(e) {
                $('#pdfViewer').html(
                    `
                    <embed src='./Models/Upload_resume/${e.target.value}' width='100%' height='1000px' type='application/pdf'>
                    `
                );
                // Remove any existing modals before appending a new one
                $('#updateModal').show();
                // Bind the close button event after the modal has been appended
                $('.close').on('click', function() {
                    $('#updateModal').hide();
                });
            });
        });

        $(document).ready(function() {
            $(".btn_archive").click(function() {
                var id = $(this).data("id");

                console.log("Archive button clicked. ID: " + id); // Debug: Check the ID of the record being archived

                if (confirm("Are you sure you want to archive this record?")) {
                    console.log("User confirmed archiving."); // Debug: User confirmed the action

                    $.ajax({
                        url: 'Models/archive_functions.php',
                        type: 'POST',
                        data: {
                            action: 'archive', // Ensure this matches the expected action
                            id: id
                        },
                        success: function(response) {
                            console.log("Success:", response);
                            location.reload(); // Reload the page
                        },
                        error: function(xhr, status, error) {
                            console.error("Error:", error);
                        }
                    });


                } else {
                    console.log("User canceled the archiving."); // Debug: User canceled the action
                }
            });
        });
    </script>



</body>

</html>