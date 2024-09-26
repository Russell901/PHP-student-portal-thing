<?php 
session_start(); 
include '../setup.php'; 

// Check if user is logged in and is a lecturer 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lecturer') { 
    header("Location: ../login.php"); 
    exit(); 
} 

$lecturer_id = $_SESSION['user_id']; 

// Fetch lecturer details 
$sql = "SELECT FirstName, LastName, Email FROM Users WHERE UserID = :lecturer_id AND Role = 'lecturer'"; 
$stmt = $conn->prepare($sql);
$stmt->bindValue(':lecturer_id', $lecturer_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$lecturer = $result->fetchArray(SQLITE3_ASSOC); 

// Fetch modules taught by the lecturer
$sql = "SELECT m.ModuleID, m.ModuleCode, m.ModuleName, m.CreditHours,
        (SELECT COUNT(*) FROM Grades g WHERE g.ModuleID = m.ModuleID) as StudentCount
        FROM Modules m
        WHERE m.ModuleID IN (SELECT DISTINCT ModuleID FROM Grades)
        ORDER BY m.ModuleCode"; 
$result = $conn->query($sql); 
$modules = []; 
while ($row = $result->fetchArray(SQLITE3_ASSOC)) { 
    $modules[] = $row; 
} 

// Calculate total students (DISTINCT across all modules)
$sql = "SELECT COUNT(DISTINCT StudentID) as TotalStudents FROM Grades";
$result = $conn->query($sql);
$totalStudents = $result->fetchArray(SQLITE3_ASSOC)['TotalStudents'];

// Calculate average grade
$totalGrades = 0;
$gradedStudents = 0;
foreach ($modules as $module) {
    $sql = "SELECT AVG(TotalGrade) as AvgGrade FROM Grades WHERE ModuleID = :module_id AND TotalGrade IS NOT NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':module_id', $module['ModuleID'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    $avgGrade = $result->fetchArray(SQLITE3_ASSOC)['AvgGrade'];
    if ($avgGrade !== null) {
        $totalGrades += $avgGrade;
        $gradedStudents++;
    }
}
$overallAvgGrade = $gradedStudents > 0 ? $totalGrades / $gradedStudents : 0;
?>

<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Lecturer Dashboard</title> 
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head> 
<body class="bg-gray-100"> 
    <nav class="bg-blue-600 p-4 text-white">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Lecturer Dashboard</h1>
            <div>
                <span class="mr-4">Welcome, <?php echo htmlspecialchars($lecturer['FirstName'] . ' ' . $lecturer['LastName']); ?>!</span>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-8">
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4">Quick Stats</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-100 p-4 rounded">
                    <h3 class="font-semibold">Total Modules</h3>
                    <p class="text-2xl"><?php echo count($modules); ?></p>
                </div>
                <div class="bg-green-100 p-4 rounded">
                    <h3 class="font-semibold">Total Students</h3>
                    <p class="text-2xl"><?php echo $totalStudents; ?></p>
                </div>
                <div class="bg-yellow-100 p-4 rounded">
                    <h3 class="font-semibold">Overall Average Grade</h3>
                    <p class="text-2xl"><?php echo number_format($overallAvgGrade, 2); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Your Modules</h2>
            <div class="overflow-x-auto">
                <table class="w-full"> 
                    <thead>
                        <tr class="bg-gray-200"> 
                            <th class="p-2 text-left">Module Code</th> 
                            <th class="p-2 text-left">Module Name</th> 
                            <th class="p-2 text-left">Credit Hours</th>
                            <th class="p-2 text-left">Student Count</th>
                            <th class="p-2 text-left">Actions</th> 
                        </tr>
                    </thead>
                    <tbody> 
                        <?php foreach ($modules as $module): ?> 
                        <tr class="border-b"> 
                            <td class="p-2"><?php echo htmlspecialchars($module['ModuleCode']); ?></td> 
                            <td class="p-2"><?php echo htmlspecialchars($module['ModuleName']); ?></td> 
                            <td class="p-2"><?php echo htmlspecialchars($module['CreditHours']); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars($module['StudentCount']); ?></td>
                            <td class="p-2">
                                <a href="view_grades.php?module=<?php echo urlencode($module['ModuleCode']); ?>" class="text-blue-500 hover:underline">View Grades</a>
                                | 
                                <a href="edit_module.php?id=<?php echo urlencode($module['ModuleID']); ?>" class="text-green-500 hover:underline">Edit</a>
                                | 
                                <a href="enter_grades.php?module_id=<?php echo urlencode($module['ModuleID']); ?>" class="text-indigo-500 hover:underline">Enter Grades</a>
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
