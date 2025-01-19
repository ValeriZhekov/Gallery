<?php
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);


    if (empty($username) || empty($password)) {
        $error_message = "Please provide both username and password.";
        header("Location: login.php?error=" . urlencode($error_message));
        exit;
    }


    $query = "SELECT * FROM Users WHERE Username = ?";
    $params = [$username];


    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }


    $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if ($user) {

        if (password_verify($password, $user['Password'])) {

            session_start();
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['Username'];


            header("Location: gallery.php");
            exit;
        } else {
            $error_message = "Invalid username or password.";
        }
    } else {
        $error_message = "Invalid username or password.";
    }


    sqlsrv_free_stmt($stmt);


    header("Location: login.php?error=" . urlencode($error_message));
    exit;
}


sqlsrv_close($conn);
?>