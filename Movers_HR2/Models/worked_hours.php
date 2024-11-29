<?php
// Database connection
include 'config.php';
$db = new ConnectionDb();
$conn = $db->DbConnection();

// Check if we are fetching worked hours (GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['employee_name'])) {
    $employeeName = $_GET['employee_name'];

    // Fetch worked hours for the selected employee
    $sql = "SELECT date, time_in, time_out, total_hours FROM worked_hours WHERE employee_name = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo "Error preparing query: " . $conn->error;
        exit();
    }

    $stmt->bind_param("s", $employeeName);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if there are records
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['date']}</td>
                    <td>{$row['time_in']}</td>
                    <td>{$row['time_out']}</td>
                    <td>{$row['total_hours']}</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No records found</td></tr>";
    }

    $stmt->close();
    $conn->close();
    exit();
}

// Check if we are adding a new record (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['addemployeeName'], $_POST['department'])) {
        $employeeName = $_POST['addemployeeName'];
        $department = $_POST['department'];

        // Prepare the insert query (update table name accordingly)
        $query = "INSERT INTO worked_hours (employee_name, department) VALUES (?, ?)";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
            exit();
        }

        $stmt->bind_param('ss', $employeeName, $department);

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Employee added successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add employee: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Required data missing.']);
    }

    // Close the database connection at the end of the script
    $conn->close();
    exit();
}

// Fetch all employees (GET request without employee_name)
$sql = "SELECT employee_name, department FROM worked_hours GROUP BY employee_name, department";
$result = $conn->query($sql);

$employees = array();
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}

echo json_encode($employees);

$conn->close();
