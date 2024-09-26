<?php
session_start();
include '../setup.php';

// Check if user is logged in and is a lecturer or admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'lecturer' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../login.php");
    exit();
}

// Check if a module ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: lecturer_dashboard.php");
    exit();
}

$module_id = $_GET['id'];

// Fetch module details
$sql = "SELECT * FROM Modules WHERE ModuleID = :module_id";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':module_id', $module_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$module = $result->fetchArray(SQLITE3_ASSOC);

if (!$module) {
    header("Location: lecturer_dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $module_code = $_POST['module_code'];
    $module_name = $_POST['module_name'];
    $credit_hours = $_POST['credit_hours'];
    $description = $_POST['description'];
    $learning_outcomes = $_POST['learning_outcomes'];

    $sql = "UPDATE Modules SET 
            ModuleCode = :module_code,
            ModuleName = :module_name,
            CreditHours = :credit_hours,
            Description = :description,
            LearningOutcomes = :learning_outcomes
            WHERE ModuleID = :module_id";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':module_code', $module_code, SQLITE3_TEXT);
    $stmt->bindValue(':module_name', $module_name, SQLITE3_TEXT);
    $stmt->bindValue(':credit_hours', $credit_hours, SQLITE3_INTEGER);
    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
    $stmt->bindValue(':learning_outcomes', $learning_outcomes, SQLITE3_TEXT);
    $stmt->bindValue(':module_id', $module_id, SQLITE3_INTEGER);

    $result = $stmt->execute();

    if ($result) {
        $success_message = "Module successfully updated.";
        // Refresh module data
        $result = $conn->query($sql);
        $module = $result->fetchArray(SQLITE3_ASSOC);
    } else {
        $error_message = "Error updating module. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Module - <?php echo htmlspecialchars($module['ModuleName']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 p-4 text-white">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Edit Module</h1>
            <div>
                <a href="lecturer_dashboard.php" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded mr-2">Back to Dashboard</a>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4">Edit Module: <?php echo htmlspecialchars($module['ModuleName']); ?></h2>

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
                    <label for="module_code" class="block text-sm font-medium text-gray-700">Module Code</label>
                    <input type="text" id="module_code" name="module_code" required
                           value="<?php echo htmlspecialchars($module['ModuleCode']); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="module_name" class="block text-sm font-medium text-gray-700">Module Name</label>
                    <input type="text" id="module_name" name="module_name" required
                           value="<?php echo htmlspecialchars($module['ModuleName']); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="credit_hours" class="block text-sm font-medium text-gray-700">Credit Hours</label>
                    <input type="number" id="credit_hours" name="credit_hours" required
                           value="<?php echo htmlspecialchars($module['CreditHours']); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"><?php echo htmlspecialchars($module['Description']); ?></textarea>
                </div>
                <div>
                    <label for="learning_outcomes" class="block text-sm font-medium text-gray-700">Learning Outcomes</label>
                    <textarea id="learning_outcomes" name="learning_outcomes" rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"><?php echo htmlspecialchars($module['LearningOutcomes']); ?></textarea>
                </div>
                <div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                        Update Module
                    </button>
                </div>
            </form>
        </div>
    </div>

    <footer class="bg-gray-200 mt-8 py-4">
        <div class="container mx-auto text-center text-gray-600">
            &copy; <?php echo date('Y'); ?> Student Portal. All rights reserved.
        </div>
    </footer>
</body>
</html>