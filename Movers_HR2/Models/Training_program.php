<?php
include 'config.php';
class Training_program
{
    public $conn;

    public  function __construct($conn)
    {
        $this->conn = ConnectionDb::DbConnection();
    }

    // display training program
    public function DisplayProgram()
    {
        $result = $this->conn->query("SELECT * FROM training_program");

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['training_name'] . "</td>";
                echo "<td>" . $row['date'] . "</td>";
                echo "<td>" . $row['time'] . "</td>";
                 echo "<td>" . $row['place'] . "</td>";
                echo "<td>
                      <button value='${row['id']}' style='background-color: #a6c4e5' id='Update_btn'>Update</button>
                        <button value='${row['id']}'  style='background-color: #a6c4e5' id='delete_button_'>Delete</button>
                     </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='2'>No records found</td></tr>";
        }
    }


    public function ViewUpdate($id)
    {
        $query = "SELECT * FROM training_program WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo '
        <div id="updateModal_' . $id . '" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal(' . $id . ')">&times;</span>
                <h2>Update Training Program</h2>
                <p>Please enter the new details below:</p>
                <div style="display: flex; flex-direction: column; gap: 10px">
            <!-- Training Name -->
                <label for="training_name">Training Name</label>
                <input type="text" id="training_name_' . $id . '" value="' . $row['training_name'] . '" required>

                    <!-- Date -->
                    <label for="date">Date</label>
                    <input type="date" id="date_' . $id . '" value="' . $row['date'] . '" required>

                    <!-- Time -->
                    <label for="time">Time</label>
                    <input type="time" id="time_' . $id . '" value="' . $row['time'] . '" required>

                    <!-- Place -->
                    <label for="place">Place</label>
                    <input type="text" id="place_' . $id . '" value="' . $row['place'] . '" required>

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
    public function UpdateProgram($id, $training_name, $date, $time, $place)
    {
        $query = "UPDATE training_program SET training_name = ?, date = ?, time = ?, place = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssssi', $training_name, $date, $time, $place, $id);

        if ($stmt->execute()) {
            return true; // Update successful
        } else {
            return false; // Update failed
        }
    }


    public function deleteTrainingProgram($id)
    {
        $query = "DELETE FROM training_program WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            return "Training program deleted successfully.";
        } else {
            return "Error deleting training program: " . $this->conn->error;
        }
    }

}
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    global $conn;
    $program = new Training_program($conn);
    if (isset($_POST['action'])){
        switch ($_POST['action']){
            case 'update':
                $id = $_POST['id'];
                $program->ViewUpdate($id);
                break;

            case 'save_update':
                $id = $_POST['id'];
                $training_name = $_POST['training_name'];
                $date = $_POST['date'];
                $time = $_POST['time'];
                $place = $_POST['place'];

                if ($program->UpdateProgram($id, $training_name, $date, $time, $place)) {
                    echo 'Update successful';
                } else {
                    echo 'Update failed';
                }
                break;

            case 'delete_training_program':
                $id = $_POST['id'];
              $res = $program->deleteTrainingProgram($id);
               echo $res;
        }

    }
}
