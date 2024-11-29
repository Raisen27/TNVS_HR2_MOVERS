<?php
session_start(); // Start the session

include 'Models/dbconnect.php';

$message = ""; // Initialize a variable to store messages

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    $message = "<p class='error'>You are not logged in. Redirecting to the login page...</p>";
    // Redirect the user to the login page if not logged in
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $employeeName = $conn->real_escape_string($_POST['employeeName']);
    $trainingProgram = $conn->real_escape_string($_POST['training_program']);
    $date = $conn->real_escape_string($_POST['date']);

    // SQL query to insert data into the database
    $query = "INSERT INTO training_status (name, position, training_program, evaluator, start_date) VALUES ('$employeeName', '', '$trainingProgram', '', NOW())";

    if ($conn->query($query) === TRUE) {
        $message = "<p class='success'>New training program added successfully.</p>";
    } else {
        $message = "<p class='error'>Error adding training program: " . htmlspecialchars($conn->error) . "</p>";
    }

    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Talent Management</title>
    <link rel="stylesheet" type="text/css" href="css/trainingstatus.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!--ajax cdn-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

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
            <img src="assets/logo.png">
        </div>

        <div class="dashboardSection">
            <a href="dashboard.php" class="dashboard"><i class="fas fa-tachometer-alt icon"></i>DASHBOARD</a>
        </div>

        <div class="recruitmentSection">
            <a href="./Job_offer.php" class="recruitment"><i class="fas fa-user-plus icon"></i>RECRUITMENT AND ONBOARDING</a>
        </div>

        <div class="trainingSection">
            <a href="module3.php" class="m-trainAndDev"><i class="fas fa-chalkboard-teacher icon"></i>TRAINING MANAGEMENT</a>
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
        <a href="module3.php" class="btn1train"><i class="fas fa-book icon"></i>Training Program</a>
        <a href="training-schedule.php" class="btn3train"><i class="fas fa-calendar-alt icon"></i>Training Schedule</a>
        <a href="training-status.php" class="tp-btn2train"><i class="fas fa-check-circle icon"></i> Training Status</a>
    </div>

    <section class="div2">
        <!-- Add training Program -->
        <button id="btn_add_training" class="add_program"><i class="fas fa-plus"></i> ADD EMPLOYEE</button>


        <div class="searchdiv">
            <input type="search" id="search" class="searchBar" placeholder="Search Job..." data-search>
            <button class="searchbtn" type="submit"> <i class="fas fa-search icon"></i></button>
        </div>
    </section>

    <section class="table_body">
        <table class="tscointainer">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Training Program</th>
                    <th>Evaluator</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include './Models/training_status.php';
                global $conn;
                $t_status = new training_status($conn);
                $t_status->DisplayTrainingStatus();
                ?>
            </tbody>
        </table>
    </section>

    <div id="addtrainingModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h3>Add Program</h3>
            <form id="addtrainingform" action="training-status.php" method="POST">

                <label for="employeeName">Employee Name:</label>
                <input type="text" id="employeeName" name="employeeName" required>

                <label for="training_program">Training Program:</label>
                <input type="text" id="training_program" name="training_program" required>

                <label for="date">Start Date:</label>
                <input type="date" id="date" name="date" required>


                <button type="submit" id="submitProgram" class="submitBtn">Add Program</button>
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
            $(document).on("click", '#Update_btn', function(e) {
                var id = e.target.value;
                console.log(id);

                $.ajax({
                    url: './Models/training_status.php',
                    type: 'post',
                    data: {
                        id: id,
                        action: "update"
                    },
                    success: function(data, status) {
                        console.log(status);
                        // Remove any existing modals
                        $('#updateModal_' + id).remove();
                        // Append the new modal from the AJAX response
                        $('body').append(data);
                        // Show the modal
                        $('#updateModal_' + id).show();

                        // Close modal when clicking on the close button
                        $('.close').on('click', function() {
                            $('#updateModal_' + id).hide();
                        });
                    }
                });
            });

            $(document).on('click', 'button[id^="Save_button_"]', function() {
                var id = $(this).val(); // Get the ID from the button

                // Collect the updated values from the modal
                var name = $('#name' + id).val();
                var position = $('#position' + id).val();
                var training_program = $('#training_program_' + id).val();
                var evaluator = $('#evaluator' + id).val();
                var start_date = $('#start_date_' + id).val();
                var end_date = $('#end_date_' + id).val();
                var status = $('#status_' + id).val();
                console.log(training_program)

                // Send the data via AJAX to the server
                $.ajax({
                    url: './Models/training_status.php', // Your PHP file that will handle the update
                    type: 'POST',
                    data: {
                        id: id,
                        name: name,
                        position: position,
                        training_program: training_program,
                        evaluator: evaluator,
                        start_date: start_date,
                        end_date: end_date,
                        status: status,
                        action: "save_"
                    },
                    success: function(response) {
                        alert(response); // Display the server's response
                        location.reload(); // Reload the page after successful update
                    },
                    error: function(xhr, status, error) {
                        console.error("Error: " + error);
                        alert('An error occurred while updating the training status.');
                    }
                });
            });

            // Function to close the modal
            function closeModal(id) {
                $('#updateModal_' + id).hide();
            }

            $(document).on('click', 'button[id^="delete_button_"]', function() {
                var id = $(this).val(); // Get the ID from the button

                // Confirm deletion before proceeding
                if (confirm('Are you sure you want to delete this training status?')) {
                    // Send the delete request via AJAX
                    $.ajax({
                        url: './Models/training_status.php', // PHP file that will handle the delete
                        type: 'POST',
                        data: {
                            id: id,
                            action: "delete"
                        },
                        success: function(response) {
                            alert(response); // Display the server's response
                            location.reload(); // Reload the page to reflect the deletion
                        },
                        error: function(xhr, status, error) {
                            console.error("Error: " + error);
                            alert('An error occurred while deleting the training status.');
                        }
                    });
                }
            });
        });



        $(document).ready(function() {
            $('#btn_add_training').click(function() {
                $('#addtrainingModal').css('display', 'block');
            });

            $('#closeModal').click(function() {
                $('#addtrainingModal').css('display', 'none');
            });

            $(window).click(function(event) {
                if ($(event.target).is('#addtrainingModal')) {
                    $('#addtrainingModal').css('display', 'none');
                }
            });


        })
    </script>
</body>

</html>