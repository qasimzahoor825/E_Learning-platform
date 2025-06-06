<?php
session_start();
include "../db/db.php";

// Check if user is logged in and is a teacher
if ($_SESSION["role"] != "teacher") {
    header("Location: ../login/login.php");
    exit();
}

// Check if course_id is passed in the URL
if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];
    
    // Fetch the course details
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    
    // Fetch the students who have attempted/submit assignments or quizzes for this course
    $stmt = $conn->prepare("SELECT * FROM submissions WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $submissions_result = $stmt->get_result();
} else {
    header("Location: grade_submission.php"); // Redirect if course_id is missing
    exit();
}

// Grading submissions (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submission_id = $_POST['submission_id'];
    $grade = $_POST['grade'];
    $feedback = $_POST['feedback'];
    $graded_at = date('Y-m-d H:i:s');

    // Insert grade and feedback into the grades table
    $stmt = $conn->prepare("INSERT INTO grades (submission_id, grade, feedback, graded_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $submission_id, $grade, $feedback, $graded_at);
    if ($stmt->execute()) {
        $message = "Grade submitted successfully!";
    } else {
        $message = "Error grading submission!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grade Submissions - <?php echo $course['name']; ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="grade-submission-page">
    <h1>Grade Submissions for Course: <?php echo $course['name']; ?></h1>

    <!-- Display Message -->
    <?php if (isset($message)) { echo "<p>$message</p>"; } ?>

    <!-- Show students' submissions -->
    <h2>Submissions</h2>
    <table class="grade-submission-table">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Submission</th>
                <th>Grade</th>
                <th>Feedback</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($submission = $submissions_result->fetch_assoc()): ?>
                <?php
                // Fetch the student details for this submission
                $student_id = $submission['student_id'];
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                $student_result = $stmt->get_result();
                $student = $student_result->fetch_assoc();

                // Check if the submission already has a grade
                $stmt_grade = $conn->prepare("SELECT * FROM grades WHERE submission_id = ?");
                $stmt_grade->bind_param("i", $submission['id']);
                $stmt_grade->execute();
                $grade_result = $stmt_grade->get_result();
                $graded_submission = $grade_result->fetch_assoc();

                // Fetch the related task (assignment or quiz) using the related_id
                $related_id = $submission['related_id'];
                $task = null; // Default value for task

                if ($submission['type'] == 'quiz') {
                    // Fetch quiz details using related_id (if submission type is 'quiz')
                    $stmt_task = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
                    $stmt_task->bind_param("i", $related_id);
                    $stmt_task->execute();
                    $task_result = $stmt_task->get_result();
                    $task = $task_result->fetch_assoc();
                } elseif ($submission['type'] == 'assignment') {
                    // Fetch assignment details using related_id (if submission type is 'assignment')
                    $stmt_task = $conn->prepare("SELECT * FROM assignments WHERE id = ?");
                    $stmt_task->bind_param("i", $related_id);
                    $stmt_task->execute();
                    $task_result = $stmt_task->get_result();
                    $task = $task_result->fetch_assoc();
                }
                ?>
                <tr>
                    <td><?php echo $student['name']; ?></td>
                    <td>
                        <a href="view_submission.php?submission_id=<?php echo $submission['id']; ?>" target="_blank">View Submission</a>
                    </td>
                    <td>
                        <?php if ($graded_submission): ?>
                            <!-- If already graded, display the grade -->
                            <strong><?php echo $graded_submission['grade']; ?></strong>
                        <?php else: ?>
                            <!-- If not graded, show input fields -->
                            <form method="POST" action="">
                                <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                <input type="text" name="grade" placeholder="Enter grade" required>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($graded_submission): ?>
                            <!-- If already graded, display the feedback -->
                            <textarea disabled rows="3"><?php echo $graded_submission['feedback']; ?></textarea>
                        <?php else: ?>
                            <!-- If not graded, show input field for feedback -->
                            <textarea name="feedback" placeholder="Enter feedback" rows="3"></textarea>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($graded_submission): ?>
                            <!-- If already graded, disable the submit button -->
                            <button type="button" disabled>Graded</button>
                        <?php else: ?>
                            <!-- If not graded, show the submit button -->
                            <button type="submit">Grade</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="grade_submission.php">Back to Courses</a>
</body>
</html>
