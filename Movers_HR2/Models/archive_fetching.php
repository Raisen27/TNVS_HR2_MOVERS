<?php
header('Content-Type: archive/json');
echo json_encode($responseData); // Replace $responseData with your actual data variable
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';

$conn = new ConnectionDb();

// Fetch screening and selection archive
$sqlscreening = "SELECT * FROM screening_selection WHERE is_archive = '1'";
$resultscreening = $conn->DbConnection()->query($sqlscreening);

if (!$resultscreening) {
    die(json_encode(["error" => "Error fetching screening_selection: " . $conn->DbConnection()->error]));
}

$screening = [];
if ($resultscreening->num_rows > 0) {
    while ($row = $resultscreening->fetch_assoc()) {
        $screening[] = [
            "name" => $row['name'],
            "applied_position" => $row['applied_position'],
            "age" => $row['age'],
            "email" => $row['email'],
            "resume" => $row['resume'],
            "status" => $row['status'],
        ];
    }
}

// Fetch onboarding status
$sqlonboarding = "SELECT * FROM onboarding_status WHERE Archive_status = 'ACTIVE'";
$resultonboarding = $conn->DbConnection()->query($sqlonboarding);

if (!$resultonboarding) {
    die(json_encode(["error" => "Error fetching onboarding: " . $conn->DbConnection()->error]));
}

$onboarding = [];
if ($resultonboarding->num_rows > 0) {
    while ($row = $resultonboarding->fetch_assoc()) {
        $onboarding[] = [
            "name" => $row['name'],
            "interview_status" => $row['interview_status'],
            "application_status" => $row['application_status'],
            "onboarding_status" => $row['onboarding_status'],
        ];
    }
}

// Fetch training performance
$sqlperformance = "SELECT * FROM training_performance WHERE is_archive = '1'";
$resultperformance = $conn->DbConnection()->query($sqlperformance);

if (!$resultperformance) {
    die(json_encode(["error" => "Error fetching training performance: " . $conn->DbConnection()->error]));
}

$performance = [];
if ($resultperformance->num_rows > 0) {
    while ($row = $resultperformance->fetch_assoc()) {
        $performance[] = [
            "employee_name" => $row['employee_name'],
            "training_program" => $row['training_program'],
            "evaluator" => $row['evaluator'],
            "development" => $row['development'],
            "date_given" => $row['date_given'],
            "remarks" => $row['remarks'],
        ];
    }
}

// Return all data as JSON
echo json_encode([
    "screening" => $screening,
    "onboarding" => $onboarding,
    "performance" => $performance
]);

// Close the database connection
$conn->DbConnection()->close();
