<?php
session_start();  // Start the session

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
    <link rel="stylesheet" type="text/css" href="css/onbordingstyles.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- ajax cdn -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- sweet alert cdn -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
</head>

<body>

    <?php include "displayAuthenticatedUser.php"; ?>

    <div class="container">
        <div class="photo">
            <img src="assets/logo.png">
        </div>
        <div class="dashboardSection">
            <a href="dashboard.php" class="dashboard"><i class="fas fa-tachometer-alt icon"></i>DASHBOARD</a>
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
        <a href="./Job_offer.php" class="btn1"><i class="fas fa-briefcase icon"></i> Job Offers</a>
        <a href="screening-selection.php" class="btn2"><i class="fas fa-users icon"></i> Screening and Selection</a>
        <a href="onboarding-status.php" class="onboarding-btn3"><i class="fas fa-user-check icon"></i> Onboarding Status</a>
    </div>

    <section class="div2">
        <div class="searchdiv">
            <input type="search" id="search" class="searchBar" placeholder="  Search..." data-search>
            <button class="searchbtn" type="submit"> <i class="fas fa-search icon"></i></button>
        </div>
    </section>


    <section class="table_body">
        <table class="onboarding-container">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Interview Status</th>
                    <th>Application Status</th>
                    <th>Onboarding Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                include_once './Models/Onboarding_status.php';
                global $conn;
                $Onboard = new Onboarding_status($conn);
                $Onboard->DisplayOnboarding();
                ?>
            </tbody>
        </table>
    </section>


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


        // Event for update btn
        $(document).ready(function() {
            $(document).on('click', '.Update_btn', function(event) {
                const id = $(this).data('id'); // Get the ID

                // Check if the ID is valid
                if (!id) {
                    return;
                }

                $.ajax({
                    url: './Models/Onboarding_status.php', // Update with the correct URL if needed
                    type: "POST",
                    data: {
                        id: id
                    },
                    beforeSend: function() {
                        // Before request actions (if needed)
                    },
                    success: function(data, status) {
                        // Check if data returned is correct and modal exists
                        if (!data) {
                            return;
                        }

                        $('#updateModal').remove(); // Remove existing modal
                        $('body').append(data); // Append the new modal

                        // Show the modal
                        if ($('#updateModal').length > 0) {
                            $('#updateModal').show();
                        }

                        // Close button functionality
                        $('.close').on('click', function() {
                            $('#updateModal').hide(); // Hide the modal
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire("Error", "An error occurred while fetching the data. Please try again.", "error");
                    }
                });
            });
        });



        var interview_status = '';
        var application_status = '';
        var onboarding_status = '';

        $(document).on('change', '#interview_status', function(event) {
            interview_status = event.target.value;
        });

        $(document).on('change', '#application_status', function(event) {
            application_status = event.target.value;
        });

        $(document).on('change', '#onboarding_status', function(event) {
            onboarding_status = event.target.value;
        });

        // Event for save btn
        $(document).on('click', '#Save_button', function(event) {
            var app_status = $('#app_status').val(); // Capture selected application status
            var view_status = $('#view_status').val(); // Capture interview status
            var onboard_status = $('#onboard_status').val(); // Capture onboarding status

            var Data = {
                Save_id: event.target.value, // The ID of the row being updated
                interview_status: interview_status || view_status, // Default to current if no change
                application_status: application_status || app_status, // Default to current if no change
                onboarding_status: onboarding_status || onboard_status // Default to current if no change
            };

            // Making AJAX request to save data
            $.ajax({
                url: './Models/Onboarding_status.php', // Update path if necessary
                type: "POST",
                data: Data,
                dataType: 'json',
                encode: true,
                success: function(res) {
                    if (res.success == true) {
                        Swal.fire({
                            title: "Success!",
                            text: res.message,
                            icon: "success"
                        });
                        setTimeout(() => {
                            window.location.reload()
                        }, 2000); // Reload the page after success
                    }
                }
            });
        });

        $(document).ready(function() {
            $(".Archive_btn").click(function() {
                var id = $(this).data("id");

                console.log("Archive button clicked. ID: " + id); // Debug: Check the ID of the record being archived

                if (confirm("Are you sure you want to archive this record?")) {
                    console.log("User confirmed archiving."); // Debug: User confirmed the action

                    $.ajax({
                        url: 'Models/archive_functions.php', // The PHP script that will handle the archiving
                        type: 'POST',
                        data: {
                            id: id
                        },
                        success: function(response) {
                            console.log("AJAX success. Response: ", response); // Debug: Log the response from the server
                            location.reload(); // Reloads the page
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX error. Status: " + status); // Debug: Log the status of the error
                            console.error("AJAX error. Error: " + error); // Debug: Log the specific error message
                            console.error("Response text: " + xhr.responseText); // Debug: Log the response text from the server
                            alert("An error occurred: " + error);
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