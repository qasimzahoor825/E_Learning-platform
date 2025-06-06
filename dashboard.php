<?php
session_start();
include "../db/db.php";

if ($_SESSION["role"] != "teacher") {
    header("Location: ../login/login.php");
    exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background-image: url("../images/i.jpg");
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            color: white;
        }

        .dashboard-container {
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
            margin: 20px;
        }

        header {
            margin-bottom: 20px;
        }

        .dashboard-nav a {
            margin-right: 15px;
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Welcome, Teacher</h1>
            <nav class="dashboard-nav">
                <a href="create_course.php">Create Course</a>
                <a href="create_assignment.php">Create Assignment</a>
                <a href="upload_lecture.php">Upload Lecture</a>
                <a href="create_assessment.php">Create Assessment</a>
                <a href="grade_submission.php">Grade Submissions</a>
                <a href="?logout=true" class="logout-btn">Logout</a>
            </nav>
        </header>
    </div>
</body>
</html>
