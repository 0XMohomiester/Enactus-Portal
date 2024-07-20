<?php
session_start();
include 'config.php';

$DEFAULT_IMG = "https://as1.ftcdn.net/v2/jpg/03/46/83/96/1000_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg";

if(isset($_COOKIE['session']) && is_string($_COOKIE['session']) && !empty($_COOKIE['session']) && isset($_SESSION['session']) && !empty($_SESSION['session'])){
    
    if($_COOKIE['session'] === $_SESSION['session']){
        $id = $_SESSION['id'];

        $user_info_sql = "SELECT role from users where id = ?";
        try{
            $stmt = $conn->prepare($user_info_sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();  // send to the database
            // No need to check if the number of records return because id is PK (unique).
            $stmt->bind_result($role);
            $stmt->fetch();
            $stmt->close();
        }
        catch(Exception $exception_dashboard1){
        }

        $select_post_sql = "SELECT 
        announcements.id, 
        announcements.Title, 
        announcements.Content, 
        announcements.date, 
        users.id, 
        users.username
    FROM 
        announcements
    JOIN 
        users ON announcements.id = users.id
    ORDER BY 
        announcements.date DESC;";
        $result = $conn->query($select_post_sql);

    /* personal_info */
    $sql_username_profile = "SELECT * FROM personal_info WHERE id = '$id'";
    $result_prof = mysqli_query($conn, $sql_username_profile);
    if(mysqli_num_rows($result_prof) == 1){
        $row = mysqli_fetch_assoc($result_prof);
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
    <link rel="stylesheet" href="CSS/anouncements_css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <title>announcements</title>
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
                <li><a href="#" class="navlink4">Announcements</a></li>
                <li><a href="report.php" class="navlink5">Report</a></li>
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
    </div>
    <div class="container-md main_container">
        <div class="row justify-content-center main">
            <div class="col-lg-8 text-center maintext">
                <h1 class="maintitle">Announcements</h1>
            </div>
        </div>
    </div>
    <?php 
        $creat = false;
        $usersql = "SELECT role FROM users WHERE id = ?";
        $stmt = $conn->prepare($usersql);
        $stmt->bind_param("i", $id);
        $stmt->execute();  // send to the database
        $stmt->store_result();
        $stmt->bind_result($role);
        $stmt->fetch();
        if($role === 'admin'){
            echo "<div class='creat text-end'><a href='create_post.php'>+New Post</a></div>";
            $creat = true;
        }
    ?>
    
        <?php
            if ($result->num_rows > 0) {
                if(!$creat){
                    $margin = 'margin-top: 70px;';
                }else{
                    $margin = '';
                }
                echo "<div class='container maincont' style='$margin'>";
                
                while ($row = $result->fetch_assoc()) {
                    $currentid = $row['id'];
                    $currentusername = $row['username'];
                    $currentdate = $row['date'];
                    $dateParts = explode(" ", $currentdate);
                    $date = $dateParts[0];
                    $currenttitle = $row['Title'];
                    $currentcontent = $row['Content'];

                    $sql_username_profile_cur = "SELECT * FROM personal_info WHERE id = '$currentid'";
                    $result_prof_cur = mysqli_query($conn, $sql_username_profile_cur);
                    if(mysqli_num_rows($result_prof_cur) == 1){
                        $row_cur = mysqli_fetch_assoc($result_prof_cur);
                        $full_name_cur = $row_cur['Full_Name'];
                        $nameParts_cur = explode(" ", $full_name_cur);
                        $firstName_cur = $nameParts_cur[0] . " " . end($nameParts_cur);
                        $user_avatar_cur = $row_cur['Avatar'];
                        $null_cur = false;
                        if($user_avatar_cur == NULL || !(file_exists(__DIR__ . '/images/' . $user_avatar_cur))){
                            $null_cur = true;
                            $user_avatar_cur = $DEFAULT_IMG;
                        }
                    }
                    if(!$null_cur){
                        $user_avatar_cur = 'images/' . $user_avatar_cur;
                    }
                    echo "<div class='row singlecontent'>
                    <div class='container'>
                        <div class='row'>
                            <div class='col-1' style='padding: 0; margin: 0; width: 40px;'>
                                <img style='border-radius: 50%; border: solid 1px #FEBF0F; width: 40px; height: 40px;' src='" . htmlspecialchars($user_avatar_cur, ENT_QUOTES, 'UTF-8') . "' alt='profile'>
                            </div>
                            <div class='col-2'>
                                <p class='name'>" . htmlspecialchars($firstName_cur, ENT_QUOTES, 'UTF-8') . "</p>
                            </div>
                            <div class='col-9 text-end datecur'>
                                <p class='date'>" . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . "</p>
                            </div>
                        </div>
                        <div class='row'>
                            <p class='title'>" . htmlspecialchars($currenttitle, ENT_QUOTES, 'UTF-8') . "</p>
                        </div>
                        <div class='row'>
                            <p>" . htmlspecialchars($currentcontent, ENT_QUOTES, 'UTF-8') . "</p>
                        </div>
                    </div>
                </div>
                <hr>";
                
                    }
                    echo "</div>";
                    $no = false;
                }else {
                $no = true;
            }
        ?>
    <?php
        if($no){
            echo "<h1 class='no text-center'>No announcements yet.</h1>";
        }
    ?>
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