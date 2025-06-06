<?php
session_start();
include "../db/db.php";

// Check if the user is logged in and their role is "student"
if (!isset($_SESSION["role"]) || $_SESSION["role"] != "student") {
    header("Location: ../login/login.php");
    exit();
}

// Check if the quiz_id is provided
if (!isset($_GET["quiz_id"]) || empty($_GET["quiz_id"])) {
    header("Location: view_assessments.php");
    exit();
}

$quiz_id = intval($_GET["quiz_id"]);
$student_id = $_SESSION["user_id"];

// Fetch questions for the quiz
$questions_query = "SELECT id AS question_id, question_text, question_type, options FROM questions WHERE quiz_id = ?";
$stmt = $conn->prepare($questions_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions_result = $stmt->get_result();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $answers = $_POST["answers"];
    $submission_time = date("Y-m-d H:i:s");

    // Insert the submission record in the submissions table
    $submission_query = "
        INSERT INTO submissions (student_id, course_id, type, related_id, submitted_at)
        VALUES (?, (SELECT course_id FROM quizzes WHERE id = ?), 'quiz', ?, ?)
    ";
    $stmt = $conn->prepare($submission_query);
    $stmt->bind_param("iiis", $student_id, $quiz_id, $quiz_id, $submission_time);
    $stmt->execute();
    $submission_id = $conn->insert_id; // Get the newly created submission ID

    foreach ($answers as $question_id => $student_answer) {
        // Fetch the correct answer for the question
        $correct_answer_query = "SELECT correct_answer FROM questions WHERE id = ?";
        $stmt = $conn->prepare($correct_answer_query);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $correct_answer = $result->fetch_assoc()["correct_answer"];

        // Check if the student's answer is correct
        $is_correct = ($student_answer === $correct_answer) ? 1 : 0;

        // Insert the student's answer into the student_answers table
        $insert_answer_query = "
            INSERT INTO student_answers (student_id, quiz_id, question_id, student_answer, is_correct)
            VALUES (?, ?, ?, ?, ?)
        ";
        $stmt = $conn->prepare($insert_answer_query);
        $stmt->bind_param("iiisi", $student_id, $quiz_id, $question_id, $student_answer, $is_correct);
        $stmt->execute();
    }

    // Redirect after submission
    header("Location: view_assessments.php?message=quiz_submitted");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attempt Quiz</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="quiz-container">
        <h1>Attempt Quiz</h1>
        <form method="POST" action="">
            <?php if ($questions_result->num_rows > 0): ?>
                <?php while ($question = $questions_result->fetch_assoc()): ?>
                    <div class="question-item">
                        <p><?php echo htmlspecialchars($question["question_text"]); ?></p>
                        <?php if ($question["question_type"] === "multiple_choice"): ?>
                            <?php $options = explode(",", $question["options"]); ?>
                            <?php foreach ($options as $option): ?>
                                <label>
                                    <input type="radio" name="answers[<?php echo $question["question_id"]; ?>]" value="<?php echo htmlspecialchars(trim($option)); ?>" required>
                                    <?php echo htmlspecialchars(trim($option)); ?>
                                </label><br>
                            <?php endforeach; ?>
                        <?php elseif ($question["question_type"] === "short_answer"): ?>
                            <textarea name="answers[<?php echo $question["question_id"]; ?>]" rows="3" required></textarea>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
                <button type="submit" class="btn">Submit Quiz</button>
            <?php else: ?>
                <p>No questions available for this quiz.</p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
