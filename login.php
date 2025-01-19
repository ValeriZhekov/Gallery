<?php
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: gallery.php");
    exit;
}

$error_message = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }

        .login-container {
            background: #fff;
            padding: 20px 30px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }

        .login-container h1 {
            margin-bottom: 20px;
        }

        .login-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .login-container button {
            padding: 10px 15px;
            width: 100%;
            background: #007bff;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .login-container button:hover {
            background: #0056b3;
        }

        .login-container .register-button {
            background: #28a745;
        }

        .login-container .register-button:hover {
            background: #218838;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h1>Login</h1>
        <form action="process_login.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <button class="register-button" onclick="location.href='register.php'">Register</button>
        <?php if ($error_message): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
    </div>
</body>

</html>