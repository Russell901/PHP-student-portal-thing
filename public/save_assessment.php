<?php
// Connect to database
$conn = new mysqli('localhost', 'username', 'password', 'database');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$studentID = $_POST['studentID'];
$moduleID = $_POST['moduleID'];
$assessmentType = $_POST['assessmentType'];
$score = $_POST['score'];
$weight = $_POST['weight'];

// Insert data into ContinuousAssessment table
$sql = "INSERT INTO ContinuousAssessment (StudentID, ModuleID, AssessmentType, Score, Weight)
        VALUES ('$studentID', '$moduleID', '$assessmentType', '$score', '$weight')";

if ($conn->query($sql) === TRUE) {
    echo "Assessment grade added successfully!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
