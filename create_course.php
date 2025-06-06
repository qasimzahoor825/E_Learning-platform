<?php
session_start();
include "../db/db.php";

// Check if the user is a teacher
if ($_SESSION["role"] != "teacher") {
    header("Location: ../login/login.php");
    exit();
}

$teacher_id = $_SESSION['user_id']; // Assuming 'id' is stored in session during login
$error = $success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_name = trim($_POST['course_name']);
    $course_description = trim($_POST['course_description']);

    if (empty($course_name) || empty($course_description)) {
        $error = "All fields are required!";
    } else {
        $stmt = $conn->prepare("INSERT INTO courses (name, description, teacher_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $course_name, $course_description, $teacher_id);

        if ($stmt->execute()) {
            $success = "Course created successfully!";
        } else {
            $error = "Error creating course: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Create a New Course</h1>
            <nav class="dashboard-nav">
                <a href="dashboard.php">Dashboard</a>
                <a href="upload_lecture.php">Upload Lecture</a>
                <a href="create_assessment.php">Create Assessment</a>
                <a href="grade_submission.php">Grade Submissions</a>
                <a href="?logout=true" class="logout-btn">Logout</a>
            </nav>
        </header>
        <main>
            <form action="create_course.php" method="POST" class="form-container">
                <div>
                    <label for="course_name">Course Name:</label>
                    <input type="text" id="course_name" name="course_name" required>
                </div>
                <div>
                    <label for="course_description">Course Description:</label>
                    <textarea id="course_description" name="course_description" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn">Create Course</button>
            </form>
            <?php if (!empty($error)) : ?>
                <p class="error-message"><?= $error ?></p>
            <?php elseif (!empty($success)) : ?>
                <p class="success-message"><?= $success ?></p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
