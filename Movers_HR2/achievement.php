<?php
session_start();  // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Redirect the user to the login page if not logged in
    header('Location: login.php');
    exit();
}


// Database connection
include 'Models/config.php';
$db = new ConnectionDb();
$conn = $db->DbConnection();


// Fetch 
$sql = "SELECT * FROM achievement";
$result = $conn->query($sql);

// Check if query was successful
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talent Management</title>
    <link rel="stylesheet" type="text/css" href="css/achievement.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <?php include 'displayAuthenticatedUser.php'; ?>

    <div class="container">
        <div class="photo">
            <img src="assets/logo.png">
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
        <div class="performanceSection">
            <a href="worked-hours.php" class="m-performance">
                <i class="fas fa-chart-line icon"></i> Archive
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


        <!-- Add Employee Button -->
        <button id="btn_add_employee" class="add-btn"><i class="fas fa-plus"></i>ADD ACHIEVEMENT</button>

        <div class="searchdiv">
            <input type="search" id="search" class="searchBar" placeholder="Search Job..." oninput="search()">
            <button class="searchbtn" type="submit"> <i class="fas fa-search icon"></i></button>
        </div>

        <!-- Add Employee Modal -->
        <div id="addEmployeeModal" class="add-modal">
            <div class="add-modal-content">
                <span class="add-close" id="closeModal">&times;</span>
                <h3>Add Employee</h3>
                <form id="addEmployeeForm">
                    <label for="addemployeeName">Employee Name:</label>
                    <input type="text" id="addemployeeName" name="addemployeeName" required>

                    <label for="department">Department:</label>
                    <input type="text" id="department" name="department" required>

                    <label for="achievement"> Achievement:</label>
                    <input type="achievement" id="achievement" name="achievement" required>

                    <label for="dateGiven">Date Given:</label>
                    <input type="date" id="dateGiven" name="dateGiven" required>

                    <label for="givenBy">Given By:</label>
                    <input type="text" id="givenBy" name="givenBy" required>

                    <button type="submit" id="submit" class="submitBtn">SUBMIT</button>
                </form>
            </div>

        </div>

    </section>
    <!-- Employee Cards Container -->
    <div class="employee-cards-container">
        <!-- Employee cards will be dynamically inserted here -->
    </div>

    <!-- Modal to show achievements -->
    <div id="achievementModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" id="closeAchievementModal">&times;</span>
            <h2 id="employeeName"></h2><!-- Display employee name -->
            <p id="employeeDepartment"></p> <!-- Display employee department -->

            <section class="table_body">
                <table class="achievement-table">
                    <thead>
                        <tr>
                            <th>Achievements</th>
                            <th>Date Given</th>
                            <th>Given By</th>
                        </tr>
                    </thead>
                    <tbody id="achievementTableBody">
                        <!-- Fetched achievements data will be inserted here -->
                    </tbody>
                </table>
            </section>

        </div>
    </div>


    <script>
        //search event
        function search() {
            let filter = document.getElementById('search').value.toLowerCase();
            let employeeCards = document.querySelectorAll('.employee-card');

            employeeCards.forEach(card => {
                let name = card.querySelector('h3').innerText.toLowerCase();
                if (name.includes(filter)) {
                    card.style.display = ""; // Show card if match found
                } else {
                    card.style.display = "none"; // Hide card if no match
                }
            });
        }
        //end of search

        $(document).ready(function() {
            // Show the modal when the "Add employee" button is clicked
            $('#btn_add_employee').click(function() {
                $('#addEmployeeModal').css('display', 'block');
            });

            // Close the modal when the "x" is clicked or clicking outside the modal
            function closeAddEmployeeModal() {
                $('#addEmployeeModal').css('display', 'none');
            }

            $('#closeModal').click(closeAddEmployeeModal);
            $(window).click(function(event) {
                if ($(event.target).is('#addEmployeeModal')) {
                    closeAddEmployeeModal();
                }
            });

            // Handle form submission via AJAX
            $('#addEmployeeForm').submit(function(event) {
                event.preventDefault(); // Prevent the default form submission

                // Gather form data
                var addemployeeName = $('#addemployeeName').val();
                var department = $('#department').val();
                var achievement = $('#achievement').val(); // Assuming you have an input for achievement
                var dateGiven = $('#dateGiven').val(); // Assuming you have an input for date given
                var givenBy = $('#givenBy').val(); // Assuming you have an input for who gave the achievement

                // Send the data to the server via AJAX
                $.ajax({
                    url: 'Models/achievements.php', // PHP script to handle employee addition
                    type: 'POST',
                    data: {
                        employeeName: addemployeeName,
                        department: department,
                        achievement: achievement,
                        dateGiven: dateGiven,
                        givenBy: givenBy
                    },
                    dataType: 'json', // Expect JSON response from the server
                    success: function(response) {
                        alert(response.message);
                        if (response.status === 'success') {
                            closeAddEmployeeModal(); // Close the modal
                            loadEmployees(); // Reload employee data to see the new addition
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error: " + error);
                        console.error(xhr.responseText); // Log the actual response for debugging
                        alert('Failed to add Employee. Please try again.');
                    }
                });
            });

            // Load employee data and create employee cards
            function loadEmployees() {
                $.ajax({
                    url: 'Models/achievements.php', // PHP script to fetch employees
                    type: 'GET',
                    dataType: 'json', // Expect JSON response from the server
                    success: function(employees) {
                        var employeeCardsContainer = $('.employee-cards-container');
                        employeeCardsContainer.empty(); // Clear existing cards

                        // Create employee cards dynamically
                        employees.forEach(function(employee) {
                            var card = $('<div class="employee-card"></div>');
                            card.html('<h3>' + employee.employee_name + '</h3><p>' + employee.department + '</p>');
                            card.data('employee', employee);

                            // Click event to show  achievements
                            card.click(function() {
                                var selectedEmployee = $(this).data('employee');
                                console.log('Selected Employee:', selectedEmployee); // Log employee data for debugging

                                // Set employee name and department
                                $('#employeeName').text(selectedEmployee.employee_name);
                                $('#employeeDepartment').text(selectedEmployee.department);

                                // Force a reflow to update the UI
                                $('#employeeName').hide().show(0);
                                $('#employeeDepartment').hide().show(0);

                                // Fetch  achievements for the selected employee
                                loadAchievement(selectedEmployee.employee_name); // Assuming this function exists

                                // Show the achievements modal
                                $('#achievementModal').css('display', 'block'); // Make modal visible
                            });

                            employeeCardsContainer.append(card);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching employees: ', error);
                    }
                });
            }

            // Load employees on page load
            loadEmployees(); // Call to load employees when the page loads
        });



        // Load employees dynamically
        function loadEmployees() {
            $.ajax({
                url: 'Models/achievements.php',
                type: 'GET',
                success: function(response) {
                    var employees = JSON.parse(response);
                    $('.employee-cards-container').empty(); // Clear container before adding new employees

                    employees.forEach(function(employee) {
                        var card = $('<div class="employee-card"></div>');
                        card.html('<h3>' + employee.employee_name + '</h3><p>' + employee.department + '</p>');
                        card.data('employee', employee);

                        card.click(function() {
                            $('#employeeName').text(employee.employee_name);
                            $('#employeeDepartment').text(employee.department);
                            loadAchievement(employee.employee_name);
                            $('#achievementModal').css('display', 'block');
                        });

                        $('.employee-cards-container').append(card);
                    });
                },
                error: function() {
                    console.error('Error loading employees.');
                }
            });
        }



        // Show the Add Achievement modal
        $('#btn_add_achievement').click(function() {
            $('#addachievementModal').css('display', 'block');
        });

        // Close the Add Achievement modal
        $('#closeAchievement').click(function() {
            $('#addachievementModal').css('display', 'none');
        });

        // Close the Achievement modal
        $('#closeAchievementModal').click(function() {
            $('#achievementModal').css('display', 'none');
        });


        // Load employees on page load
        loadEmployees();



        function loadAchievement(employeeName) {
            $.ajax({
                url: './Models/add_achievement.php', // Endpoint to fetch achievements for the given employee
                type: 'POST',
                data: {
                    employee_name: employeeName
                },
                success: function(data) {
                    $('#achievementTableBody').html(data); // Update the table body with new achievements
                },
                error: function(xhr, status, error) {
                    console.error('Error loading achievements:', error);
                }
            });
        }
    </script>
</body>

</html>