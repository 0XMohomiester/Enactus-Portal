<?php
session_start();
include 'config.php';

$DEFAULT_IMG = "https://as1.ftcdn.net/v2/jpg/03/46/83/96/1000_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg";

if(isset($_COOKIE['session']) && is_string($_COOKIE['session']) && !empty($_COOKIE['session']) && isset($_SESSION['session']) && !empty($_SESSION['session'])){
    if($_COOKIE['session'] === $_SESSION['session']){
        $myid = $_SESSION['id'];
        $usersql = "SELECT users.role, personal_info.Did FROM users JOIN personal_info ON users.id = personal_info.id WHERE users.id = ?";
        $stmt = $conn->prepare($usersql);
        $stmt->bind_param("i", $myid);
        $stmt->execute();  // send to the database
        $stmt->store_result();
        $stmt->bind_result($role, $Did);
        $stmt->fetch();
        $stmt->close();
        $iderror = false;

        if(!$role == 'admin_hr'){
            header("Location: dashboard.php");
        }
        /* personal_info */
        $sql_username_profile = "SELECT * FROM personal_info WHERE id = '$myid'";
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
        $searchdone = false;
        if(isset($_POST['search'])){
            $id = $_POST['id'];
            if(!empty($id)){
                $statsql = "SELECT * FROM statistics WHERE user_id = ?";
                $stmt = $conn->prepare($statsql);
                $stmt->bind_param("i", $id);
                $stmt->execute();  // send to the database
                $stmt->store_result();
                $stmt->bind_result($id, $hours, $lshours, $lhours, $warnings, $tasks);
                $stmt->fetch();
                $stmt->close();
                $searchdone = true;
                $_SESSION['search_id'] = $id;  
                } else {
                    $iderror = true;
                }
                
            }
        }
        if(isset($_POST['search'])){
            $id = $_POST['id'];
            if(!empty($id)){
                $persql = "SELECT * FROM personal_info WHERE id = ?";
                $stmt = $conn->prepare($persql);
                $stmt->bind_param("i", $id);
                $stmt->execute();  // send to the database
                $stmt->store_result();
                $stmt->bind_result($id, $Pdid, $Pfullname, $Pphone, $Pemail, $Plevel, $Pmajor,$Pavatar, $Page);
                $stmt->fetch();
                $stmt->close();
                $searchdone = true;
                $_SESSION['search_id'] = $id;  
                } else {
                    $iderror = true;
            }  
        }
        if(isset($_SESSION['search_id'])){
            $id = $_SESSION['search_id'];
        }
        $edit = isset($_POST['edit']) && is_string($_POST['edit'])? $_POST['edit'] : null;
        $number = isset($_POST['number']) && is_string($_POST['number'])? $_POST['number'] : null;
        $inc = isset($_POST['inc']) && is_string($_POST['inc'])? $_POST['inc'] : null;
        $dec = isset($_POST['dec']) && is_string($_POST['dec']) ? $_POST['dec'] : null;

        if (empty($_SESSION['form_token'])) {
            $_SESSION['form_token'] = bin2hex(random_bytes(32));
        }
        $token = $_SESSION['form_token'];
        if (isset($_POST['form_token']) && is_string($_POST['form_token']) && hash_equals($_SESSION['form_token'], $_POST['form_token'])) {
            if (!empty($number)) {
                if (isset($inc) && !empty($inc)) {
                    if ($edit === "hours") {
                        $sqlh = "UPDATE statistics SET hours = hours + ?, HLastMonth = HLastMonth + ? WHERE user_id = ?";
                        $stmt = mysqli_prepare($conn, $sqlh);
                        mysqli_stmt_bind_param($stmt, "iis", $number, $number, $id);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    } else if ($edit === "warnings") {
                        $sqlw = "UPDATE statistics SET warnings = warnings + ? WHERE user_id = ?";
                        $stmt = mysqli_prepare($conn, $sqlw);
                        mysqli_stmt_bind_param($stmt, "is", $number, $id);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    } else if ($edit === "tasks") {
                        $sqlt = "UPDATE statistics SET tasks = tasks + ? WHERE user_id = ?";
                        $stmt = mysqli_prepare($conn, $sqlt);
                        mysqli_stmt_bind_param($stmt, "is", $number, $id);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    }
                    unset($_SESSION['form_token']);
                }
                if (isset($dec) && !empty($dec)) {
                    if ($edit === "hours") {
                        $sqlh = "UPDATE statistics SET hours = hours - ?, HLastMonth = HLastMonth - ? WHERE user_id = ?";
                        $stmt = mysqli_prepare($conn, $sqlh);
                        mysqli_stmt_bind_param($stmt, "iis", $number, $number, $id);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    } else if ($edit === "warnings") {
                        $sqlw = "UPDATE statistics SET warnings = warnings - ? WHERE user_id = ?";
                        $stmt = mysqli_prepare($conn, $sqlw);
                        mysqli_stmt_bind_param($stmt, "is", $number, $id);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    } else if ($edit === "tasks") {
                        $sqlt = "UPDATE statistics SET tasks = tasks - ? WHERE user_id = ?";
                        $stmt = mysqli_prepare($conn, $sqlt);
                        mysqli_stmt_bind_param($stmt, "is", $number, $id);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    }
                    unset($_SESSION['form_token']);
                }
            }
        }
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
    <link rel="stylesheet" href="CSS/admin_css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <title>dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body style="background-color: #3B3B3B; color: white;">
    <div class="w3-sidebar w3-bar-block w3-collapse w3-card w3-animate-left side_nav" style="background: rgb(37,37,37);background: linear-gradient(rgba(37,37,37,1) 0%, rgba(42,42,42,1) 45%, rgba(14,14,14,1) 100%);" id="mySidebar">
        <!-- <button class="w3-bar-item w3-button w3-large w3-hide-large" onclick="w3_close()">Close &times;</button> -->
        <div>
            <img class="enactus_logo" src="CSS/imgs/enactus.png" alt="enactus">
        </div>
        <div class="navlinks">
            <ul>
                <li><a href="dashboard.php" class="navlink1" >Dashboard</a></li>
                <li><a href="tasks.php" class="navlink2">My Tasks</a></li>
                <li><a href="leader_board.php" class="navlink3">Leader board</a></li>
                <li><a href="announcements.php" class="navlink4">Announcements</a></li>
                <li><a href="report.php" class="navlink5">Report</a></li>
                <li><a href="#" class="navlink7">admin</a></li>
                <li class="logout"><a href="logout.php" class="navlink6" style="color:#FEBF0F;"><i class="bi bi-box-arrow-left" style="margin-right: 5px"></i>logout</a></li>      
            </ul>
        </div>
    </div>
        
    <div class="w3-main">
        <div class="up">
            <button class="up_button w3-button w3-xlarge w3-hide-large" id="sidebarOpener">&#9776;</button>  
            <div class="profile">
                <div class="profile_cont">
                    <a href="profile.php" style="padding: 0; margin: 0; text-decoration: none;"><?php echo $firstName; ?></a>
                    <p style="padding: 0; margin: 0; font-size: 10px; color: rgb(146, 146, 146);"><?php echo $user_id; ?></p>
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
        <div class="container-md">
            <div class="row search text-center">
                <div class="col">
                    <p class="welcome">Welcome admin</p>
                    <p class="search_id yellow">search for an id:</p>
                    <form action="" method="post">
                        <input type="text" name="id" required>
                        <input style="padding-left: 3px; padding-right: 3px;" type="submit" value="search" name="search"><br>
                        <?php
                            if($iderror){
                                echo "Incorrect id";
                            }
                        ?>
                    </form>
                </div>
            </div>
            <?php

        if($searchdone){
            $number = $hours/60.0;
            $hours = number_format($number, 2);
            $number = $lshours/60.0;
            $lshours = number_format($number, 2);
            $number = $lhours/60.0;
            $lhours = number_format($number, 2);
            echo "<div class='row data'>
            <div class='col'>
                <div class='container'>
                    <div class='row'>
                        <div class='col'>
                            <p>ID: $id</p>
                            <p>Full Name: $Pfullname</p>
                            <p>Phone: $Pphone</p>
                            <p>Age: $Page</p>
                            <p>Hours: $hours</p>
                            <p>Warnings: $warnings</p>
                            <p>Tasks: $tasks</p>
                        </div>
                            <div style='border-left: 1px solid white; padding-left: 30px;' class='col'>
                            <p>Department: $Pdid</p>
                            <p>Email: $Pemail</p>
                            <p>Level: $Plevel</p>
                            <p>Major: $Pmajor</p>
                            <p>Last Season Hours: $lshours</p>
                            <p>Last Month Hours: $lhours</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class='row edit_main text-center'>
                <div class='col'>             
                    <p class='search_id yellow'>Please select data then enter a number to add or subtract:</p>
                    <p class='search_id yellow' style='margin-bottom: 20px;'>NOTE: Please enter the hours by MINUTES!</p>
                    <form action='' method='post'>
                        <input type='hidden' name='form_token' value='$token'>
                        <select class='edit_select' name='edit' id='edit'>
                            <option value='hours'>Hours</option>
                            <option value='warnings'>Warnings</option>
                            <option value='tasks'>Tasks</option>
                        </select>
                        <input class='edit_txt' type='text' name='number' id='number'>
                        <input class='edit_button pos' type='submit' name='inc' id='inc' value='+'>
                        <input class='edit_button neg' type='submit' name='dec' id='dec' value='-'>
                    </form>
                </div>
            </div>";
        }
        
    ?>
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
    <script src="bootstrap/js/bootstrap.js"></script>
</body>
</html>
