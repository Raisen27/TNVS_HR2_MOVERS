<?php
include 'config.php';
$conn = new ConnectionDb();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employeeName = $_POST['employeeName'] ?? null;
    $position = $_POST['position'] ?? null;
    $t_program = $_POST['t_program'] ?? null;
    $evaluator = $_POST['evaluator'] ?? null;
    $date = $_POST['date'] ?? null;

    // Validate inputs
    if (!$employeeName || !$t_program || !$date) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    // Prepare the SQL statement to insert data
    $query = "INSERT INTO training_status (name, position, training_program, evaluator, start_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->DbConnection()->prepare($query);

    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->DbConnection()->error]);
        exit();
    }

    $stmt->bind_param('sssss', $employeeName, $position, $t_program, $evaluator, $date);

    // Execute the query and return the result
    if ($stmt->execute()) {
        // Get the ID of the inserted record
        $new_id = $stmt->insert_id;
        echo json_encode([
            'status' => 'success',
            'message' => 'Employee added successfully.',
            'data' => [
                'id' => $new_id,
                'employeeName' => $employeeName,
                'position' => $position,
                't_program' => $t_program,
                'evaluator' => $evaluator,
                'date' => $date,
                'status' => 'Pending'
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add employee: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->DbConnection()->close();
}
