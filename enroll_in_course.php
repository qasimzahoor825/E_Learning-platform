<?php
session_start();
include "../db/db.php";

// Check if the user is logged in and their role is "student"
if (!isset($_SESSION["role"]) || $_SESSION["role"] != "student") {
    header("Location: ../login/login.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["course_id"])) {
    $student_id = $_SESSION["user_id"];
    $course_id = intval($_POST["course_id"]);

    // Check if the student is already enrolled
    $check_query = "SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $student_id, $course_id);
    $stmt->execute();
    $check_result = $stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Already enrolled
        header("Location: view_courses.php?message=already_enrolled");
    } else {
        // Enroll the student
        $enroll_query = "INSERT INTO enrollments (student_id, course_id, enrolled_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($enroll_query);
        $stmt->bind_param("ii", $student_id, $course_id);
        if ($stmt->execute()) {
            header("Location: view_courses.php?message=enrolled_successfully");
        } else {
            header("Location: view_courses.php?message=enrollment_failed");
        }
    }
} else {
    header("Location: view_courses.php");
    exit();
}
?>
