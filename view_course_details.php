<?php
session_start();
include "../db/db.php";

// Check if the user is logged in and their role is "student"
if (!isset($_SESSION["role"]) || $_SESSION["role"] != "student") {
    header("Location: ../login/login.php");
    exit();
}

// Get the course ID from the query parameter
if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {
    header("Location: view_courses.php");
    exit();
}

$course_id = intval($_GET['course_id']);

// Fetch course details along with teacher details
$stmt = $conn->prepare("
    SELECT 
        courses.name AS course_name, 
        courses.description AS course_description, 
        users.name AS teacher_name, 
        users.email AS teacher_email 
    FROM courses 
    JOIN users ON courses.teacher_id = users.id 
    WHERE courses.id = ?
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the course exists
if ($result->num_rows === 0) {
    echo "<p>Course not found.</p>";
    exit();
}

$course = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Details</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* View Course Details Specific Styles */
        .course-details-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .course-details-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .course-details-header h1 {
            font-size: 28px;
            color: #333;
        }

        .course-details-body {
            line-height: 1.6;
            font-size: 16px;
            color: #555;
        }

        .teacher-info {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .teacher-info h3 {
            font-size: 20px;
            color: #333;
        }

        .teacher-info p {
            font-size: 14px;
            color: #666;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 12px;
            background: #007bff;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="course-details-container">
        <div class="course-details-header">
            <h1><?php echo htmlspecialchars($course['course_name']); ?></h1>
        </div>
        <div class="course-details-body">
            <p><strong>Description:</strong> <?php echo htmlspecialchars($course['course_description']); ?></p>
        </div>
        <div class="teacher-info">
            <h3>Teacher Details</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($course['teacher_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($course['teacher_email']); ?></p>
        </div>
        <a href="view_courses.php" class="back-btn">Back to Courses</a>
    </div>
</body>
</html>
