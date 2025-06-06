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
$title = "";
$file_path = "";
$error = "";
$success = "";

// Fetch courses created by the teacher
$stmt = $conn->prepare("SELECT id, name FROM courses WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$courses = $stmt->get_result();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST["course_id"];
    $title = $_POST["title"];
    $file = $_FILES["file"];

    if (empty($course_id) || empty($title) || empty($file["name"])) {
        $error = "All fields are required.";
    } else {
        // Handle file upload
        $target_dir = "../uploads/lectures/";
        $target_file = $target_dir . basename($file["name"]);
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file is a valid type (for example, PDF, PPT, or video)
        if (!in_array($file_type, ["pdf", "ppt", "pptx", "mp4", "avi"])) {
            $error = "Only PDF, PPT, PPTX, MP4, and AVI files are allowed.";
        } else {
            // Upload the file to the server
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                // Insert lecture details into the database
                $stmt = $conn->prepare("INSERT INTO lectures (course_id, title, file_path) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $course_id, $title, $target_file);

                if ($stmt->execute()) {
                    $success = "Lecture uploaded successfully!";
                } else {
                    $error = "Error: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Lecture</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Upload a Lecture</h1>

    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="course_id">Select Course:</label><br>
        <select id="course_id" name="course_id" required>
            <option value="">-- Select a Course --</option>
            <?php while ($row = $courses->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label for="title">Lecture Title:</label><br>
        <input type="text" id="title" name="title" value="<?php echo $title; ?>" required><br><br>

        <label for="file">Lecture File:</label><br>
        <input type="file" id="file" name="file" accept=".pdf, .ppt, .pptx, .mp4, .avi" required><br><br>

        <button type="submit">Upload Lecture</button>
    </form>

    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
