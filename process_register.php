<?php
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);


    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO Users (Username, Password) VALUES (?, ?)";
        $params = [$username, $hashed_password];

        //do querry
        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();

            // handle existing username error
            if (!empty($errors) && strpos($errors[0]['message'], 'Violation of UNIQUE KEY') !== false) {
                $error_message = "Username already exists!";
            } else {
                $error_message = "An error occurred. Please try again.";
            }
        } else {
            //go back to login after registration
            header("Location: login.php");
            exit;
        }

        // free statement resource
        if (is_resource($stmt)) {
            sqlsrv_free_stmt($stmt);
        }
    }
}

sqlsrv_close($conn);
?>