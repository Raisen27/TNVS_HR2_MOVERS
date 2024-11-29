<?php
session_start();  // Start the session
include 'Models/dbconnect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Redirect the user to the login page if not logged in
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required POST data are present
    if (isset(
        $_POST['employeeName'],
        $_POST['trainingProgram'],
        $_POST['evaluator'],
        $_POST['development'],
        $_POST['dateGiven'],
        $_POST['remarks']
    )) {

        // Sanitize input data to prevent SQL injection and ensure data is safe
        $employeeName = htmlspecialchars($_POST['employeeName']);
        $trainingProgram = htmlspecialchars($_POST['trainingProgram']);
        $evaluator = htmlspecialchars($_POST['evaluator']);
        $development = htmlspecialchars($_POST['development']);
        $dateGiven = htmlspecialchars($_POST['dateGiven']);
        $remarks = htmlspecialchars($_POST['remarks']);
        $archiveStatus = htmlspecialchars('!ACTIVE');

        // Prepare the SQL query
        $query = "INSERT INTO training_performance (employee_name, training_program, evaluator, development, date_given, remarks, archive_status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);

        // Check if the statement preparation failed
        if ($stmt === false) {
            // If preparation fails, display the error
            die('Error preparing statement: ' . $conn->error);
        }

        // Bind the parameters to the prepared statement
        $stmt->bind_param('sssssss', $employeeName, $trainingProgram, $evaluator, $development, $dateGiven, $remarks, $archiveStatus);

        // Execute the query and check for success
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Record added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add record: ' . $stmt->error]);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talent Management</title>
    <link rel="stylesheet" type="text/css" href="css/training-performance.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Ajax CDN -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Sweet Alert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <style>
        button {
            border: none;
            padding: 10px;
            background-color: #a6c4e5;
            border-radius: 10px;
            cursor: pointer;
        }

        /* Modal styles */
        .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            z-index: 1000;
            /* On top of other elements */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgba(0, 0, 0, 0.371);
            /* Black background with opacity */
        }

        h2 {
            margin-bottom: 10px;
        }

        p {
            margin-top: 0px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 3% auto;
            /*  from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 46%;
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

    <?php include 'displayAuthenticatedUser.php'; ?>

    <div class="container">
        <div class="photo">
            <img src="assets/logo.png" alt="Company Logo">
        </div>
        <!-- Sidebar navigation -->
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
            <a href="worked-hours.php" class="m-performance">
                <i class="fas fa-chart-line icon"></i> PERFORMANCE MANAGEMENT
            </a>
        </div>

        <div class="Logout">
            <a href="login.php" class="logoutbtn">
                <i class="fas fa-sign-out-alt icon"></i> LOG OUT
            </a>
        </div>
    </div>

    <div class="buttonContainer">
        <a href="worked-hours.php" class="menu1">
            <i class="fas fa-clock"></i> Worked Hours
        </a>
        <a href="achievement.php" class="menu2">
            <i class="fas fa-trophy"></i> Achievement
        </a>
        <a href="training-performance.php" class="menu3">
            <i class="fas fa-clipboard-check"></i> Training Performance
        </a>
    </div>

    <section class="div2">
        <!-- Add Record Button -->
        <button id="btn_add_record" class="add-btn"><i class="fas fa-plus"></i> ADD RECORD</button>

        <div class="searchdiv">
            <input type="search" id="search" class="searchBar" placeholder="Search Job..." data-search>
            <button class="searchbtn" type="submit"> <i class="fas fa-search icon"></i></button>
        </div>


    </section>

    <section class="table_body">
        <table class="employee-performance-container">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Training Program</th>
                    <th>Evaluator</th>
                    <th>Development</th>
                    <th>Date Given</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include './Models/training_performance.php';
                global $conn;
                $program = new training_performance($conn);
                $program->DisplayProgram();
                ?>
            </tbody>
        </table>
    </section>

    <!-- Add Modal -->
    <div id="addRecordModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2>Add Record</h2>
            <form id="addRecordForm" action="training-performance.php" method="POST">
                <label for="employeeName">Employee Name:</label>
                <input type="text" id="employeeName" name="employeeName" required>

                <label for="trainingProgram">Training Program:</label>
                <input type="text" id="trainingProgram" name="trainingProgram" required>

                <label for="evaluator">Evaluator:</label>
                <input type="text" id="evaluator" name="evaluator" required>

                <label for="development">Development:</label>
                <input type="text" id="development" name="development" required>

                <label for="dateGiven">Date Given:</label>
                <input type="date" id="dateGiven" name="dateGiven" required>

                <label for="remarks">Remarks:</label>
                <select id="remarks" name="remarks" required>
                    <option value="Success">Successful</option>
                    <option value="Failed">Failed</option>
                </select>

                <button type="submit" id="submitRecord" class="submitBtn">Add Record</button>
            </form>
        </div>
    </div>

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
            // Show the modal when the "Add Record" button is clicked
            $('#btn_add_record').click(function() {
                $('#addRecordModal').css('display', 'block');
            });

            // Close the modal when the "x" is clicked
            $('#closeModal').click(function() {
                $('#addRecordModal').css('display', 'none');
            });

            // Close the modal when clicking outside of the modal
            $(window).click(function(event) {
                if ($(event.target).is('#addRecordModal')) {
                    $('#addRecordModal').css('display', 'none');
                }
            });


        });

        //update

        $(document).ready(function() {
            $(document).on("click", '#Update_btn', function(e) {
                var id = e.target.value;
                console.log(id);

                $.ajax({
                    url: './Models/training_performance.php',
                    type: 'post',
                    data: {
                        id: id,
                        action: "update"
                    },
                    success: function(data, status) {
                        console.log(status);
                        $('#updateModal_' + id).remove(); // Remove any existing modals
                        $('body').append(data); // Append the new modal
                        $('#updateModal_' + id).show(); // Show the modal

                        $('.close').on('click', function() {
                            $('#updateModal_' + id).hide(); // Close modal on click
                        });
                    }
                });
            });

            $(document).on('click', '[id^=Save_button_]', function(e) {
                const id = e.target.value;
                const employee_name = $('#employee_name_' + id).val();
                const training_program = $('#training_program_' + id).val();
                const evaluator = $('#evaluator_' + id).val();
                const development = $('#development_' + id).val();
                const date_given = $('#date_given_' + id).val();
                const remarks = $('#remarks_' + id).val();


                $.ajax({
                    url: './Models/training_performance.php',
                    type: 'post',
                    data: {
                        action: "save_update",
                        id: id,
                        employee_name: employee_name,
                        training_program: training_program,
                        evaluator: evaluator,
                        development: development,
                        date_given: date_given,
                        remarks: remarks

                    },
                    success: function(response) {
                        alert(response);
                        location.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Error saving update:", textStatus, errorThrown);
                        alert("Failed to save update. Please try again.");
                    }
                });
            });

            //delete
            $(document).on('click', '[id^=delete_button_]', function(e) {
                const id = e.target.value;

                if (confirm("Are you sure you want to delete this training program?")) {
                    $.ajax({
                        url: './Models/training_performance.php',
                        type: 'post',
                        data: {
                            action: "delete_training_performance",
                            id: id
                        },
                        success: function(response) {
                            alert(response);
                            location.reload();
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error("Error deleting training program:", textStatus, errorThrown);
                            alert("Failed to delete training program. Please try again.");
                        }
                    });
                }
            });
        });
        //archive
        $(document).ready(function() {
            $("#archive_button_").click(function() {
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