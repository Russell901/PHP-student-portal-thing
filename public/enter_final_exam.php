<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Enter Final Exam Grades</title>
</head>
<body>
  <h2>Enter Final Exam Grades</h2>
  <form action="save_final_exam.php" method="POST">
    <label for="studentID">Student ID:</label>
    <input type="number" name="studentID" required><br>

    <label for="moduleID">Module ID:</label>
    <input type="number" name="moduleID" required><br>

    <label for="finalExamGrade">Final Exam Grade:</label>
    <input type="number" step="0.1" name="finalExamGrade" required><br>

    <input type="submit" value="Save Final Exam Grade">
  </form>
</body>
</html>
