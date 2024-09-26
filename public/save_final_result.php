<?php
// Connect to database
$conn = new mysqli('localhost', 'username', 'password', 'database');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$studentID = $_POST['studentID'];
$moduleID = $_POST['moduleID'];
$finalExamGrade = $_POST['finalExamGrade'];

// Insert data into FinalExam table
$sql = "INSERT INTO FinalExam (StudentID, ModuleID, FinalExamGrade)
        VALUES ('$studentID', '$moduleID', '$finalExamGrade')";

if ($conn->query($sql) === TRUE) {
    echo "Final exam grade added successfully!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
