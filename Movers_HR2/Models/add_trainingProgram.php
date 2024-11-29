<?php
include 'config.php';
$conn = new ConnectionDb();



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $trainingName = $_POST['trainingName'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $place = $_POST['place'];

    $query = "INSERT INTO training_program (training_name, date, time, place) VALUES (?, ?, ?, ?)";
    $stmt = $conn->DbConnection()->prepare($query);

    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->DbConnection()->error]);
        exit();
    }

    $stmt->bind_param('ssss', $trainingName, $date, $time, $place);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Program added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to Add Program: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->DbConnection()->close();
}
