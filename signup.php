<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "../db/db.php";
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = $_POST["role"];

    $conn->query("INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')");
    echo "Sign up successful! <a href='../login/login.php'>Login</a>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
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

        input, select, button {
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
    
    <h1>Sign Up</h1>
    <form method="POST">
        <input type="text" name="name" placeholder="Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <select name="role">
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
        </select><br>
        <button type="submit">Sign Up</button>
    </form>
</body>
</html>
