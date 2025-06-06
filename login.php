<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "../db/db.php";
    $email = $_POST["email"];
    $password = $_POST["password"];

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["role"] = $user["role"];
            header("Location: ../" . ($user["role"] == "student" ? "student/dashboard.php" : "teacher/dashboard.php"));
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: white;
        }

        form {
            background: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 10px;
            width: 300px;
            display: flex; /* Enables Flexbox */
            flex-direction: column; /* Stacks children vertically */
        }

        input, button {
            margin: 10px 0;
            padding: 10px;
            width: 100%; /* Makes inputs take full width */
            box-sizing: border-box; /* Ensures padding is included in width */
        }

        button {
            background-color: #4CAF50; /* Button color */
            color: white; /* Text color */
            border: none; /* No border */
            cursor: pointer; /* Pointer cursor on hover */
            transition: background-color 0.3s; /* Smooth transition for hover effect */
        }

        button:hover {
            background-color: #45a049; /* Darker shade on hover */
        }
    </style>
</head>
<body>
    
    <h1>Login</h1>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
