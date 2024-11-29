<?php
include 'config.php';
class training_performance
{
    public $conn;

    public  function __construct($conn)
    {
        $this->conn = ConnectionDb::DbConnection();
    }

    // display training program
    public function DisplayProgram()
    {
        $result = $this->conn->query("SELECT * FROM training_performance where archive_status = '!ACTIVE'");

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['employee_name'] . "</td>";
                echo "<td>" . $row['training_program'] . "</td>";
                echo "<td>" . $row['evaluator'] . "</td>";
                echo "<td>" . $row['development'] . "</td>";
                echo "<td>" . $row['date_given'] . "</td>";
                echo "<td>" . $row['remarks'] . "</td>";
                echo "<td>
                      <button value='${row['id']}' style='background-color: #a6c4e5' id='Update_btn'>Update</button>
                        <button value='${row['id']}'  style='background-color: #a6c4e5' id='delete_button_'>Delete</button>
                        <button data-id='${row['id']}'  style='background-color: #a6c4e5' id='archive_button_'>Archive</button>
                     </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='2'>No records found</td></tr>";
        }
    }


    public function ViewUpdate($id)
    {
        $query = "SELECT * FROM training_performance WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo '
        <div id="updateModal_' . $id . '" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal(' . $id . ')">&times;</span>
                <h2>Update Employee Training Data</h2>
                <p>Please enter the new details below:</p>
                <div style="display: flex; flex-direction: column; gap: 10px">
            <!-- Employee Name -->
                <label for="training_name">Employee Name</label>
                <input type="text" id="employee_name_' . $id . '" value="' . $row['employee_name'] . '" required>

                    <!-- training_program -->
                    <label for="training_program">Training Program</label>
                    <input type="text" id="training_program_' . $id . '" value="' . $row['training_program'] . '" required>

                    <!-- evaluator -->
                    <label for="evaluator">Evaluator</label>
                    <input type="text" id="evaluator_' . $id . '" value="' . $row['evaluator'] . '" required>

                    <!-- development -->
                    <label for="development">Development</label>
                    <input type="text" id="development_' . $id . '" value="' . $row['development'] . '" required>

                     <!-- date_given -->
                    <label for="date_given">Date Given</label>
                    <input type="date" id="date_given_' . $id . '" value="' . $row['date_given'] . '" required>

                     <!-- remarks -->
                    <label for="remarks">Remarks</label>
                    <input type="text" id="remarks_' . $id . '" value="' . $row['remarks'] . '" required>
                   

                    <!-- Save Button -->
                    <button value="' . $id . '" style="border-radius: 0; font-weight: bold; padding: 10px" id="Save_button_">Save</button>
                </div>
            </div>
        </div>
        ';
        } else {
            echo 'No record found for the given ID.';
        }
    }


    // Method to save updated training program
    public function UpdatePerformance($id, $employee_name, $training_program, $evaluator, $development, $date_given, $remarks)
    {
        $query = "UPDATE training_performance SET employee_name = ?, training_program = ?, evaluator = ?, development = ?, date_given = ?, remarks = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssssssi', $employee_name, $training_program, $evaluator, $development, $date_given, $remarks, $id);

        if ($stmt->execute()) {
            return true; // Update successful
        } else {
            return false; // Update failed
        }
    }



    //delete
    public function deletetrainingPerformance($id)
    {
        $query = "DELETE FROM training_performance WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            return "Employee training data deleted successfully.";
        } else {
            return "Error deleting Employee training data: " . $this->conn->error;
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $conn;
    $program = new training_performance($conn);
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update':
                $id = $_POST['id'];
                $program->ViewUpdate($id);
                break;

            case 'save_update':
                $id = $_POST['id'];
                $employee_name = $_POST['employee_name'];
                $training_program = $_POST['training_program'];
                $evaluator = $_POST['evaluator'];
                $development = $_POST['development'];
                $date_given = $_POST['date_given'];
                $remarks = $_POST['remarks'];

                if ($program->UpdatePerformance($id, $employee_name, $training_program, $evaluator, $development, $date_given, $remarks)) {
                    echo 'Update successful';
                } else {
                    echo 'Update failed';
                }
                break;

            case 'delete_training_performance':
                $id = $_POST['id'];
                $res = $program->deletetrainingPerformance($id);
                echo $res;
        }
    }
}
