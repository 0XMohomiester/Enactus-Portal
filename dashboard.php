<?php
include 'config.php';
session_start();
if(isset($_COOKIE['session']) && !empty($_COOKIE['session']) && isset($_SESSION['session']) && !empty($_SESSION['session'])){
    if($_COOKIE['session'] === $_SESSION['session']){
        $id_logged_in = $_SESSION['id'];
        $sql_total_hours = "SELECT * FROM statistics WHERE user_id = '$id_logged_in'";
        $result = mysqli_query($conn, $sql_total_hours);
        if (mysqli_num_rows(($result)) == 1){
            $row = mysqli_fetch_assoc($result);
            echo "<h1>Welcome " . $_SESSION['username'] . "</h1>";
            echo "Your Total Hours: " . $row['hours'] . "<br>";
            echo "Your Total Tasks: " . $row['tasks'] . "<br>";
            echo "Your Total Warnings: " . $row['warnings'] . "<br>";
        }

    }
}
else{
    header('Location: login.php');
}

?>

<!DOCTYPE html>
<html>
    <head><title>dashboard</title></head>
    <body>
        <a href="/logout.php">Logout</a>
        <a href="/profile.php">Profile</a>
    </body>
</html>