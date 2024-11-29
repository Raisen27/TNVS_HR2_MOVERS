<?php
include 'config.php';
$conn = new ConnectionDb();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jobName = $_POST['jobName'];

    // Prepare the insert query
    $query = "INSERT INTO job_offers (job_name, Delete_status) VALUES (?, 'Active')"; // Default status set to 'Active'
    $stmt = $conn->DbConnection()->prepare($query);

    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->DbConnection()->error]);
        exit();
    }

    $stmt->bind_param('s', $jobName); // Bind only jobName since status is hardcoded

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Job added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add job: ' . $stmt->error]);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->DbConnection()->close();
}
