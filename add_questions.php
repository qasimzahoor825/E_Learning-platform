<?php
session_start();
include "../db/db.php";

// Ensure the user is a teacher
if ($_SESSION["role"] != "teacher") {
    header("Location: ../login/login.php");
    exit();
}

$quiz_id = $_GET['quiz_id']; // Get the quiz ID passed from the create assessment page
$question_text = "";
$question_type = "";
$options = "";
$correct_answer = "";
$error = "";
$success = "";

// Handle form submission to add questions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_text = $_POST["question_text"];
    $question_type = $_POST["question_type"];
    $options = $_POST["options"];
    $correct_answer = $_POST["correct_answer"];

    if (empty($question_text) || empty($question_type) || empty($correct_answer)) {
        $error = "All fields are required.";
    } else {
        // Insert question into the questions table
        $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, question_type, options, correct_answer) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $quiz_id, $question_text, $question_type, $options, $correct_answer);

        if ($stmt->execute()) {
            $success = "Question added successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Questions to Assessment</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Add Questions to Assessment</h1>

    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="question_text">Question Text:</label><br>
        <textarea id="question_text" name="question_text" required></textarea><br><br>

        <label for="question_type">Question Type:</label><br>
        <select id="question_type" name="question_type" required>
            <option value="multiple_choice">Multiple Choice</option>
            <option value="short_answer">Short Answer</option>
        </select><br><br>

        <label for="options">Options (for multiple choice):</label><br>
        <textarea id="options" name="options" placeholder="Comma separated options (only for multiple choice)"></textarea><br><br>

        <label for="correct_answer">Correct Answer:</label><br>
        <input type="text" id="correct_answer" name="correct_answer" required><br><br>

        <button type="submit">Add Question</button>
    </form>

    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
