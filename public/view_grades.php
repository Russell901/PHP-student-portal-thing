<?php
// Connect to database
$conn = new mysqli('localhost', 'username', 'password', 'database');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Example: Viewing grades for StudentID = 1
$studentID = 1;

echo "<h2>Grades for Student ID: $studentID</h2>";

// Query to get continuous assessment grades
$sqlCA = "SELECT m.ModuleName, ca.AssessmentType, ca.Score, ca.Weight
          FROM ContinuousAssessment ca
          JOIN Modules m ON ca.ModuleID = m.ModuleID
          WHERE ca.StudentID = $studentID";
$resultCA = $conn->query($sqlCA);

echo "<h3>Continuous Assessment Grades</h3>";
if ($resultCA->num_rows > 0) {
    echo "<table border='1'><tr><th>Module</th><th>Assessment</th><th>Score</th><th>Weight</th></tr>";
    while($row = $resultCA->fetch_assoc()) {
        echo "<tr><td>" . $row["ModuleName"] . "</td><td>" . $row["AssessmentType"] . "</td><td>" . $row["Score"] . "</td><td>" . $row["Weight"] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "No assessments found.";
}

// Query to get final exam grades
$sqlFE = "SELECT m.ModuleName, fe.FinalExamGrade
          FROM FinalExam fe
          JOIN Modules m ON fe.ModuleID = m.ModuleID
          WHERE fe.StudentID = $studentID";
$resultFE = $conn->query($sqlFE);

echo "<h3>Final Exam Grades</h3>";
if ($resultFE->num_rows > 0) {
    echo "<table border='1'><tr><th>Module</th><th>Final Exam Grade</th></tr>";
    while($row = $resultFE->fetch_assoc()) {
        echo "<tr><td>" . $row["ModuleName"] . "</td><td>" . $row["FinalExamGrade"] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "No final exam grades found.";
}

$conn->close();
?>
