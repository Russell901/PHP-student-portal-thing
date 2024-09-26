<?php
session_start();
include '../setup.php';

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lecturer') {
    header("Location: ../login.php");
    exit();
}

$lecturer_id = $_SESSION['user_id'];

// Get the module ID from the query string
$module_id = isset($_GET['module_id']) ? intval($_GET['module_id']) : 0;

// Fetch module details
$stmt = $conn->prepare("SELECT ModuleName FROM Modules WHERE ModuleID = :module_id");
$stmt->bindValue(':module_id', $module_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$module = $result->fetchArray(SQLITE3_ASSOC);

// Fetch students enrolled in this module
$stmt = $conn->prepare("
    SELECT s.StudentID, s.FirstName, s.LastName, g.ContinuousAssessmentGrade, g.FinalExamGrade 
    FROM Users s
    LEFT JOIN Grades g ON s.StudentID = g.StudentID AND g.ModuleID = :module_id
    WHERE s.Role = 'student'
");
$stmt->bindValue(':module_id', $module_id, SQLITE3_INTEGER);
$result = $stmt->execute();

$students = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $students[] = $row;
}

// If the form is submitted, save the grades
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['grades'] as $student_id => $gradeData) {
        $ca_grade = $gradeData['ca'];
        $final_exam_grade = $gradeData['final'];
        $total_grade = $ca_grade + $final_exam_grade;
        $grade_point = calculateGradePoint($total_grade);

        // Insert or update the grades in the database
        $stmt = $conn->prepare("
            INSERT INTO Grades (StudentID, ModuleID, ContinuousAssessmentGrade, FinalExamGrade, TotalGrade, GradePoint)
            VALUES (:student_id, :module_id, :ca_grade, :final_grade, :total_grade, :grade_point)
            ON CONFLICT(StudentID, ModuleID)
            DO UPDATE SET ContinuousAssessmentGrade = :ca_grade, FinalExamGrade = :final_grade, TotalGrade = :total_grade, GradePoint = :grade_point
        ");
        $stmt->bindValue(':student_id', $student_id, SQLITE3_INTEGER);
        $stmt->bindValue(':module_id', $module_id, SQLITE3_INTEGER);
        $stmt->bindValue(':ca_grade', $ca_grade, SQLITE3_FLOAT);
        $stmt->bindValue(':final_grade', $final_exam_grade, SQLITE3_FLOAT);
        $stmt->bindValue(':total_grade', $total_grade, SQLITE3_FLOAT);
        $stmt->bindValue(':grade_point', $grade_point, SQLITE3_FLOAT);
        $stmt->execute();
    }
    header("Location: enter_grades.php?module_id=$module_id&success=1");
    exit();
}

// Function to calculate grade point based on total grade
function calculateGradePoint($total_grade) {
    if ($total_grade >= 75) return 4.0;
    if ($total_grade >= 65) return 3.0;
    if ($total_grade >= 50) return 2.0;
    if ($total_grade >= 35) return 1.0;
    return 0.0;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Grades - <?php echo htmlspecialchars($module['ModuleName']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 p-4 text-white">
        <div class="container mx-auto">
            <h1 class="text-2xl font-bold">Enter Grades for <?php echo htmlspecialchars($module['ModuleName']); ?></h1>
        </div>
    </nav>

    <div class="container mx-auto mt-8">
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
                Grades have been successfully updated!
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-white p-6 rounded-lg shadow-md">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2 text-left">Student Name</th>
                        <th class="p-2 text-left">Continuous Assessment Grade</th>
                        <th class="p-2 text-left">Final Exam Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr class="border-b">
                        <td class="p-2"><?php echo htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']); ?></td>
                        <td class="p-2">
                            <input type="number" name="grades[<?php echo $student['StudentID']; ?>][ca]" class="border border-gray-300 p-2 rounded w-full" value="<?php echo htmlspecialchars($student['ContinuousAssessmentGrade']); ?>" min="0" max="100" required>
                        </td>
                        <td class="p-2">
                            <input type="number" name="grades[<?php echo $student['StudentID']; ?>][final]" class="border border-gray-300 p-2 rounded w-full" value="<?php echo htmlspecialchars($student['FinalExamGrade']); ?>" min="0" max="100" required>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="mt-6">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Save Grades</button>
                <a href="lecturer_dashboard.php" class="ml-4 text-blue-500 hover:underline">Back to Dashboard</a>
            </div>
        </form>
    </div>
</body>
</html>
