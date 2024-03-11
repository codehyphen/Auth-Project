<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- jQuery library -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>

    <!-- Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        .error{
            color: red;
        }
    </style>
</head>

<body>
    <span class="error"><?php echo (!empty($error) ? $error : '') ?></span><br>
    <h2>Create Account</h2>
    <form method="post">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter Username" value="<?=set_value('username', (!empty($username) ? $username : ''))?>"><br>
        <?=form_error('username')?>
        <label>Email</label>
        <input type="text" name="email" placeholder="Enter Email" value="<?=set_value('email', (!empty($email) ? $email : ''))?>"><br>
        <?=form_error('email')?>
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter Password" value="<?=set_value('password', (!empty($password) ? $password : ''))?>"><br>
        <?=form_error('password')?>
        <input type="submit" name="signup" value="Register">
    </form>

    <span>Already a User? <a href="/AuthProject/Users/login">Log-In</a></span>
</body>

</html>