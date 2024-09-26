<?php
session_start();
include '../setup.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch student information
$stmt = $conn->prepare("SELECT FirstName, LastName FROM Users WHERE UserID = :user_id");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$student = $result->fetchArray(SQLITE3_ASSOC);

// Fetch student's modules and grades
$stmt = $conn->prepare("
    SELECT m.ModuleCode, m.ModuleName, g.ContinuousAssessmentGrade, g.FinalExamGrade, g.TotalGrade, g.GradePoint
    FROM Modules m
    LEFT JOIN Grades g ON m.ModuleID = g.ModuleID AND g.StudentID = :user_id
");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();

$modules = [];
$totalModules = 0;
$totalGrades = 0;
$gradedModules = 0;

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $modules[] = $row;
    if ($row['TotalGrade'] !== null) {
        $totalGrades += $row['TotalGrade'];
        $gradedModules++;
    }
    $totalModules++;
}

$overallAvgGrade = $gradedModules > 0 ? $totalGrades / $gradedModules : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 p-4 text-white">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Student Dashboard</h1>
            <div>
                <span class="mr-4">Welcome, <?php echo htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']); ?>!</span>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-8">
        <!-- Quick Stats -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4">Quick Stats</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-100 p-4 rounded">
                    <h3 class="font-semibold">Total Modules</h3>
                    <p class="text-2xl"><?php echo $totalModules; ?></p>
                </div>
                <div class="bg-green-100 p-4 rounded">
                    <h3 class="font-semibold">Graded Modules</h3>
                    <p class="text-2xl"><?php echo $gradedModules; ?></p>
                </div>
                <div class="bg-yellow-100 p-4 rounded">
                    <h3 class="font-semibold">Overall Average Grade</h3>
                    <p class="text-2xl"><?php echo number_format($overallAvgGrade, 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Modules and Grades -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Your Modules and Grades</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-2 text-left">Module Code</th>
                            <th class="p-2 text-left">Module Name</th>
                            <th class="p-2 text-left">Continuous Assessment</th>
                            <th class="p-2 text-left">Final Exam</th>
                            <th class="p-2 text-left">Total Grade</th>
                            <th class="p-2 text-left">Grade Point</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($modules as $module): ?>
                        <tr class="border-b">
                            <td class="p-2"><?php echo htmlspecialchars($module['ModuleCode']); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars($module['ModuleName']); ?></td>
                            <td class="p-2"><?php echo isset($module['ContinuousAssessmentGrade']) ? htmlspecialchars($module['ContinuousAssessmentGrade']) : 'N/A'; ?></td>
                            <td class="p-2"><?php echo isset($module['FinalExamGrade']) ? htmlspecialchars($module['FinalExamGrade']) : 'N/A'; ?></td>
                            <td class="p-2"><?php echo isset($module['TotalGrade']) ? htmlspecialchars($module['TotalGrade']) : 'N/A'; ?></td>
                            <td class="p-2"><?php echo isset($module['GradePoint']) ? htmlspecialchars($module['GradePoint']) : 'N/A'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="bg-gray-200 mt-8 py-4">
        <div class="container mx-auto text-center text-gray-600">
            &copy; <?php echo date('Y'); ?> Student Portal. All rights reserved.
        </div>
    </footer>
</body>
</html>
