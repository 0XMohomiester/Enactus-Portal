<?php
session_start();
include 'config.php';
$id_create_post = $_SESSION['id'];
$date = date("Y/m/d h:i:s");
$usersql = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($usersql);
$stmt->bind_param("i", $id_create_post);
$stmt->execute();  // send to the database
$stmt->store_result();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();



if(isset($_COOKIE['session']) && !empty($_COOKIE['session']) && isset($_SESSION['session']) && !empty($_SESSION['session'])){
    // Check if the Cookie equal to Session or not (Authentication)
    if($_COOKIE['session'] === $_SESSION['session']){
        // Check if the user is authorize to access this page or not (Authorization)
        if($role != 'admin'){
            header("Location: dashboard.php");
        }
        if(isset($_POST['submit'])){
            if(isset($_POST['title']) && isset($_POST['content']) && !empty($_POST['title']) && !empty($_POST['content'])){
                if(is_string($_POST['title']) && is_string($_POST['content'])){
                    $title = $_POST['title'];
                    $content = $_POST['content'];
                    $create_post_sql = "INSERT INTO `announcements` (`id`, `Title`, `Content`, `date`) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($create_post_sql);
                    $stmt->bind_param("isss", $id_create_post, $title, $content, $date);
                    $stmt->execute();
                    $stmt->close();
                    header("Location: announcements.php");
                }
                else{
                    header("Location: login.php");
                }
            }
            else{
                header("Location: login.php");
            }
        }
    }
    else{
        header("Location: login.php");
    }
}
else{
    header("Location: login.php");
}





?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="CSS/lato_font.css">
    <link rel="stylesheet" href="CSS/report_css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <title>Creat Post</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body style="background-color: #3B3B3B; color: white;">
<div class="w3-main">
    <div class="container-md main_container" style="margin-top: 100px;">
        <div class="row justify-content-center maincreat">
            <div class="col-lg-8 text-center maintext">
                <h1 class="title">Create a Post</h1>
            </div>
        </div>  
        <div class="row justify-content-center maincreat">
            <div class="col-lg-8">
                <form action="" method="post">
                    <label for="title">Title:</label><br>   
                    <input class="subject" type="text" name="title" required><br>
                    <label for="content">Content:</label><br> 
                    <textarea class="content" id="message" name="content" required></textarea><br>
                    <div class="text-end">
                        <button class="send" type="submit" name="submit">Create</button>
                    </div>
                </form>
            </div>
        </div>   
    </div>
</div>
<div class="circle1 animation4 c1"></div>
<div class="circle2 animation2 c2"></div>
<div class="circle3 animation3 c3"></div>
<div class="circle1 animation1 c4"></div>
<div class="circle3 animation3 c5"></div>
<div class="circle2 animation4 c6"></div>
<div class="circle3 animation2 c7"></div>
<div class="loader_warper">
    <div class="loader"> 
        <img src= "CSS/imgs/enactus.png"/> 
    </div> 
</div>
<div class="back">
    <a href="announcements.php"><i class="bi bi-arrow-left-short"></i></i></a>
</div>
<script>
    /////////////////////////////////////////
    //open and close sidebar
    $(document).ready(function(){
        $("#sidebarOpener").click(function(){
            $("#mySidebar").toggle();
        });

        $(document).mouseup(function(e) {
            var container = $("#mySidebar");
            if (!container.is(e.target) && container.has(e.target).length === 0) {
                container.hide();
            }
        });
    });
    function handleClickOutside(event) {
        const sidebar = document.getElementById("mySidebar");
        // If the click was outside the sidebar, close the sidebar
        if (!sidebar.contains(event.target)) {
            w3_close();
        }
    }
    //loader////
    $(window).on("load", function(){
        setTimeout(function(){
            $(".loader_warper").hide();
        }, 800);  // 3000 milliseconds = 3 seconds
    });
</script>

<script src="CSS/bootstrap/js/bootstrap.js"></script>
</body>
</html>