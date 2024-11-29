<?php
// Include database connection
include('config.php'); // Make sure to include your database connection script

$conn = new ConnectionDb();

// Check if the ID is set and valid
if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id = $_POST['id'];

    // Sanitize the input to prevent SQL injection
    $id = mysqli_real_escape_string($conn->DbConnection(), $id);

    // Perform the update query to mark the record as archived in the onboarding_status table
    $query1 = "UPDATE onboarding_status SET Archive_status = 'ACTIVE' WHERE id = '$id'";

    // Perform the update query to mark the record as archived in the screening_selection table
    $query2 = "UPDATE screening_selection SET archive_status = 'ACTIVE' WHERE id = '$id'";

    $query3 = "UPDATE training_performance SET archive_status = 'ACTIVE' WHERE id = '$id'";

    // Begin a transaction to ensure both queries execute successfully
    mysqli_begin_transaction($conn->DbConnection());

    try {
        // Execute the first query (onboarding_status update)
        if (!mysqli_query($conn->DbConnection(), $query1)) {
            throw new Exception("Failed to update onboarding_status");
        }

        // Execute the second query (screening_selection update)
        if (!mysqli_query($conn->DbConnection(), $query2)) {
            throw new Exception("Failed to update screening_selection");
        }

        // Execute the 3rd query (training_performance update)
        if (!mysqli_query($conn->DbConnection(), $query3)) {
            throw new Exception("Failed to update screening_selection");
        }

        // Commit the transaction if both queries were successful
        mysqli_commit($conn->DbConnection());

        // Success: Send a response back to the AJAX call
        echo json_encode(['message' => 'Record archived successfully in both tables.']);
    } catch (Exception $e) {
        // If an error occurs, rollback the transaction
        mysqli_roll_back($conn->DbConnection());

        // Failure: Send a response with error message
        echo json_encode(['message' => 'Failed to archive the record. Please try again. Error: ' . $e->getMessage()]);
    }

    // Close the database connection
    mysqli_close($conn->DbConnection());
} else {
    // Invalid request, send an error response
    echo json_encode(['message' => 'Invalid ID.']);
}
