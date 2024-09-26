<?php
session_start();

// Include the setup script
include 'setup.php';

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // User is logged in
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    if ($role == 'student') {
        header("Location: student_dashboard.php");
    } elseif ($role == 'lecturer') {
        header("Location: lecturer_dashboard.php");
    }
} else {
    // User is not logged in, show login/register options
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Student Portal</title>
    </head>
    <body>
        <h1>Welcome to the Student Portal</h1>
        <p>Please <a href="login.php">login</a> or <a href="register.php">register</a>.</p>
        <p>Check your php configuration here: <a href="phpinfo.php">PHP Info</a></p>
    </body>
    </html>
    <?php
}
?>