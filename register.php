<?php

include 'setup.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = SQLite3::escapeString($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = SQLite3::escapeString($_POST['role']);
    $firstName = SQLite3::escapeString($_POST['firstName']);
    $lastName = SQLite3::escapeString($_POST['lastName']);
    $email = SQLite3::escapeString($_POST['email']);

    $sql = "INSERT INTO Users (Username, Password, Role, FirstName, LastName, Email) 
            VALUES ('$username', '$password', '$role', '$firstName', '$lastName', '$email')";

    if ($conn->exec($sql)) {
        $success = "Registration successful. You can now <a href='login.php'>login</a>.";
    } else {
        $error = "Error: " . $conn->lastErrorMsg();
    }
}

// Don't close the connection here
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Student Portal</title>
</head>
<body>
    <h2>Register</h2>
    <?php 
    if (isset($error)) { echo "<p style='color: red;'>$error</p>"; }
    if (isset($success)) { echo "<p style='color: green;'>$success</p>"; }
    ?>
    <form method="post" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        <label for="role">Role:</label>
        <select id="role" name="role" required>
            <option value="student">Student</option>
            <option value="lecturer">Lecturer</option>
        </select><br><br>
        <label for="firstName">First Name:</label>
        <input type="text" id="firstName" name="firstName" required><br><br>
        <label for="lastName">Last Name:</label>
        <input type="text" id="lastName" name="lastName" required><br><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>
        <input type="submit" value="Register">
    </form>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
</body>
</html>