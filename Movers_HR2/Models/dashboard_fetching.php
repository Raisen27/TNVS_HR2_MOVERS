<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1); // Show errors on the screen

// Database connection parameters
include 'config.php';
$conn = new ConnectionDb();


// Fetch job offers
$sqlJobOffers = "SELECT job_name FROM job_offers WHERE Delete_status = 'Active'";
$resultJobOffers = $conn->DbConnection()->query($sqlJobOffers);

if (!$resultJobOffers) {
    die(json_encode(["error" => "Error fetching job offers: " . $conn->DbConnection()->error]));
}

$jobOffers = [];
if ($resultJobOffers->num_rows > 0) {
    while ($row = $resultJobOffers->fetch_assoc()) {
        $jobOffers[] = $row['job_name'];
    }
}

// Fetch applicants
$sqlApplicants = "SELECT name, applied_position FROM screening_selection";
$resultApplicants = $conn->DbConnection()->query($sqlApplicants);

if (!$resultApplicants) {
    die(json_encode(["error" => "Error fetching applicants: " . $conn->DbConnection()->error]));
}

$applicants = [];
if ($resultApplicants->num_rows > 0) {
    while ($row = $resultApplicants->fetch_assoc()) {
        $applicants[] = [
            "name" => $row['name'],
            "position" => $row['applied_position']
        ];
    }
}

// Fetch hired applicants
$sqlHiredApplicants = "SELECT name FROM onboarding_status WHERE application_status = 'HIRED'";
$resultHiredApplicants = $conn->DbConnection()->query($sqlHiredApplicants);

if (!$resultHiredApplicants) {
    die(json_encode(["error" => "Error fetching hired applicants: " . $conn->DbConnection()->error]));
}

$hiredApplicants = [];
if ($resultHiredApplicants->num_rows > 0) {
    while ($row = $resultHiredApplicants->fetch_assoc()) {
        $hiredApplicants[] = [
            "name" => $row['name'],

        ];
    }
}

// Fetch training employees
$sqlTrainingEmployees = "SELECT name, training_program, start_date, end_date FROM training_status WHERE status = 'ongoing'";
$resultTrainingEmployees = $conn->DbConnection()->query($sqlTrainingEmployees);

if (!$resultTrainingEmployees) {
    die(json_encode(["error" => "Error fetching training employees: " . $conn->DbConnection()->error]));
}

$trainingEmployees = [];
if ($resultTrainingEmployees->num_rows > 0) {
    while ($row = $resultTrainingEmployees->fetch_assoc()) {
        $trainingEmployees[] = [
            "name" => $row['name'],
            "training_program" => $row['training_program'],
            "start_date" => $row['start_date'],
            "end_date" => $row['end_date']
        ];
    }
}

// Return all data as JSON
echo json_encode([
    "jobOffers" => $jobOffers,
    "applicants" => $applicants,
    "hiredApplicants" => $hiredApplicants,
    "trainingEmployees" => $trainingEmployees
]);

// Close the database connection
$conn->DbConnection()->close();
