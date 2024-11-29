<?php
session_start();  // Start the session
include 'Models/dbconnect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Redirect the user to the login page if not logged in
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the required form data are present
    if (isset($_POST['jobName'], $_POST['status'])) {

        // Sanitize the input to prevent SQL injection and XSS attacks
        $job_name = htmlspecialchars($_POST['jobName']);
        $status = htmlspecialchars($_POST['status']);

        // Prepare the query to insert a new job offer
        $query = "INSERT INTO job_offers (job_name, status, Delete_Status) VALUES (?, ?, 'Active')";
        $stmt = $conn->prepare($query);

        // Check if the statement preparation fails
        if ($stmt === false) {
            die('Error preparing the statement: ' . $conn->error);
        }

        // Bind the parameters to the prepared statement
        $stmt->bind_param('ss', $job_name, $status);

        // Execute the query and check for success
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Job offer added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add job offer: ' . $stmt->error]);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    }
}
?>



<!DOCTYPE html>
<html>

<head>
    <title>Talent Management</title>
    <link rel="stylesheet" type="text/css" href="css/module1styles.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Ajax CDN -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Sweet Alert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .icon {
            margin-right: 8px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
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
    </style>
</head>

<body>

    <?php include  'displayAuthenticatedUser.php';   ?>

    <!--side nav -->
    <div class="container">
        <div class="photo">
            <img src="assets/logo.png">
        </div>

        <div class="dashboardSection">
            <a href="dashboard.php" class="dashboard"><i class="fas fa-tachometer-alt icon"></i> DASHBOARD</a>
        </div>

        <div class="recruitmentSection">
            <a href="./Job_offer.php" class="m-recruitment"><i class="fas fa-user-plus icon"></i> RECRUITMENT AND ONBOARDING</a>
        </div>

        <div class="trainingSection">
            <a href="module3.php" class="trainAndDev"><i class="fas fa-chalkboard-teacher icon"></i> TRAINING MANAGEMENT</a>
        </div>

        <div class="performanceSection">
            <a href="worked-hours.php" class="performance"><i class="fas fa-chart-line icon"></i> PERFORMANCE MANAGEMENT</a>
        </div>

        <div class="Logout">
            <a href="logout.php" class="logoutbtn">
                <i class="fas fa-sign-out-alt icon"></i> LOG OUT
            </a>
        </div>
    </div> <!-- End of side nav -->

    <!-- menu -->
    <div class="buttonContainer">
        <a href="Job_offer.php" class="job-btn1">
            <i class="fas fa-briefcase icon"></i> Job Offers
        </a>
        <a href="screening-selection.php" class="btn2">
            <i class="fas fa-users icon"></i> Screening and Selection
        </a>
        <a href="onboarding-status.php" class="btn3">
            <i class="fas fa-user-check icon"></i> Onboarding Status
        </a>
    </div>

    <!-- add-search div -->
    <section class="div2"><!-- Add Job Button -->

        <form action=""></form>
        <button id="btn_add_job" class="add_btn"><i class="fas fa-plus"></i> ADD JOB</button>



        <div class="searchdiv">
            <input type="search" id="search" class="searchBar" placeholder="Search Job..." data-search>
            <button class="searchbtn" type="submit"> <i class="fas fa-search icon"></i></button>
        </div>
    </section><!-- End of this div -->

    <section class="table_body">
        <table class="offer-container">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Job</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="jobOffersTableBody">
                <?php
                include_once './Models/JobOffer.php';
                global $conn;
                $jobList = new JobOffer($conn);
                $jobList->DisplayJob_offer();
                ?>
            </tbody>
        </table>
    </section>

    <!-- Add Job Modal -->
    <div id="addJobModal" class="add-modal">
        <div class="add-modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2>Add Job</h2>
            <form id="addJobForm" action="Job_offer.php" method="POST">
                <label for="jobName">Job Name:</label>
                <input type="text" id="jobName" name="jobName" required>

                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
                <button type="submit">Add Job</button>
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


        // add job modal
        $(document).ready(function() {
            // Add Job Modal functionality
            $('#btn_add_job').click(function() {
                $('#addJobModal').css('display', 'block');
            });

            $('#closeModal').click(function() {
                $('#addJobModal').css('display', 'none');
            });

            $(window).click(function(event) {
                if ($(event.target).is('#addJobModal')) {
                    $('#addJobModal').css('display', 'none');
                }
            });



            // Delete job event
            $(document).on('click', '#delete_btn', function(event) {
                var jobId = event.target.value;

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: './Models/JobOffer.php',
                            type: 'POST',
                            data: {
                                id: jobId
                            },
                            dataType: 'json',
                            success: function(res) {
                                if (res.status === 'success') {
                                    Swal.fire('Deleted!', res.message, 'success');
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 2000);
                                }
                            }
                        });
                    }
                });
            });

            // Update job event
            $(document).on('click', '#btn_update', function(e) {
                $.ajax({
                    url: './Models/JobOffer.php',
                    type: 'POST',
                    data: {
                        Job_id: e.target.value
                    },
                    success: function(data) {
                        $('#updateModal').remove();
                        $('body').append(data);
                        $('#updateModal').show();

                        $('.close').on('click', function() {
                            $('#updateModal').hide();
                        });
                    }
                });
            });

            // for saving updated info
            // event for select status

            var UpdatedStatus = '';
            $(document).on('change', '#Job_offer_status', function(event) {
                UpdatedStatus = event.target.value;
            })

            $(document).on('click', '#confirmUpdate', function() {
                var Data = {
                    Save_status: UpdatedStatus,
                    Job_id_save: event.target.value,
                    job_name: $('#updateInput').val()
                }

                // ajax request to save the data
                $.ajax({
                    url: "./Models/JobOffer.php",
                    type: 'POST',
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
                            }, 2000)
                        }
                    }
                })
            })
        })
    </script>

</body>

</html>