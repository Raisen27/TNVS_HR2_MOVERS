<?php
// include the database connection
include_once 'config.php';
// call the class and method
global $conn;
class Onboarding_status
{

    // create property
    public $conn;

    //creating constructor for conn
    public function __construct($conn)
    {
        $this->conn = $conn = ConnectionDb::DbConnection();
    }

    public function DisplayOnboarding()
    {

        // Query to fetch onboarding data
        $result = $this->conn->query("SELECT * FROM onboarding_status WHERE Archive_status = '!ACTIVE'");

        // Debug: Check if the query was successful
        if ($result === false) {
            echo "<script>alert('Error executing query: " . $this->conn->error . "');</script>";
            return;
        }

        // Check if records are found
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Escape output for security
                $name = htmlspecialchars($row['name']);
                $interviewStatus = htmlspecialchars($row['interview_status']);
                $applicationStatus = htmlspecialchars($row['application_status']);
                $onboardingStatus = htmlspecialchars($row['onboarding_status']);
                $id = htmlspecialchars($row['id']); // Escape the ID as well

                echo <<<HTML
                <tr>
                    <td>$name</td>
                    <td>$interviewStatus</td>
                    <td>$applicationStatus</td>
                    <td>$onboardingStatus</td>
                    <td>
                        <div>
                            <button data-id="$id" style="background-color: #a6c4e5" class="Update_btn">Update</button>
                            <button data-id="$id" style="background-color: #a6c4e5" class="Archive_btn">Archive</button>
                        </div>
                    </td>
                </tr>
            HTML;
            }
        } else {
            echo "<script>alert('No Onboarding Data Available');</script>";
        }
    }





    // View update method

    public function ViewUpdate($id)
    {
        $query = "SELECT * FROM onboarding_status WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            echo '
            
     <div id="updateModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Update Onboarding Details</h2>
            <p>Please enter the new details below:</p>
            <div style="display: flex; flex-direction: column; gap: 10px">
                <select id="interview_status">
                    <option selected value="' . $row['interview_status'] . '">Select interview Status( ' . $row['interview_status'] . ')</option>
                    <option value="INITIAL INTERVIEW">INITIAL INTERVIEW</option>
                    <option value="FINAL INTERVIEW">FINAL INTERVIEW</option>
                </select>
                <input id="view_status" value="' . $row['interview_status'] . '" style="display: none" type="text">
                <select id="application_status">
                    <option selected value="' . $row['application_status'] . '">Select application status( ' . $row['application_status'] . ')</option>
                    <option value="ON PROCESS">ON PROCESS</option>
                    <option value="TO BE CALL">TO BE CALL</option>
                    <option value="HIRED">HIRED</option>
                    <option value="FAILED">FAILED</option>
                </select>
                 <input id="app_status" value="' . $row['application_status'] . '" style="display: none" type="text">
                <select id="onboarding_status">
                    <option selected value="' . $row['onboarding_status'] . '">Select Job Status( ' . $row['onboarding_status'] . ')</option>
                    <option value="SELECT">SELECT</option>
                    <option value="PRE-BOARDING">PRE-BOARDING</option>
                    <option value="ORIENTATION">ORIENTATION</option>
                    <option value="TRAINING">TRAINING</option>
                    <option value="TRANSITION">TRANSITION</option>
                </select>
                <input id="onboard_status" value="' . $row['onboarding_status'] . '" style="display: none" type="text">
                <button  value=" ' . $id . '" style="border-radius: 0; font-weight: bold; padding: 10px" id="Save_button">Save</button>
            </div>
            <br><br>
        </div>
    </div>
            ';
        }
    }

    // method for saving updated onboard
    public function SavingOnboard($interview_status, $application_status, $onboarding_status, $id)
    {
        $query = "UPDATE onboarding_status SET interview_status = ?, application_status = ?, onboarding_status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sssi', $interview_status, $application_status, $onboarding_status, $id);

        if ($stmt->execute()) {
            // Check if the onboarding_status is 'TRAINING'
            if ($onboarding_status === 'TRAINING') {
                // Fetch the name column
                $nameQuery = "SELECT name FROM onboarding_status WHERE id = ?";
                $nameStmt = $this->conn->prepare($nameQuery);
                $nameStmt->bind_param('i', $id);
                $nameStmt->execute();
                $nameResult = $nameStmt->get_result();
                $nameRow = $nameResult->fetch_assoc();

                if ($nameRow) {
                    $name = $nameRow['name'];

                    // Fetch all training names from the training_program table
                    $trainingNamesQuery = "SELECT training_name FROM training_program";
                    $trainingNamesStmt = $this->conn->prepare($trainingNamesQuery);
                    $trainingNamesStmt->execute();
                    $trainingNamesResult = $trainingNamesStmt->get_result();

                    // Insert into training_schedule for each training name
                    while ($trainingRow = $trainingNamesResult->fetch_assoc()) {
                        $training_name = $trainingRow['training_name'];

                        // Insert the name and training program into the training_schedule table
                        $insertQuery = "INSERT INTO training_schedule (name, training_program) VALUES (?, ?)";
                        $insertStmt = $this->conn->prepare($insertQuery);
                        $insertStmt->bind_param('ss', $name, $training_name);

                        if (!$insertStmt->execute()) {
                            // Handle insertion failure (optional)
                            echo json_encode(['success' => false, 'message' => 'Failed to insert into training_schedule']);
                            return;
                        }
                    }
                }
            }
            echo json_encode(['success' => true, 'message' => 'Successfully Updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Update Failed']);
        }
    }



    // method for archive

    public function Archive_Onboard($id)
    {
        // Check if the connection is valid
        if ($this->conn) {
            // Prepare the query to check the current Archive_Status
            $checkQuery = "SELECT Archive_Status FROM onboarding_status WHERE id = ?";
            $checkStmt = $this->conn->prepare($checkQuery);

            if ($checkStmt === false) {
                echo json_encode(['success' => false, 'message' => 'Check query preparation failed: ' . $this->conn->error]);
                return;
            }

            $checkStmt->bind_param('i', $id);

            // Execute the check statement
            if ($checkStmt->execute()) {
                $result = $checkStmt->get_result();
                if ($result->num_rows === 0) {
                    echo json_encode(['success' => false, 'message' => 'Record not found.']);
                    return;
                }

                $row = $result->fetch_assoc();
                $currentStatus = $row['Archive_Status'];

                // If the record is already active, return a message
                if ($currentStatus === 'Active') {
                    echo json_encode(['success' => false, 'message' => 'Record is already active.']);
                    return;
                }

                // Prepare the query to update the Archive_Status to 'Active'
                $updateQuery = "UPDATE onboarding_status SET Archive_Status = 'Active' WHERE id = ?";
                $stmt = $this->conn->prepare($updateQuery);

                if ($stmt === false) {
                    echo json_encode(['success' => false, 'message' => 'Update query preparation failed: ' . $this->conn->error]);
                    return;
                }

                // Bind the parameter
                $stmt->bind_param('i', $id);

                // Execute the statement
                if ($stmt->execute()) {
                    // Check if any rows were affected
                    if ($stmt->affected_rows > 0) {
                        echo json_encode(['success' => true, 'message' => 'Successfully Archived']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'No changes made. Record may already be active.']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to execute update: ' . $stmt->error]);
                }

                // Close the statement to free resources
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to execute check: ' . $checkStmt->error]);
            }

            // Close the check statement
            $checkStmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        }
    }
};

// Saving Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $viewUpdate = new Onboarding_status($conn);
    $viewUpdate->ViewUpdate($id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Save_id'])) {
    $id = $_POST['Save_id'];
    $interview_status = $_POST['interview_status'];
    $application_status = $_POST['application_status'];
    $onboarding_status = $_POST['onboarding_status'];

    $Save_updated = new Onboarding_status($conn);
    $Save_updated->SavingOnboard($interview_status, $application_status, $onboarding_status, $id);
}

// handle the delete functionality
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['delete_Id'])) {
    $Delete_fun = new Onboarding_status($conn);
    $status_de = "is_deleted";
    $delete_Id = $_POST['delete_Id'];
}
