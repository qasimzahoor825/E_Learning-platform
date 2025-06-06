<?php
session_start();
include "../db/db.php";

// Check if the user is logged in and their role is "student"
if (!isset($_SESSION["role"]) || $_SESSION["role"] != "student") {
    header("Location: ../login/login.php");
    exit();
}

$student_id = $_SESSION["user_id"];

// Get the assignment ID from the URL
$assignment_id = isset($_GET['assignment_id']) ? $_GET['assignment_id'] : null;
if (!$assignment_id) {
    header("Location: view_assignments.php");
    exit();
}

// Fetch assignment details
$assignment_query = "
    SELECT title, description FROM assignments WHERE id = ?
";
$stmt = $conn->prepare($assignment_query);
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment_result = $stmt->get_result();

if ($assignment_result->num_rows == 0) {
    header("Location: view_assignments.php");
    exit();
}

$assignment = $assignment_result->fetch_assoc();

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['assignment_file'])) {
    $file = $_FILES['assignment_file'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    $upload_dir = "../uploads/";

    if ($file_error === 0) {
        // Generate a unique file name to avoid overwriting
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = uniqid("", true) . '.' . $file_ext;
        $file_path = $upload_dir . $new_file_name;

        // Move the file to the uploads directory
        if (move_uploaded_file($file_tmp, $file_path)) {
            // Fetch the course_id associated with the assignment
            $course_query = "SELECT course_id FROM assignments WHERE id = ?";
            $stmt_course = $conn->prepare($course_query);
            $stmt_course->bind_param("i", $assignment_id);
            $stmt_course->execute();
            $stmt_course->bind_result($course_id);
            $stmt_course->fetch();
            $stmt_course->close();

            // Set the values to be inserted into the database
            $answers = NULL;  // Set answers to NULL as we're not handling answers
            $submitted_at = date('Y-m-d H:i:s');  // Get the current timestamp

            // Insert submission record into the database
            $insert_submission_query = "
                INSERT INTO submissions (student_id, course_id, type, related_id, file_path, answers, submitted_at)
                VALUES (?, ?, 'assignment', ?, ?, ?, ?)
            ";
            // Now, bind the parameters (7 variables)
            $stmt_insert = $conn->prepare($insert_submission_query);
            $stmt_insert->bind_param("iiisss", $student_id, $course_id, $assignment_id, $file_path, $answers, $submitted_at);

            if ($stmt_insert->execute()) {
                echo "Assignment uploaded successfully.";
            } else {
                echo "Error uploading the assignment.";
            }
        } else {
            echo "Failed to upload file.";
        }
    } else {
        echo "There was an error uploading your file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Assignment</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Add custom styles for responsiveness */
        .upload-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .upload-container h1 {
            text-align: center;
        }

        .upload-container form {
            display: flex;
            flex-direction: column;
        }

        .upload-container form label,
        .upload-container form input,
        .upload-container form button {
            margin-bottom: 15px;
        }

        @media screen and (max-width: 768px) {
            .upload-container {
                padding: 15px;
            }

            .upload-container h1 {
                font-size: 1.5em;
            }

            .upload-container form input,
            .upload-container form button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="upload-container">
        <h1>Upload Your Assignment</h1>
        <p><strong>Assignment Title:</strong> <?php echo htmlspecialchars($assignment['title']); ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($assignment['description']); ?></p>

        <form action="upload_assignment.php?assignment_id=<?php echo $assignment_id; ?>" method="POST" enctype="multipart/form-data">
            <label for="assignment_file">Choose File:</label>
            <input type="file" name="assignment_file" id="assignment_file" required>
            <br>
            <button type="submit" class="btn">Upload Assignment</button>
        </form>
    </div>
</body>
</html>
