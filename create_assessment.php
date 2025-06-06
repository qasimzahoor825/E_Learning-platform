<?php
session_start();
include "../db/db.php";

// Ensure the user is a teacher
if ($_SESSION["role"] != "teacher") {
    header("Location: ../login/login.php");
    exit();
}

$teacher_id = $_SESSION["user_id"];
$course_id = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $description = $_POST["description"];
    $course_id = $_POST["course_id"];

    if (empty($title) || empty($description) || empty($course_id)) {
        $error = "All fields are required.";
    } else {
        // Insert quiz into the quizzes table
        $stmt = $conn->prepare("INSERT INTO quizzes (course_id, title, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $course_id, $title, $description);

        if ($stmt->execute()) {
            $quiz_id = $stmt->insert_id;  // Get the inserted quiz ID
            // Redirect to the question creation page for this quiz
            header("Location: add_questions.php?quiz_id=" . $quiz_id);
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

$stmt = $conn->prepare("SELECT id, name FROM courses WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$courses = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Assessment</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Create an Assessment</h1>
    
    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="course_id">Select Course:</label><br>
        <select id="course_id" name="course_id" required>
            <option value="">-- Select a Course --</option>
            <?php while ($row = $courses->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label for="title">Assessment Title:</label><br>
        <input type="text" id="title" name="title" required><br><br>

        <label for="description">Assessment Description:</label><br>
        <textarea id="description" name="description" required></textarea><br><br>

        <button type="submit">Create Assessment</button>
    </form>

    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
