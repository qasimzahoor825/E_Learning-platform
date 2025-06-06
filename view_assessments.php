<?php
session_start();
include "../db/db.php";

// Check if the user is logged in and their role is "student"
if (!isset($_SESSION["role"]) || $_SESSION["role"] != "student") {
    header("Location: ../login/login.php");
    exit();
}

$student_id = $_SESSION["user_id"];

// Fetch quizzes for the courses the student is enrolled in
$quizzes_query = "
    SELECT q.id AS quiz_id, q.title, q.description, q.created_at, c.name AS course_name
    FROM quizzes q
    INNER JOIN courses c ON q.course_id = c.id
    INNER JOIN enrollments e ON e.course_id = c.id
    WHERE e.student_id = ?
";
$stmt = $conn->prepare($quizzes_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$quizzes_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assessments</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* General styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .assessment-container {
            padding: 20px;
            margin: auto;
            max-width: 1000px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            text-align: center;
        }
        .btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .assessment-container {
                padding: 10px;
                margin: 10px;
            }
            table {
                font-size: 14px;
            }
            th, td {
                padding: 8px;
            }
            h1 {
                font-size: 1.5em;
            }
            .btn {
                padding: 8px 16px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            th, td {
                font-size: 12px;
                padding: 6px;
            }
            .btn {
                padding: 6px 12px;
                font-size: 12px;
            }
            h1 {
                font-size: 1.3em;
            }
        }
    </style>
</head>
<body>
    <div class="assessment-container">
        <h1>Your Quizzes</h1>
        <?php if ($quizzes_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Quiz Title</th>
                        <th>Description</th>
                        <th>Course</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($quiz = $quizzes_result->fetch_assoc()): ?>
                        <?php
                        // Check if the student has already attempted this quiz
                        $quiz_id = $quiz["quiz_id"];
                        $attempt_check_query = "
                            SELECT COUNT(*) AS attempt_count 
                            FROM student_answers 
                            WHERE student_id = ? AND quiz_id = ?
                        ";
                        $stmt_attempt = $conn->prepare($attempt_check_query);
                        $stmt_attempt->bind_param("ii", $student_id, $quiz_id);
                        $stmt_attempt->execute();
                        $attempt_result = $stmt_attempt->get_result();
                        $attempted = $attempt_result->fetch_assoc()["attempt_count"] > 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($quiz["title"]); ?></td>
                            <td><?php echo htmlspecialchars($quiz["description"]); ?></td>
                            <td><?php echo htmlspecialchars($quiz["course_name"]); ?></td>
                            <td><?php echo htmlspecialchars($quiz["created_at"]); ?></td>
                            <td>
                                <?php if ($attempted): ?>
                                    <button class="btn" disabled>Already Attempted</button>
                                <?php else: ?>
                                    <a href="attempt_assessment.php?quiz_id=<?php echo $quiz_id; ?>" class="btn">Attempt</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No quizzes available for your enrolled courses.</p>
        <?php endif; ?>
    </div>
</body>
</html>
