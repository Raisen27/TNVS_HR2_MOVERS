<?php

include_once 'config.php';

class JobOffer
{
    private $conn;

    // Constructor to initialize the database connection
    public function __construct()
    {
        $this->conn = ConnectionDb::DbConnection();
    }

    // Method to display the job offer list
    public function DisplayJob_offer()
    {
        if (isset($_POST['search_query']) && !empty($_POST['search_query'])) {
            $this->searchJobOffer($_POST['search_query']);
        } else {
            $result = $this->conn->query("SELECT * FROM job_offers WHERE Delete_Status = 'Active'");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr><td>" . htmlspecialchars($row['status']) . "</td>
                          <td>" . htmlspecialchars($row['job_name']) . "</td>
                          <td>
                             <button id='btn_update' value='" . $row['id'] . "' class='edit'>Update</button>
                             <button id='delete_btn' value='" . $row['id'] . "' class='delete'>Delete</button>
                          </td></tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No Data Available</td></tr>";
            }
        }
    }

    // Method to delete a job offer
    public function Delete_job_offer($id)
    {
        $query = "UPDATE job_offers SET Delete_Status = 'is_deleted' WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Job offer successfully deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete']);
        }
        $stmt->close();
    }

    // Method to view and update a job offer
    public function ViewUpdate($id)
    {
        $query = "SELECT * FROM job_offers WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo '
            <div id="updateModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Update Job Offer</h2>
                    <p>Please enter the new details below:</p>
                    <div style="display: flex; flex-direction: column; gap: 10px">
                        <input type="text" value="' . htmlspecialchars($row['job_name']) . '" id="updateInput" placeholder="Enter new job details" required>
                        <select id="Job_offer_status">
                            <option selected value="' . $row['status'] . '">Select Job Status (' . $row['status'] . ')</option>
                            <option value="CLOSE">CLOSE</option>
                            <option value="OPEN">OPEN</option>
                        </select>
                        <button value="' . $id . '" style="border-radius: 0; font-weight: bold; padding: 10px" id="confirmUpdate">Save</button>
                    </div>
                    <br><br>
                </div>
            </div>';
        }
        $stmt->close();
    }

    // Method to save (update) job offer details
    public function SavingJobOffer($job_name, $status, $id)
    {
        $query = "UPDATE job_offers SET job_name = ?, status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssi', $job_name, $status, $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Successfully Updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Update Failed']);
        }
        $stmt->close();
    }


    // Method to fetch job names for a dropdown list
    public function GeJobName()
    {
        // Modify the query to select job offers where status is 'OPEN' and Delete_Status is not 'is_deleted'
        $result = $this->conn->query("SELECT job_name FROM job_offers WHERE status = 'OPEN' AND Delete_Status != 'is_deleted'");

        // Check if the query returns any rows
        if ($result->num_rows > 0) {
            // Loop through the result set and output each job name as an option
            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . htmlspecialchars($row['job_name']) . '">' . htmlspecialchars($row['job_name']) . '</option>';
            }
        } else {
            // Optionally handle the case where no results are found
            echo '<option value="">No job offers available</option>';
        }
    }
}

// Handle requests for deletion, update, save, and add job offers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobOffer = new JobOffer();

    // Handle delete request
    if (isset($_POST['id'])) {
        $jobOffer->Delete_job_offer($_POST['id']);
    }

    // Handle view/update request
    if (isset($_POST['Job_id'])) {
        $jobOffer->ViewUpdate($_POST['Job_id']);
    }

    // Handle saving updated job offer
    if (isset($_POST['Job_id_save'])) {
        $jobOffer->SavingJobOffer($_POST['job_name'], $_POST['Save_status'], $_POST['Job_id_save']);
    }
}
