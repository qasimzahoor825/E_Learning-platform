<?php
session_start();
include "../db/db.php";

// Check if user is logged in and is a student
if ($_SESSION["role"] != "student") {
    header("Location: ../login/login.php");
    exit();
}

// Get student id from session
$student_id = $_SESSION['user_id'];

// Fetch submissions (for quizzes and assignments) for the logged-in student
$stmt = $conn->prepare("SELECT * FROM submissions WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$submission_result = $stmt->get_result();

// Arrays to categorize quizzes and assignments
$quizzes = [];
$assignments = [];

// Loop through the submissions and categorize them
while ($submission = $submission_result->fetch_assoc()) {
    if ($submission['type'] == 'quiz') {
        $quizzes[] = $submission;
    } elseif ($submission['type'] == 'assignment') {
        $assignments[] = $submission;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Grades</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<style>
  /* Apply default styles for large screens */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

.container {
    width: 80%; /* Container takes up 80% of the width on large screens */
    margin: 0 auto;
}

h1 {
    text-align: center;
    margin-bottom: 30px;
}

.table-section {
    margin-top: 40px;
}

table {
    width: 100%; /* Make table take full width */
    border-collapse: collapse; /* Collapse table borders */
}

th, td {
    padding: 12px 15px; /* Add padding to the table cells */
    text-align: left;
    border: 1px solid #ddd; /* Add borders to the cells */
}

th {
    background-color: #f2f2f2;
}

tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

tbody tr:hover {
    background-color: #f1f1f1;
}

a {
    color: #007bff;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

/* Tables should stretch and be readable */
.table-section table {
    margin-top: 20px;
}

table tr td, table tr th {
    word-wrap: break-word; /* Prevent text from overflowing */
}

footer {
    text-align: center;
    margin-top: 40px;
    font-size: 14px;
    color: #777;
}

footer a {
    text-decoration: none;
    color: #007bff;
}

footer a:hover {
    text-decoration: underline;
}

</style>
<body>
    <h1>Your Grades</h1>

    <!-- Quizzes Section -->
    <h2>Quizzes</h2>
    <?php if (count($quizzes) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Quiz Title</th>
                    <th>Grade</th>
                    <th>Feedback</th>
                    <th>Date Graded</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quizzes as $quiz_submission): ?>
                    <?php
                    // Fetch the quiz details
                    $quiz_id = $quiz_submission['related_id'];
                    $stmt_quiz = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
                    $stmt_quiz->bind_param("i", $quiz_id);
                    $stmt_quiz->execute();
                    $quiz_result = $stmt_quiz->get_result();
                    $quiz = $quiz_result->fetch_assoc();

                    // Fetch the grade for the quiz submission
                    $stmt_grade = $conn->prepare("SELECT * FROM grades WHERE submission_id = ?");
                    $stmt_grade->bind_param("i", $quiz_submission['id']);
                    $stmt_grade->execute();
                    $grade_result = $stmt_grade->get_result();
                    $grade = $grade_result->fetch_assoc();
                    ?>
                    <tr>
                        <td><?php echo $quiz['title']; ?></td>
                        <td><?php echo $grade ? $grade['grade'] : 'Not graded yet'; ?></td>
                        <td><?php echo $grade ? $grade['feedback'] : ''; ?></td>
                        <td><?php echo $grade ? $grade['graded_at'] : ''; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No quiz submissions yet.</p>
    <?php endif; ?>

    <!-- Assignments Section -->
    <h2>Assignments</h2>
    <?php if (count($assignments) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Assignment Title</th>
                    <th>Grade</th>
                    <th>Feedback</th>
                    <th>Date Graded</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $assignment_submission): ?>
                    <?php
                    // Fetch the assignment details
                    $assignment_id = $assignment_submission['related_id'];
                    $stmt_assignment = $conn->prepare("SELECT * FROM assignments WHERE id = ?");
                    $stmt_assignment->bind_param("i", $assignment_id);
                    $stmt_assignment->execute();
                    $assignment_result = $stmt_assignment->get_result();
                    $assignment = $assignment_result->fetch_assoc();

                    // Fetch the grade for the assignment submission
                    $stmt_grade = $conn->prepare("SELECT * FROM grades WHERE submission_id = ?");
                    $stmt_grade->bind_param("i", $assignment_submission['id']);
                    $stmt_grade->execute();
                    $grade_result = $stmt_grade->get_result();
                    $grade = $grade_result->fetch_assoc();
                    ?>
                    <tr>
                        <td><?php echo $assignment['title']; ?></td>
                        <td><?php echo $grade ? $grade['grade'] : 'Not graded yet'; ?></td>
                        <td><?php echo $grade ? $grade['feedback'] : ''; ?></td>
                        <td><?php echo $grade ? $grade['graded_at'] : ''; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No assignment submissions yet.</p>
    <?php endif; ?>

</body>
</html>
