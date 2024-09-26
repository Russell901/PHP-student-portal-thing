<?php
session_start();
include '../setup.php';

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lecturer') {
    header("Location: ../login.php");
    exit();
}

$lecturer_id = $_SESSION['user_id'];

// Check if a student and module are selected
if (!isset($_GET['student']) || empty($_GET['student']) || !isset($_GET['module']) || empty($_GET['module'])) {
    header("Location: lecturer_dashboard.php");
    exit();
}

$student_id = $_GET['student'];
$module_code = $_GET['module'];

// Fetch module details
$sql = "SELECT ModuleID, ModuleName FROM Modules WHERE ModuleCode = :module_code";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':module_code', $module_code, SQLITE3_TEXT);
$result = $stmt->execute();
$module = $result->fetchArray(SQLITE3_ASSOC);

if (!$module) {
    header("Location: lecturer_dashboard.php");
    exit();
}

// Fetch student details
$sql = "SELECT UserID, FirstName, LastName FROM Users WHERE UserID = :student_id AND Role = 'student'";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':student_id', $student_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$student = $result->fetchArray(SQLITE3_ASSOC);

if (!$student) {
    header("Location: view_grades.php?module=" . urlencode($module_code));
    exit();
}

// Fetch existing grade
$sql = "SELECT ContinuousAssessmentGrade, FinalExamGrade, TotalGrade, GradePoint FROM Grades 
        WHERE StudentID = :student_id AND ModuleID = :module_id";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':student_id', $student_id, SQLITE3_INTEGER);
$stmt->bindValue(':module_id', $module['ModuleID'], SQLITE3_INTEGER);
$result = $stmt->execute();
$grade = $result->fetchArray(SQLITE3_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ca_grade = floatval($_POST['ca_grade']);
    $exam_grade = floatval($_POST['exam_grade']);
    $total_grade = $ca_grade + $exam_grade;
    $grade_point = calculateGradePoint($total_grade);

    if ($grade) {
        // Update existing grade
        $sql = "UPDATE Grades SET 
                ContinuousAssessmentGrade = :ca_grade,
                FinalExamGrade = :exam_grade,
                TotalGrade = :total_grade,
                GradePoint = :grade_point
                WHERE StudentID = :student_id AND ModuleID = :module_id";
    } else {
        // Insert new grade
        $sql = "INSERT INTO Grades (StudentID, ModuleID, ContinuousAssessmentGrade, FinalExamGrade, TotalGrade, GradePoint) 
                VALUES (:student_id, :module_id, :ca_grade, :exam_grade, :total_grade, :grade_point)";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':student_id', $student_id, SQLITE3_INTEGER);
    $stmt->bindValue(':module_id', $module['ModuleID'], SQLITE3_INTEGER);
    $stmt->bindValue(':ca_grade', $ca_grade, SQLITE3_FLOAT);
    $stmt->bindValue(':exam_grade', $exam_grade, SQLITE3_FLOAT);
    $stmt->bindValue(':total_grade', $total_grade, SQLITE3_FLOAT);
    $stmt->bindValue(':grade_point', $grade_point, SQLITE3_FLOAT);

    $result = $stmt->execute();

    if ($result) {
        $success_message = "Grade successfully saved.";
        // Refresh grade data
        $result = $conn->query($sql);
        $grade = $result->fetchArray(SQLITE3_ASSOC);
    } else {
        $error_message = "Error saving grade. Please try again.";
    }
}

function calculateGradePoint($total_grade) {
    if ($total_grade >= 70) return 4.0;
    if ($total_grade >= 65) return 3.5;
    if ($total_grade >= 60) return 3.0;
    if ($total_grade >= 55) return 2.5;
    if ($total_grade >= 50) return 2.0;
    if ($total_grade >= 45) return 1.5;
    if ($total_grade >= 40) return 1.0;
    return 0.0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Grade - <?php echo htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 p-4 text-white">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Edit Grade</h1>
            <div>
                <a href="view_grades.php?module=<?php echo urlencode($module_code); ?>" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded mr-2">Back to Grades</a>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4">Edit Grade for <?php echo htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']); ?></h2>
            <h3 class="text-xl mb-4"><?php echo htmlspecialchars($module['ModuleName']); ?> (<?php echo htmlspecialchars($module_code); ?>)</h3>

            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $success_message; ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4">
                <div>
                    <label for="ca_grade" class="block text-sm font-medium text-gray-700">Continuous Assessment Grade</label>
                    <input type="number" id="ca_grade" name="ca_grade" min="0" max="100" step="0.01" required
                           value="<?php echo isset($grade['ContinuousAssessmentGrade']) ? htmlspecialchars($grade['ContinuousAssessmentGrade']) : ''; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="exam_grade" class="block text-sm font-medium text-gray-700">Final Exam Grade</label>
                    <input type="number" id="exam_grade" name="exam_grade" min="0" max="100" step="0.01" required
                           value="<?php echo isset($grade['FinalExamGrade']) ? htmlspecialchars($grade['FinalExamGrade']) : ''; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                        Save Grade
                    </button>
                </div>
            </form>

            <?php if ($grade): ?>
            <div class="mt-8">
                <h4 class="text-lg font-semibold mb-2">Current Grade Summary</h4>
                <p>Total Grade: <?php echo number_format($grade['TotalGrade'], 2); ?></p>
                <p>Grade Point: <?php echo number_format($grade['GradePoint'], 2); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="bg-gray-200 mt-8 py-4">
        <div class="container mx-auto text-center text-gray-600">
            &copy; <?php echo date('Y'); ?> Student Portal. All rights reserved.
        </div>
    </footer>
</body>
</html>