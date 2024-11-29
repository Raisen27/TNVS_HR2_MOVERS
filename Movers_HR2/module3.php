<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Redirect the user to the login page if not logged in
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Talent Management</title>
    <link rel="stylesheet" type="text/css" href="css/module3styles.css">
    <!-- AJAX CDN -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        button {
            border: none;
            padding: 10px;
            background-color: #a6c4e5;
            border-radius: 10px;
        }

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
            background-color: rgba(0, 0, 0, 0.4);
            /* Black w/ opacity */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            /* 15% from the top and centered */
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

        #Job_offer_status {
            padding: 10px;
        }

        select {
            padding: 10px;
        }

        .icon {
            margin-right: 8px;
            /* Space between icon and text */
        }
    </style>
</head>

<body>


    <?php include  'displayAuthenticatedUser.php';   ?>

    <div class="container">
        <div class="photo">
            <img src="assets/logo.png">
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
            <a href="module3.php" class="m-trainAndDev">
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
        <a href="module3.php" class="tp-btn1train">
            <i class="fas fa-book icon"></i> Training Program
        </a>
        <a href="training-schedule.php" class="btn3train">
            <i class="fas fa-calendar-alt icon"></i> Training Schedule</a>
        <a href="training-status.php" class="btn2train">
            <i class="fas fa-check-circle icon"></i> Training Status
        </a>
        </button>

    </div>

    <section class="div2">
        <!-- Add training Program -->
        <button id="btn_add_training" class="add_program"><i class="fas fa-plus"></i> ADD PROGRAM</button>

        <!-- Add training modal -->
        <div id="addtrainingModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeModal">&times;</span>
                <h3>Add Program</h3>
                <form id="addtrainingform">
                    <label for="trainingName">Training Name:</label>
                    <input type="text" id="trainingName" name="trainingName" required>

                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required>

                    <label for="time">Time:</label>
                    <input type="time" id="time" name="time" required>

                    <label for="place">Place:</label>
                    <input type="text" id="place" name="place" required>

                    <button type="submit" id="submitProgram" class="submitBtn">Add Program</button>
                </form>
            </div>
        </div>

        <div class="searchdiv">
            <input type="search" id="search" class="searchBar" placeholder="Search Job..." data-search>
            <button class="searchbtn" type="submit"> <i class="fas fa-search icon"></i></button>
        </div>
    </section>


    <section class="table_body">
        <table class="prog-container">
            <thead>
                <tr>
                    <th>Training Name</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Place</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include './Models/Training_program.php';
                global $conn;
                $program = new Training_program($conn);
                $program->DisplayProgram();
                ?>
            </tbody>
        </table>
    </section>


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
                    url: './Models/Training_program.php',
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
                const training_name = $('#training_name_' + id).val();
                const date = $('#date_' + id).val();
                const time = $('#time_' + id).val();
                const place = $('#place_' + id).val();

                $.ajax({
                    url: './Models/Training_program.php',
                    type: 'post',
                    data: {
                        action: "save_update",
                        id: id,
                        training_name: training_name,
                        date: date,
                        time: time,
                        place: place
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

            $(document).on('click', '[id^=delete_button_]', function(e) {
                const id = e.target.value;

                if (confirm("Are you sure you want to delete this training program?")) {
                    $.ajax({
                        url: './Models/Training_program.php',
                        type: 'post',
                        data: {
                            action: "delete_training_program",
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

            $('#addtrainingform').submit(function(event) {
                event.preventDefault();

                var trainingName = $('#trainingName').val();
                var date = $('#date').val();
                var time = $('#time').val();
                var place = $('#place').val();

                $.ajax({
                    url: 'Models/add_trainingProgram.php',
                    type: 'POST',
                    data: {
                        trainingName: trainingName,
                        date: date,
                        time: time,
                        place: place
                    },
                    success: function(response) {
                        alert('Program added successfully!');
                        $('#addtrainingModal').css('display', 'none');
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        alert('Failed to add program. Please try again.');
                    }
                });
            });
        });
    </script>
</body>

</html>