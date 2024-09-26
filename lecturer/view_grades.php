<?php
session_start();
include '../setup.php';

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lecturer') {
    header("Location: ../login.php");
    exit();
}

$lecturer_id = $_SESSION['user_id'];

// Check if a module is selected
if (!isset($_GET['module']) || empty($_GET['module'])) {
    header("Location: lecturer_dashboard.php");
    exit();
}

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

// Fetch grades for the selected module
$sql = "SELECT u.UserID, u.FirstName, u.LastName, g.ContinuousAssessmentGrade, g.FinalExamGrade, g.TotalGrade, g.GradePoint
        FROM Users u
        JOIN Grades g ON u.UserID = g.StudentID
        WHERE g.ModuleID = :module_id
        ORDER BY u.LastName, u.FirstName";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':module_id', $module['ModuleID'], SQLITE3_INTEGER);
$result = $stmt->execute();

$grades = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $grades[] = $row;
}

// Calculate module statistics
$total_students = count($grades);
$sum_total_grade = 0;
$sum_ca_grade = 0;
$sum_exam_grade = 0;

foreach ($grades as $grade) {
    $sum_total_grade += $grade['TotalGrade'];
    $sum_ca_grade += $grade['ContinuousAssessmentGrade'];
    $sum_exam_grade += $grade['FinalExamGrade'];
}

$avg_total_grade = $total_students > 0 ? $sum_total_grade / $total_students : 0;
$avg_ca_grade = $total_students > 0 ? $sum_ca_grade / $total_students : 0;
$avg_exam_grade = $total_students > 0 ? $sum_exam_grade / $total_students : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Grades - <?php echo htmlspecialchars($module['ModuleName']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 p-4 text-white">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">View Grades</h1>
            <div>
                <a href="lecturer_dashboard.php" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded mr-2">Dashboard</a>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-8">
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-semibold mb-4"><?php echo htmlspecialchars($module['ModuleName']); ?> (<?php echo htmlspecialchars($module_code); ?>)</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-100 p-4 rounded">
                    <h3 class="font-semibold">Total Students</h3>
                    <p class="text-2xl"><?php echo $total_students; ?></p>
                </div>
                <div class="bg-green-100 p-4 rounded">
                    <h3 class="font-semibold">Average Total Grade</h3>
                    <p class="text-2xl"><?php echo number_format($avg_total_grade, 2); ?></p>
                </div>
                <div class="bg-yellow-100 p-4 rounded">
                    <h3 class="font-semibold">Average CA Grade</h3>
                    <p class="text-2xl"><?php echo number_format($avg_ca_grade, 2); ?></p>
                </div>
                <div class="bg-pink-100 p-4 rounded">
                    <h3 class="font-semibold">Average Exam Grade</h3>
                    <p class="text-2xl"><?php echo number_format($avg_exam_grade, 2); ?></p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-2 text-left">Student Name</th>
                            <th class="p-2 text-left">CA Grade</th>
                            <th class="p-2 text-left">Exam Grade</th>
                            <th class="p-2 text-left">Total Grade</th>
                            <th class="p-2 text-left">Grade Point</th>
                            <th class="p-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grades as $grade): ?>
                        <tr class="border-b">
                            <td class="p-2"><?php echo htmlspecialchars($grade['LastName'] . ', ' . $grade['FirstName']); ?></td>
                            <td class="p-2"><?php echo number_format($grade['ContinuousAssessmentGrade'], 2); ?></td>
                            <td class="p-2"><?php echo number_format($grade['FinalExamGrade'], 2); ?></td>
                            <td class="p-2"><?php echo number_format($grade['TotalGrade'], 2); ?></td>
                            <td class="p-2"><?php echo number_format($grade['GradePoint'], 2); ?></td>
                            <td class="p-2">
                                <a href="edit_grade.php?student=<?php echo urlencode($grade['UserID']); ?>&module=<?php echo urlencode($module_code); ?>" class="text-blue-500 hover:underline">Edit</a>
                            </td>
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