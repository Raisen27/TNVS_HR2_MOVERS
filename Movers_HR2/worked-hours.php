<?php
session_start();  // Start the session

//Database Connection
include 'Models/config.php';
$db = new ConnectionDb();
$conn = $db->DbConnection();


// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Redirect the user to the login page if not logged in
    header('Location: login.php');
    exit();
}


// Fetch worked_hours
$sql = "SELECT * FROM worked_hours";
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
    <link rel="stylesheet" type="text/css" href="css/worked-hours.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>



<?php include  'displayAuthenticatedUser.php';   ?>

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

<section class="div2"><!-- Add Employee -->
    <button id="btn_add_employee" class="add-btn"><i class="fas fa-plus"></i>ADD EMPLOYEE</button>

    <!-- Add employee Modal -->
    <div id="addEmployeeModal" class="add-modal">
        <div class="add-modal-content">
            <span class="add-close" id="closeModal">&times;</span>
            <h3>Add Employee</h3>
            <form id="addEmployeeForm">

                <label for="addemployeeName">Employee Name:</label>
                <input type="text" id="addemployeeName" name="addemployeeName" required>

                <label for="department">Department:</label>
                <input type="text" id="department" name="department" required>

                <button type="submit" id="submit" class="submitBtn">SUBMIT</button>
            </form>
        </div>
    </div>
    <div class="searchdiv">
        <input type="search" id="search" class="searchBar" placeholder="Search Job..." oninput="search()">
        <button class="searchbtn" type="submit"> <i class="fas fa-search icon"></i></button>
    </div>
</section>


<!-- PUT THE CLICKABLE DIV HERE -->

<div class="employee-cards-container">
    <!-- Employee cards will be dynamically inserted here -->
</div>

<!-- Modal to show worked hours -->
<div id="workedHoursModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" id="closeWorkedHoursModal">&times;</span>
        <h2 id="employeeName"></h2><!-- Display employee name -->
        <p id="employeeDepartment"></p> <!-- Display employee department -->
        <p id="employeeName"></p>

        <section class="table_body">
            <table class="worked-hours-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Total Hours</th>
                    </tr>
                </thead>
                <tbody id="workedHoursTableBody">
                    <!-- Fetched worked hours data will be inserted here -->
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

            // Send the data to the server via AJAX
            $.ajax({
                url: 'Models/worked_hours.php', // PHP script to handle employee addition
                type: 'POST',
                data: {
                    addemployeeName: addemployeeName,
                    department: department
                },
                dataType: 'json', // Expect JSON response from the server
                success: function(response) {
                    alert(response.message);
                    if (response.status === 'success') {
                        closeAddEmployeeModal(); // Close the modal
                        location.reload(); // Reload the page to see the new employee
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
                url: 'Models/worked_hours.php', // PHP script to fetch employees
                type: 'GET',
                success: function(response) {
                    var employees = JSON.parse(response);
                    var employeeCardsContainer = $('.employee-cards-container');
                    employeeCardsContainer.empty(); // Clear existing cards

                    // Create employee cards dynamically
                    employees.forEach(function(employee) {
                        var card = $('<div class="employee-card"></div>');
                        card.html('<h3>' + employee.employee_name + '</h3><p>' + employee.department + '</p>');
                        card.data('employee', employee);

                        // Click event to show worked hours
                        card.click(function() {
                            var selectedEmployee = $(this).data('employee');
                            console.log('Selected Employee:', selectedEmployee); // Log employee data for debugging

                            // Set employee name and department
                            $('#employeeName').text(selectedEmployee.employee_name);
                            $('#employeeDepartment').text(selectedEmployee.department);

                            // Force a reflow
                            $('#employeeName').hide().show(0);
                            $('#employeeDepartment').hide().show(0);

                            // Fetch worked hours for the selected employee
                            loadWorkedHours(selectedEmployee.employee_name);

                            // Show the modal
                            $('#workedHoursModal').css('display', 'block'); // Make modal visible
                        });

                        employeeCardsContainer.append(card);
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching employees: ', error);
                }
            });
        }

        // Load worked hours for a specific employee
        function loadWorkedHours(employee_name) {
            $.ajax({
                url: 'Models/worked_hours.php', // PHP script to fetch worked hours
                type: 'GET',
                data: {
                    employee_name: employee_name
                },
                success: function(response) {
                    $('#workedHoursTableBody').empty(); // Clear the table body before appending new data
                    $('#workedHoursTableBody').html(response); // Assuming response is valid HTML
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching worked hours: ', error);
                }
            });
        }

        // Close the worked hours modal
        $('#closeWorkedHoursModal').click(function() {
            $('#workedHoursModal').css('display', 'none');
        });

        // Load employees initially
        loadEmployees();
    });
</script>



</body>

</html>