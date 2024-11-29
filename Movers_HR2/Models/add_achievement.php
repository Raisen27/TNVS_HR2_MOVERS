<?php
// add_achievement.php

// Connect to the database
include 'config.php';
$db = new ConnectionDb();
$conn = $db->DbConnection();


$employeeName = $_POST['employee_name'] ?? '';

if (!empty($employeeName)) {
    $stmt = $conn->prepare("SELECT achievement, date_given, given_by FROM achievement WHERE employee_name = ?");
    $stmt->bind_param('s', $employeeName);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if there are results
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                        <td>" . htmlspecialchars($row['achievement']) . "</td>
                        <td>" . htmlspecialchars($row['date_given']) . "</td>
                        <td>" . htmlspecialchars($row['given_by']) . "</td>
                    </tr>";
        }
    } else {
        echo "<tr><td colspan='3'>No achievements found.</td></tr>";
    }

    $stmt->close();
} else {
    echo "<tr><td colspan='3'>Invalid employee name.</td></tr>";
}

// Check if the form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $achievement = $_POST['achievement'];
    $date = $_POST['date'];
    $given = $_POST['given'];
    $id = $_POST['id']; // Get the ID to update

    // Validate the data (basic example)
    if (!empty($achievement) && !empty($date) && !empty($given) && !empty($id)) {
        // Prepare and bind
        $stmt = $conn->prepare("UPDATE achievement SET achievement = ?, date_given = ?, given_by = ? WHERE id = ?");
        $stmt->bind_param("sssi", $achievement, $date, $given, $id);

        // Execute the statement
        if ($stmt->execute()) {
            // Prepare an array with the updated achievement
            $updatedAchievement = [
                'id' => $id,
                'achievement' => $achievement,
                'date_given' => $date,
                'given_by' => $given
            ];

            // Return the updated achievement as JSON
            echo json_encode($updatedAchievement);
        } else {
            echo json_encode(["error" => "Error: " . $stmt->error]);
        }

        // Close statement
        $stmt->close();
    } else {
        echo json_encode(["error" => "Please fill in all fields."]);
    }
}

// Close the connection
$conn->close();
