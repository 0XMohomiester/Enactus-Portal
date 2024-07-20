<?php
include 'config.php';
session_start();
ini_set('display_errors', 0);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$DEFAULT_IMG = "https://as1.ftcdn.net/v2/jpg/03/46/83/96/1000_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg";
$email_sent = false;

if(empty($_SESSION['form_token'])){
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

if(isset($_COOKIE['session']) && is_string($_COOKIE['session']) && !empty($_COOKIE['session']) && isset($_SESSION['session']) && !empty($_SESSION['session']) ){
    if($_COOKIE['session'] === $_SESSION['session']){
        $id = $_SESSION['id'];
        $usersql = "SELECT role FROM users WHERE id = ?";
        $stmt = $conn->prepare($usersql);
        $stmt->bind_param("i", $id);
        $stmt->execute(); 
        $stmt->store_result();
        $stmt->bind_result($role);
        $stmt->fetch();

        /* personal_info */
        $sql_username_profile = "SELECT * FROM personal_info WHERE id = '$id'";
        $result = mysqli_query($conn, $sql_username_profile);
        if(mysqli_num_rows($result) == 1){
            $row = mysqli_fetch_assoc($result);
            $full_name = $row['Full_Name'];
            $nameParts = explode(" ", $full_name);
            $firstName = $nameParts[0];
            $user_id = $row['id'];
            $user_avatar = $row['Avatar'];
            $null = false;
            if($user_avatar == NULL || !(file_exists(__DIR__ . '/images/' . $user_avatar))){
                $null = true;
                $user_avatar = $DEFAULT_IMG;
            }
        }
        if(isset($_POST['submit_email'])){
            if (isset($_POST['form_token']) && is_string($_POST['form_token']) && hash_equals($_SESSION['form_token'], $_POST['form_token'])) {
                if(isset($_POST['subject']) && isset($_POST['message']) && is_string($_POST['subject']) && is_string($_POST['message'])){
                    $subject = $_POST['subject'];
                    $message = $_POST['message'];
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'hrenactus7@gmail.com';
                    $mail->Password = 'jeji rahc abdq ymfy';
                    $mail->SMTPSecure = 'ssl';
                    $mail->Port = '465';
                
                    $mail->setFrom('hrenactus7@gmail.com');
                    $mail->addAddress('nooreldin2002@gmail.com');
                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body   = $message;
                    $mail->send();
                    $email_sent = true; 
                    unset($_SESSION['form_token']);
                }
            }
        
        }
    }
    else{
        header("Location: login.php");
    }
}
else{
    header("Location: dashboard.php");
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
    <title>Report</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body style="background-color: #3B3B3B; color: white;">
<div class="w3-sidebar w3-bar-block w3-collapse w3-card w3-animate-left side_nav" style="background: rgb(37,37,37);background: linear-gradient(rgba(37,37,37,1) 0%, rgba(42,42,42,1) 45%, rgba(14,14,14,1) 100%);" id="mySidebar">
    <div>
        <img class="enactus_logo" src="CSS/imgs/enactus.png" alt="enactus">
    </div>
    <div class="navlinks">
        <ul>
            <li><a href="dashboard.php" class="navlink1" >Dashboard</a></li>
            <li><a href="tasks.php" class="navlink2">My Tasks</a></li>
            <li><a href="leader_board.php" class="navlink3">Leader board</a></li>
            <li><a href="announcements.php" class="navlink4">Announcements</a></li>
            <li><a href="#" class="navlink5">Report</a></li>
            <li><?php
                if($role === "admin_hr"){
                    echo '<a href="admininfo.php" class="navlink7">admin</a><br>';
                }?></li>
            <li class="logout"><a href="logout.php" class="navlink6">logout</a></li>      
        </ul>
    </div>
</div>

<div class="w3-main">
    <div class="up">
        <button class="up_button w3-button w3-xlarge w3-hide-large" id="sidebarOpener">&#9776;</button>  
        <div class="profile">
            <div class="profile_cont">
                <a href="profile.php" style="padding: 0; margin: 0; text-decoration: none;"><?php echo htmlspecialchars($firstName); ?></a>
                <p style="padding: 0; margin: 0; font-size: 10px; color: rgb(146, 146, 146);"><?php echo htmlspecialchars($user_id) ; ?></p>
            </div>
            <img style="border-radius: 50%; border: solid 1px #FEBF0F; width: 40px; height: 40px;" src="<?php
                if($null){
                    echo $user_avatar;
                }else{
                    echo 'images/' . $user_avatar;
                }
            ?>" alt="profile">
        </div>
    </div>
    <div class="container-md main_container">
        <div class="row justify-content-center main">
            <div class="col-lg-8 text-center maintext">
                <h1 class="title">Submit Your Report</h1>
                <p class="title">Your Voice, Your Vision, Our Future</p>
            </div>
        </div>  
        <div class="row justify-content-center main">
            <div class="col-lg-8">
                <form action="" method="post">
                    <input type="hidden" name="form_token" value="<?php echo $_SESSION['form_token'];?>">
                    <label for="subject">Subject:</label><br>   
                    <input class="subject" type="text" name="subject" required><br>
                    <label for="subject">Content:</label><br> 
                    <textarea class="content" id="message" name="message" required></textarea><br>
                    <div class="text-end">
                        <button class="send" type="submit" name="submit_email">send</button>
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