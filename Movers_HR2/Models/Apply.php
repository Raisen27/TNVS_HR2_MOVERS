<?php
// Ensure no output before this line


include_once 'config.php';

global $conn;

class Apply
{
    public $conn;

    // Constructor to initialize database connection
    public function __construct()
    {
        $this->conn = ConnectionDb::DbConnection();
    }

    // Method to archive an applicant
    public function ArchiveApplicant($id)
    {
        // Update the status of the applicant to 'ARCHIVED'
        $query = "UPDATE screening_selection SET archive_status = 'ACTIVE' WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Applicant successfully archived']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to archive applicant']);
        }

        $stmt->close();
    }

    // Method for inserting applicant information
    public function Applicant_Information($name, $age, $Email, $SelectedJob, $resume)
    {
        $query = "INSERT INTO screening_selection(name, applied_position, age, email, document_path,archive_status) VALUES (?,?,?,?,?,?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssiss', $name, $SelectedJob, $age, $Email, $resume, '!ACTIVE');

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Thank You for Applying']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed To apply']);
        }

        $stmt->close();
    }

    // Method to display screening list (applicants)
    public function DisplayList_screening()
    {
        $result = $this->conn->query("SELECT * FROM screening_selection WHERE archive_status != 'ACTIVE'");
        while ($row = $result->fetch_assoc()) {
            echo "
              <tr>
              <td>${row['name']}</td>
              <td>${row['applied_position']}</td>
              <td>${row['age']}</td>
              <td>${row['email']}</td>
              <td>
               <button class='viewPdf' id='btn_view' value='{$row['document_path']}' style='border: solid #3d52a0; padding: 5px; text-decoration: none; background-color: #3d52a0; color: white;'>Open</button>
              </td>
              <td>${row['Status']}</td>
              <td>";

            if ($row['Status'] === 'ONGOING') {
                echo "
                    <a href='onboarding-status.php?id={$row['id']}' style='border: solid #3d52a0; padding: 5px; background-color: #3d52a0; color: white; text-decoration: none;'>View</a>
                    <button class='btn_archive' data-name='{$row['name']}' data-id='{$row['id']}' style='border: solid green; padding: 5px; background-color: green; color: white;'>Archive</button>
                ";
            } else {
                echo "
                    <button class='btn_accept' data-name='{$row['name']}' data-id='{$row['id']}' style='border: solid green; padding: 5px; background-color: green; color: white;'>Accept</button>
                    <button class='btn_reject' data-name='{$row['name']}' data-id='{$row['id']}' style='border: solid red; padding: 5px; background-color: red; color: white;'>Reject</button>
                ";
            }

            echo "</td></tr>";
        }
    }

    // Method to view applicant PDF (resume)
    public function ViewPdf($id)
    {
        $result = $this->conn->query("SELECT document_path FROM screening_selection WHERE id = $id");

        if ($row = $result->fetch_assoc()) {
            $documentPath = $row['document_path'];
            echo "<embed src='../Models/Upload_resume/$documentPath' width='50%' height='300px' type='application/pdf'>";
        } else {
            echo "No document found for the provided ID.";
        }
    }
}

// Handling POST request for applicant information submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize variables
    $jobTitle = $_POST['SelectedJob'] ?? '';
    $Name = $_POST['Name'];
    $Age = $_POST['Age'] ?? 0;
    $Email = $_POST['Email'];
    $Contact = $_POST['Contact_number'];
    $Resume = $_FILES['resume']['name'] ?? '';

    $errors = []; // Array to hold error messages

    // Validate required fields
    if (empty($Name)) {
        $errors['Name'] = 'Please enter your name';
    }
    if (empty($Age)) {
        $errors['Age'] = 'Please enter your age';
    }
    if (empty($Email)) {
        $errors['Email'] = 'Please enter your email';
    } elseif (!filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        $errors['Email'] = 'Please enter a valid email address';
    }
    if (empty($jobTitle) || $jobTitle === 'Select Job Position') {
        $errors['Job'] = 'Please select a job title';
    }
    if (empty($Resume)) {
        $errors['Resume'] = 'Please upload your resume';
    }
    if (empty($Contact)) {
        $errors['Contact'] = 'Please enter your contact number';
    }

    // Check if there are any errors
    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'errors' => $errors]);
        exit; // Stop further execution if there are errors
    }

    // Handle the resume upload
    $uploadDir = './Upload_resume/';
    if (move_uploaded_file($_FILES['resume']['tmp_name'], $uploadDir . $Resume)) {
        // Create an instance of the Apply class and insert data
        $Applicant = new Apply();
        $Applicant->Applicant_Information($Name, $Age, $Email, $jobTitle, $Resume);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload resume']);
    }
}

// Handling POST request for archiving an applicant
