<?php
include 'config.php';
$conn = new ConnectionDb();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    // Query to fetch data from `training_schedule`
    $fetch_sql = "SELECT * FROM training_schedule WHERE id = ?";
    $fetch_stmt = $conn->DbConnection()->prepare($fetch_sql);

    if (!$fetch_stmt) {
        die("Error in fetch SQL preparation: " . $conn->DbConnection()->error);
    }

    $fetch_stmt->bind_param("i", $id);
    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Query to insert data into `planned_schedule` with `status` as 'Planned'
        $insert_sql = "INSERT INTO training_status (name, position, training_program, evaluator, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, 'Planned')";
        $stmt = $conn->DbConnection()->prepare($insert_sql);

        if (!$stmt) {
            die("Error in insert SQL preparation: " . $conn->DbConnection()->error);
        }

        $stmt->bind_param(
            "ssssss",
            $row['name'],
            $row['position'],
            $row['training_program'],
            $row['evaluator'],
            $row['starting_date'],
            $row['end_date']
        );

        if ($stmt->execute()) {
            // Now delete the record from `training_schedule`
            $delete_sql = "DELETE FROM training_schedule WHERE id = ?";
            $delete_stmt = $conn->DbConnection()->prepare($delete_sql);

            if (!$delete_stmt) {
                die("Error in delete SQL preparation: " . $conn->DbConnection()->error);
            }

            $delete_stmt->bind_param("i", $id);

            if ($delete_stmt->execute()) {
                echo "Record moved successfully!";
            } else {
                echo "Failed to delete record: " . $delete_stmt->error;
            }
        } else {
            echo "Failed to move record: " . $stmt->error;
        }
    } else {
        echo "No record found with ID: " . $id;
    }
} else {
    echo "Invalid request method.";
}
