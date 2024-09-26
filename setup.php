<?php
// Start output buffering
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$debug = isset($_GET['debug']) && $_GET['debug'] === 'true';

// Database file
$dbFile = __DIR__ . '/student_portal.sqlite';

try {
    // Create connection
    $conn = new SQLite3($dbFile);
    $log = "Connected successfully to SQLite database.\n";

    // Create the Users table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS Users (
        UserID INTEGER PRIMARY KEY AUTOINCREMENT,
        Username TEXT UNIQUE NOT NULL,
        Password TEXT NOT NULL,
        Role TEXT NOT NULL CHECK(Role IN ('student', 'lecturer', 'admin')), -- Defines specific roles
        FirstName TEXT,
        LastName TEXT,
        Email TEXT
    )";
    $conn->exec($sql);
    $log .= "Table Users created successfully or already exists.\n";

    // Create the Students table for student-specific data
    $sql = "CREATE TABLE IF NOT EXISTS Students (
        StudentID INTEGER PRIMARY KEY AUTOINCREMENT,
        UserID INTEGER NOT NULL, -- Foreign key referencing Users
        Class TEXT NOT NULL,
        EnrollmentYear INTEGER,
        FOREIGN KEY (UserID) REFERENCES Users(UserID)
    )";
    $conn->exec($sql);
    $log .= "Table Students created successfully or already exists.\n";

    // Create the Lecturers table for lecturer-specific data
    $sql = "CREATE TABLE IF NOT EXISTS Lecturers (
        LecturerID INTEGER PRIMARY KEY AUTOINCREMENT,
        UserID INTEGER NOT NULL, -- Foreign key referencing Users
        Department TEXT NOT NULL,
        HireDate TEXT,
        FOREIGN KEY (UserID) REFERENCES Users(UserID)
    )";
    $conn->exec($sql);
    $log .= "Table Lecturers created successfully or already exists.\n";

    // Create the Modules table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS Modules (
        ModuleID INTEGER PRIMARY KEY AUTOINCREMENT,
        ModuleCode TEXT UNIQUE NOT NULL,
        ModuleName TEXT NOT NULL,
        CreditHours INTEGER NOT NULL
    )";
    $conn->exec($sql);
    $log .= "Table Modules created successfully or already exists.\n";

    // Create the Grades table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS Grades (
        GradeID INTEGER PRIMARY KEY AUTOINCREMENT,
        StudentID INTEGER,
        ModuleID INTEGER,
        ContinuousAssessmentGrade REAL,
        FinalExamGrade REAL,
        TotalGrade REAL,
        GradePoint REAL,
        FOREIGN KEY (StudentID) REFERENCES Students(StudentID),
        FOREIGN KEY (ModuleID) REFERENCES Modules(ModuleID)
    )";
    $conn->exec($sql);
    $log .= "Table Grades created successfully or already exists.\n";

} catch(Exception $e) {
    $log .= "Connection failed: " . $e->getMessage() . "\n";
}

$log .= "Setup script completed.\n";

// Don't close the connection here, as we'll need it in other files

if ($debug) {
    // If debug mode is enabled, output the log
    ob_end_flush(); // Send the buffered output
    echo nl2br($log); // Convert newlines to <br> tags for HTML output
} else {
    // If not in debug mode, discard the output
    ob_end_clean();
}

// Make the connection available to other scripts
global $conn;
?>
