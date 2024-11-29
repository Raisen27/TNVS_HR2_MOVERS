<?php
// Database connection
include 'config.php';
$db = new ConnectionDb();
$conn = $db->DbConnection();
// Initialize response variable
$response = ['status' => 'error', 'message' => 'Unknown error occurred.'];

// Handle adding an achievement (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (empty($_POST['employeeName']) || empty($_POST['department']) || empty($_POST['achievement']) || empty($_POST['dateGiven']) || empty($_POST['givenBy'])) {
        $response['message'] = 'All fields are required.';
        echo json_encode($response);
        exit;
    }

    // Get data from the form
    $employeeName = $_POST['employeeName'];
    $department = $_POST['department'];
    $achievement = $_POST['achievement'];
    $dateGiven = $_POST['dateGiven'];
    $givenBy = $_POST['givenBy'];

    // Prepare and execute the statement
    $stmt = $conn->prepare("INSERT INTO achievement (employee_name, department, achievement, date_given, given_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $employeeName, $department, $achievement, $dateGiven, $givenBy);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Achievement added successfully.';
    } else {
        $response['message'] = 'Error adding achievement: ' . $stmt->error; // Provide specific error message
    }

    $stmt->close();
    echo json_encode($response);
    $conn->close();
    exit;
}

// Check if we are fetching achievements (GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // If we are fetching achievements for a specific employee
    if (isset($_GET['employee_name'])) {
        $employeeName = $_GET['employee_name'];

        // Fetch achievements for the selected employee
        $sql = "SELECT achievement, date_given, given_by FROM achievement WHERE employee_name = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            echo json_encode(['status' => 'error', 'message' => "Error preparing query: " . $conn->error]);
            exit();
        }

        $stmt->bind_param("s", $employeeName);
        $stmt->execute();
        $result = $stmt->get_result();

        // Prepare the output for the table
        $achievements = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $achievements[] = $row;
            }
        } else {
            $achievements = [['achievement' => 'No records found', 'date_given' => '', 'given_by' => '']];
        }

        $stmt->close();
        $conn->close();

        // Return achievements as JSON
        echo json_encode($achievements);
        exit();
    } else {
        // Fetch all employees if no specific employee is requested
        $sql = "SELECT DISTINCT employee_name, department FROM achievement";
        $result = $conn->query($sql);

        $employees = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $employees[] = $row; // Collect employees
            }
        }

        // Return the employees as JSON
        echo json_encode($employees);
        $conn->close();
        exit();
    }
}

// Close the database connection at the end of the script
$conn->close();
