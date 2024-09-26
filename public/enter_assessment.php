<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Enter Continuous Assessment Grades</title>
</head>
<body>
  <h2>Enter Continuous Assessment Grades</h2>
  <form action="save_assessment.php" method="POST">
    <label for="studentID">Student ID:</label>
    <input type="number" name="studentID" required><br>

    <label for="moduleID">Module ID:</label>
    <input type="number" name="moduleID" required><br>

    <label for="assessmentType">Assessment Type (Quiz, Assignment, Midterm):</label>
    <input type="text" name="assessmentType" required><br>

    <label for="score">Score:</label>
    <input type="number" step="0.1" name="score" required><br>

    <label for="weight">Weight (as decimal, e.g., 0.2 for 20%):</label>
    <input type="number" step="0.01" name="weight" required><br>

    <input type="submit" value="Save Assessment">
  </form>
</body>
</html>
