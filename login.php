<?php
session_start();
include 'config.php';
$loggedin = false; 
$value = bin2hex(random_bytes(20));
if(isset($_POST['submit'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sql_login = "SELECT * FROM users where username  = '$username' AND password =  '$password'";
    $result = mysqli_query($conn, $sql_login);
    if (mysqli_num_rows(($result)) > 0){
        $row = mysqli_fetch_assoc($result);
        setcookie("session", $value , time() + (86400 * 30), "/"); // 1 day
        $_SESSION['session'] = $value;
        $_SESSION['username'] = $row['username'];
        $_SESSION['id'] = $row['id'];
        header('Location: dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <form action="" method="POST">
        <div>
            <label for="">username</label>
            <input type="text" name="username">
        </div>
        <div>
            <label for="">password</label>
            <input type="password" name="password">
        </div>
        <input type="submit" value="Login" name="submit">
    </form>
</body>
</html>
