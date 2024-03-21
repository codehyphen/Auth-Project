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
    <span class="error"><?php echo (!empty($error) ? $error : '')?></span>
    <h2>Create New Password</h2>
    <form method="post">
        <label>Email or Username</label>
        <input type="text" name="emailorUsername" placeholder="Enter Email or Username" value="<?= set_value('emailorUsername', (!empty($emailorUsername) ? $emailorUsername : '')) ?>"><br>
        <?= form_error('email') ?>
        <label>New Password</label>
        <input type="password" name="password" placeholder="Enter Password"><br>
        <?= form_error('password') ?>
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" placeholder="Confirm Password"><br>
        <?= form_error('confirm_password') ?>
        <input type="submit" name="reset" value="Reset">
    </form>
</body>

</html>