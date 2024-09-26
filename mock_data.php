<?php
include 'setup.php';

// Mock student data
$students = [
    ['username' => 'student1', 'password' => 'password1', 'firstName' => 'John', 'lastName' => 'Doe', 'email' => 'john@example.com', 'class' => 'A', 'enrollmentYear' => 2022],
    ['username' => 'student2', 'password' => 'password2', 'firstName' => 'Jane', 'lastName' => 'Doe', 'email' => 'jane@example.com', 'class' => 'B', 'enrollmentYear' => 2021],
    ['username' => 'student3', 'password' => 'password3', 'firstName' => 'Bob', 'lastName' => 'Smith', 'email' => 'bob@example.com', 'class' => 'A', 'enrollmentYear' => 2023],
    ['username' => 'student4', 'password' => 'password4', 'firstName' => 'Alice', 'lastName' => 'Johnson', 'email' => 'alice@example.com', 'class' => 'C', 'enrollmentYear' => 2022],
    ['username' => 'student5', 'password' => 'password5', 'firstName' => 'Tom', 'lastName' => 'Wilson', 'email' => 'tom@example.com', 'class' => 'B', 'enrollmentYear' => 2021],
    ['username' => 'student6', 'password' => 'password6', 'firstName' => 'Emma', 'lastName' => 'Davis', 'email' => 'emma@example.com', 'class' => 'C', 'enrollmentYear' => 2023],
];

// Mock module data
$modules = [
    ['moduleCode' => 'ENG101', 'moduleName' => 'English', 'creditHours' => 3],
    ['moduleCode' => 'MATH201', 'moduleName' => 'Mathematics', 'creditHours' => 4],
    ['moduleCode' => 'CS301', 'moduleName' => 'Computer Systems', 'creditHours' => 3],
    ['moduleCode' => 'NET401', 'moduleName' => 'Networking', 'creditHours' => 3],
    ['moduleCode' => 'WEB501', 'moduleName' => 'Web Development', 'creditHours' => 3],
    ['moduleCode' => 'HCI601', 'moduleName' => 'Human-Computer Interaction', 'creditHours' => 3],
];

// Insert students into Users table and Students table
foreach ($students as $student) {
    // Insert into Users table
    $stmt = $conn->prepare("INSERT INTO Users (Username, Password, Role, FirstName, LastName, Email) 
                           VALUES (:username, :password, 'student', :firstName, :lastName, :email)");
    $stmt->bindParam(':username', $student['username']);
    $stmt->bindParam(':password', $student['password']);
    $stmt->bindParam(':firstName', $student['firstName']);
    $stmt->bindParam(':lastName', $student['lastName']);
    $stmt->bindParam(':email', $student['email']);
    $stmt->execute();

    // Get the last inserted UserID
    $userId = $conn->lastInsertRowID();

    // Insert into Students table with the obtained UserID
    $stmt = $conn->prepare("INSERT INTO Students (UserID, Class, EnrollmentYear) 
                           VALUES (:userId, :class, :enrollmentYear)");
    $stmt->bindParam(':userId', $userId);
    $stmt->bindParam(':class', $student['class']);
    $stmt->bindParam(':enrollmentYear', $student['enrollmentYear']);
    $stmt->execute();
}
echo "Students inserted successfully.<br>";

// Insert modules into Modules table
foreach ($modules as $module) {
    $stmt = $conn->prepare("INSERT INTO Modules (ModuleCode, ModuleName, CreditHours) 
                           VALUES (:moduleCode, :moduleName, :creditHours)");
    $stmt->bindParam(':moduleCode', $module['moduleCode']);
    $stmt->bindParam(':moduleName', $module['moduleName']);
    $stmt->bindParam(':creditHours', $module['creditHours']);
    $stmt->execute();
}
echo "Modules inserted successfully.<br>";

// Fetch student IDs from Students table
$studentIds = $conn->query("SELECT StudentID FROM Students");
$moduleIds = range(1, 6);

// Generate random grades for students in each module
while ($student = $studentIds->fetchArray(SQLITE3_ASSOC)) {
    foreach ($moduleIds as $moduleId) {
        $continuousAssessmentGrade = mt_rand(50, 100);
        $finalExamGrade = mt_rand(50, 100);
        $totalGrade = ($continuousAssessmentGrade * 0.4) + ($finalExamGrade * 0.6);
        $gradePoint = round($totalGrade / 20, 2);

        $stmt = $conn->prepare("INSERT INTO Grades (StudentID, ModuleID, ContinuousAssessmentGrade, FinalExamGrade, TotalGrade, GradePoint) 
                               VALUES (:studentId, :moduleId, :continuousAssessmentGrade, :finalExamGrade, :totalGrade, :gradePoint)");
        $stmt->bindParam(':studentId', $student['StudentID']);
        $stmt->bindParam(':moduleId', $moduleId);
        $stmt->bindParam(':continuousAssessmentGrade', $continuousAssessmentGrade);
        $stmt->bindParam(':finalExamGrade', $finalExamGrade);
        $stmt->bindParam(':totalGrade', $totalGrade);
        $stmt->bindParam(':gradePoint', $gradePoint);
        $stmt->execute();
    }
}
echo "Grades inserted successfully.<br>";

echo "Mock data generation complete.";
?>
