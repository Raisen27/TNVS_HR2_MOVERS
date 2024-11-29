<?php
include 'config.php';
class training_schedule
{
    public $conn;
    public function __construct($conn)
    {
        $this->conn = $conn = ConnectionDb::DbConnection();
    }

    public function DisplaySchedule()
    {
        $result = $this->conn->query("SELECT * FROM training_schedule");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                echo '<td>' . htmlspecialchars($row['position']) . '</td>';
                echo '<td>' . htmlspecialchars($row['training_program']) . '</td>';
                echo '<td>' . htmlspecialchars($row['evaluator']) . '</td>';
                echo '<td>' . htmlspecialchars($row['starting_date']) . '</td>';
                echo '<td>' . htmlspecialchars($row['end_date']) . '</td>';
                echo "<td>
                      <button value='${row['id']}' style='background-color: #a6c4e5' id='Update_btn'>Update</button>
                      <button value='${row['id']}' style='background-color: #a6c4e5' id='delete_button_'>Delete</button>
                      <button class='move-btn' data-id='${row['id']}' style='background-color: #a6c4e5'>Move</button>
                     </td>";
                echo '</tr>';
            }
        } else {
            echo '<p>No training schedule found.</p>';
        }
    }



    public function GetScheduleById($id)
    {
        $query = "SELECT * FROM training_schedule WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Display the modal with the schedule details for editing
            echo '
        <div id="scheduleModal_' . $id . '" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal(' . $id . ')">&times;</span>
                <h2>Edit Schedule</h2>
                <div style="display: flex; flex-direction: column; gap: 10px">
                    <label for="name_' . $id . '">Name</label>
                    <input type="text" id="name_' . $id . '" value="' . htmlspecialchars($row['name']) . '" required>

                    <label for="position_' . $id . '">Position</label>
                    <input type="text" id="position_' . $id . '" value="' . htmlspecialchars($row['position']) . '" required>

                    <label for="training_program_' . $id . '">Training Program</label>
                    <input type="text" id="training_program_' . $id . '" value="' . htmlspecialchars($row['training_program']) . '" required>

                    <label for="evaluator_' . $id . '">Evaluator</label>
                    <input type="text" id="evaluator_' . $id . '" value="' . htmlspecialchars($row['evaluator']) . '" required>

                    <label for="starting_date_' . $id . '">Starting Date</label>
                    <input type="date" id="starting_date_' . $id . '" value="' . htmlspecialchars($row['starting_date']) . '" required>

                    <label for="end_date_' . $id . '">End Date</label>
                    <input type="date" id="end_date_' . $id . '" value="' . htmlspecialchars($row['end_date']) . '" required>

                    <button value="' . $id . '" class="save_button" style="border-radius: 0; font-weight: bold; padding: 10px">SAVE</button>
                </div>
            </div>
        </div>
        ';
        } else {
            echo 'No schedule found for the given ID.';
        }

        $stmt->close();
    }

    public function SaveSchedule($id, $name, $position, $training_program, $evaluator, $starting_date, $end_date)
    {
        $query = "UPDATE training_schedule SET name = ?, position = ?, training_program = ?, evaluator = ?, starting_date = ?, end_date = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssssssi', $name, $position, $training_program, $evaluator, $starting_date, $end_date, $id);

        if ($stmt->execute()) {
            echo 'Schedule updated successfully.';
        } else {
            echo 'Error updating schedule: ' . $stmt->error;
        }

        $stmt->close();
    }

    public function DeleteSchedule($id)
    {
        $query = "DELETE FROM training_schedule WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo "Schedule deleted successfully.";
        } else {
            echo "Error deleting schedule: " . $stmt->error;
        }

        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $conn;
    $schedule = new training_schedule($conn);
    switch ($_POST['action']) {
        case 'get_schedule':
            $id = $_POST['id'];
            $schedule->GetScheduleById($id);
            break;
        case 'save_schedule':
            // Retrieve the parameters from the POST request
            $id = $_POST['id'];
            $name = $_POST['name'];
            $position = $_POST['position'];
            $training_program = $_POST['training_program'];
            $evaluator = $_POST['evaluator'];
            $starting_date = $_POST['starting_date'];
            $end_date = $_POST['end_date'];

            // Call the SaveSchedule method with the retrieved parameters
            $schedule->SaveSchedule($id, $name, $position, $training_program, $evaluator, $starting_date, $end_date);
            break;

        case 'delete_schedule':
            $id = $_POST['id'];
            $schedule->DeleteSchedule($id);
            break;
    }
}
