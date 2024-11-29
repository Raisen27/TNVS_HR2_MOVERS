<?php
include 'config.php';

class training_status
{
    public $conn;

    public function __construct($conn)
    {
        $this->conn = $conn = ConnectionDb::DbConnection();
    }

    public function DisplayTrainingStatus()
    {
        $result = $this->conn->query("SELECT * FROM training_status");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>"; // Assuming 'id' is a column in your table
                echo "<td>" . htmlspecialchars($row['name']) . "</td>"; // Assuming 'id' is a column in your table
                echo "<td>" . htmlspecialchars($row['position']) . "</td>";
                echo "<td>" . htmlspecialchars($row['training_program']) . "</td>"; // Assuming 'status' is a column in your table
                echo "<td>" . htmlspecialchars($row['evaluator']) . "</td>";
                echo "<td>" . htmlspecialchars($row['start_date']) . "</td>"; // Assuming 'description' is a column in your table
                echo "<td>" . htmlspecialchars($row['end_date']) . "</td>"; // Assuming 'description' is a column in your table
                echo "<td>
                      <button value='${row['id']}' style='background-color: #a6c4e5' id='Update_btn'>Update</button>
                        <button value='${row['id']}'  style='background-color: #a6c4e5' id='delete_button_'>Delete</button>
                     </td>";
                echo "</tr>";
            }
        } else {
            echo "<td></td>";
        }
    }

    public function UpdateTrainingStatus($name, $position, $training_program, $evaluator, $start_date, $endDate, $status, $id)
    {
        // Prepare the SQL statement for updating the training status
        $stmt = $this->conn->prepare("UPDATE training_status SET name = ?, position = ?, training_program = ?, evaluator = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?");

        // Bind the parameters to the SQL query
        $stmt->bind_param("sssssssi", $name, $position, $training_program, $evaluator, $start_date, $endDate, $status, $id);

        // Execute the query
        if ($stmt->execute()) {
            echo "Training status updated successfully.";

            // If the status is "COMPLETED," insert a record into another table
            if (strtoupper($status) === "COMPLETED") {
                $actions = "Follow-up"; // Example value, can be dynamic
                $development = "Employee demonstrated progress"; // Example value, can be dynamic


                // Prepare the SQL statement for the insertion
                $insertStmt = $this->conn->prepare("INSERT INTO training_performance(employee_name, training_program, evaluator, remarks, date_given, actions, development, archive_status) VALUES (?, ?, ?, 'Success', ?, ?, ?, '!ACTIVE')");

                // Bind the parameters for the insertion
                $insertStmt->bind_param("ssssss", $name, $training_program, $evaluator,  $start_date, $actions, $development,);

                // Execute the insertion query
                if ($insertStmt->execute()) {
                    echo "Employee development record inserted successfully.";
                } else {
                    echo "Error inserting employee development record: " . $insertStmt->error;
                }

                // Close the insertion statement
                $insertStmt->close();
            }
        } else {
            echo "Error updating training status: " . $stmt->error;
        }

        // Close the update statement
        $stmt->close();
    }



    public function ViewUpdate($id)
    {
        $query = "SELECT * FROM training_status WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo '
        <div id="updateModal_' . $id . '" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal(' . $id . ')">&times;</span>
                <h2>Update Training Status</h2>
                <p>Please enter the new details below:</p>
                <div style="display: flex; flex-direction: column; gap: 10px">

                    <!-- NAme -->
                    <label for="name' . $id . '">Name</label>
                    <input type="text" id="name' . $id . '" value="' . htmlspecialchars($row['name']) . '" required>

                    <!-- position -->
                    <label for="position' . $id . '">Position</label>
                    <input type="text" id="position' . $id . '" value="' . htmlspecialchars($row['position']) . '" required>
                    
                    <!-- Training Program -->
                    <label for="training_program' . $id . '">Training Program</label>
                    <input type="text" id="training_program_' . $id . '" value="' . htmlspecialchars($row['training_program']) . '" required>

                    <!-- evaluator -->
                    <label for="evaluator' . $id . '">Evaluator</label>
                    <input type="text" id="evaluator' . $id . '" value="' . htmlspecialchars($row['evaluator']) . '" required>

                    <!-- Start Date -->
                    <label for="start_date_' . $id . '">Start Date</label>
                    <input type="date" id="start_date_' . $id . '" value="' . htmlspecialchars($row['start_date']) . '" required>

                    <!-- End Date -->
                    <label for="end_date_' . $id . '">End Date</label>
                    <input type="date" id="end_date_' . $id . '" value="' . htmlspecialchars($row['end_date']) . '" required>

                    <!-- Status -->
                    <label for="status_' . $id . '">Status</label>
                    <select id="status_' . $id . '" required>
                        <option value="Planned" ' . ($row['status'] == 'Planned' ? 'selected' : '') . '>Planned</option>
                        <option value="Ongoing" ' . ($row['status'] == 'Ongoing' ? 'selected' : '') . '>Ongoing</option>
                        <option value="Completed" ' . ($row['status'] == 'Completed' ? 'selected' : '') . '>Completed</option>
                    </select>

                    <!-- Save Button -->
                    <button value="' . $id . '" style="border-radius: 0; font-weight: bold; padding: 10px" id="Save_button_' . $id . '">SAVE</button>
                </div>
            </div>
        </div>
        ';
        } else {
            echo 'No record found for the given ID.';
        }

        $stmt->close();
    }

    public function DeleteTrainingStatus($id)
    {
        // Prepare the SQL DELETE statement
        $stmt = $this->conn->prepare("DELETE FROM training_status WHERE id = ?");

        // Bind the ID parameter to the SQL query
        $stmt->bind_param('i', $id);

        // Execute the query and check if it was successful
        if ($stmt->execute()) {
            echo "Training status deleted successfully.";
        } else {
            echo "Error deleting training status: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $conn;
    $trainingStatus = new training_status($conn);
    switch ($_POST['action']) {
        case 'update':
            $id = $_POST['id'];
            $trainingStatus->ViewUpdate($id);
            break;
        case 'save_':
            // Retrieve the necessary parameters to update the record
            $id = $_POST['id'];
            $name = $_POST['name'];
            $position = $_POST['position'];
            $training_program = $_POST['training_program'];
            $evaluator = $_POST['evaluator'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $status = $_POST['status'];

            // Call the UpdateTrainingStatus method and pass the parameters
            $trainingStatus->UpdateTrainingStatus($name, $position, $training_program, $evaluator, $start_date, $end_date, $status, $id);
            break;

        default:
            echo "Invalid action.";
            break;

        case 'delete':
            $id = $_POST['id'];
            $trainingStatus->DeleteTrainingStatus($id);
            break;
    }
}
