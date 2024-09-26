<?php
ob_start();
include 'setup.php';

session_start();


$error = '';
$redirect = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = SQLite3::escapeString($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT UserID, Username, Password, Role FROM Users WHERE Username = '$username'";
    $result = $conn->query($sql);

    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if (password_verify($password, $row['Password'])) {
            $_SESSION['user_id'] = $row['UserID'];
            $_SESSION['role'] = $row['Role'];
            
            if ($row['Role'] == 'student') {
                $redirect = "student/student_dashboard.php";
            } elseif ($row['Role'] == 'lecturer') {
                $redirect = "lecturer/lecturer_dashboard.php";
            }
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
}

if (!empty($redirect)) {
    header("Location: $redirect");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Portal</title>
</head>
<body>
    <h2>Login</h2>
    <?php 
    if (!empty($error)) { 
        echo "<p style='color: red;'>$error</p>"; 
    } 
    ?>
    <form method="post" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" value="Login">
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</body>
</html>

