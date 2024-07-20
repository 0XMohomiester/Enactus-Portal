<?php
session_start();
include 'config.php';

$DEFAULT_IMG = "https://as1.ftcdn.net/v2/jpg/03/46/83/96/1000_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg";
function truncateContent($content, $maxLength) {
    // Check if the content length is greater than the maximum length
    if (strlen($content) > $maxLength) {
        // Truncate the content and add "..."
        return substr($content, 0, $maxLength) . "...";
    } else {
        // Return the content as is if it's within the limit
        return $content;
    }
}

if(isset($_COOKIE['session']) && is_string($_COOKIE['session']) && !empty($_COOKIE['session']) && isset($_SESSION['session']) && !empty($_SESSION['session']) ){
    
    if($_COOKIE['session'] === $_SESSION['session']){
        // Fetch loggedin user information from database.
        $user_id_session = $_SESSION['id'];
        $user_info_sql = "SELECT id,role from users where id = ?";
        try{
            $stmt = $conn->prepare($user_info_sql);
            $stmt->bind_param("i", $user_id_session);
            $stmt->execute();  // send to the database
            // No need to check if the number of records return because id is PK (unique).
            $stmt->bind_result($id, $role);
            $stmt->fetch();
            $stmt->close();
        }
        catch(Exception $exception_dashboard1){
        }

        /* statistics */
        $statsql = "SELECT * FROM statistics WHERE user_id = '$id'"; // select all from statistics
        $statres = mysqli_query($conn, $statsql);
        $statrows = mysqli_num_rows($statres);
        $statrow = mysqli_fetch_assoc($statres);
        if($statrows == 1){ 
            $hoursnum = $statrow['hours'];
            $number = $hoursnum/60.0;
            $hours = number_format($number, 2);
            $warnings = $statrow['warnings'];
            $tasks = $statrow['tasks'];
        }
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
        /* lastanoucment */
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
            announcements.date ASC;";

        $result = $conn->query($select_post_sql);
        $no_annouc = false;
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $username = $row['username'];
                $title = $row['Title'];
                $full_date = $row['date'];
                $dateParts = explode(" ", $full_date);
                $date = $dateParts[0];
                if (!function_exists('truncateContent')) {
                    function truncateContent($content, $maxLength) {
                        // Check if the content length is greater than the maximum length
                        if (strlen($content) > $maxLength) {
                            // Truncate the content and add "..."
                            return substr($content, 0, $maxLength) . "...";
                        } else {
                            // Return the content as is if it's within the limit
                            return $content;
                        }
                    }
                }
                
                // Example usage
                $content = $row['Content'];
                // $content = "safkasjfkasjf sfjkasjfjksajfkjkasjfkasjjfksjafkjaskjfkajsfjaksjfaj";
                $maxLength = 25;
                $truncatedContent = truncateContent($content, $maxLength);
            }
        }else{
            $no_annouc = true;
        }
    
    }
}else{
    header('Location: login.php');
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
    <link rel="stylesheet" href="CSS/dashboard_css/style.css">
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
            <li><a href="#" class="navlink1" >Dashboard</a></li>
            <li><a href="tasks.php" class="navlink2">My Tasks</a></li>
            <li><a href="leader_board.php" class="navlink3">Leader board</a></li>
            <li><a href="announcements.php" class="navlink4">Announcements</a></li>
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
    <div class="main_container container-md">
        <div class="row Welcome">
            <div class="col-lg-8">
                <div class="container">
                    <div class="row date">
                        <?php echo date("Y/m/d"); ?>
                    </div>
                    <div class="row Welcometext">
                        Welcome back, <?php echo $firstName; ?>
                    </div>
                    <div class="row slogan">
                        always stay updated in enactus portal
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <img class="enactus_logo2" src="CSS/imgs/Enactus_Logo_W.png" alt="enactus2">
            </div>
        </div>
        <div class="row main justify-content-center">
            <div class="col-lg-3 text-center maincont_color maincont"  style="height: 150px;">
                <div class="container"> <!---hours--->
                    <div class="row">
                        <i class="bi bi-clock yellow main_icons"></i>
                    </div>
                    <div class="row">
                        <p class="main_number n1"><?php echo $hours; ?></p>
                    </div>
                    <div class="row">
                        <p>hours</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 text-center maincont_color maincont"  style="height: 150px;">
                <div class="container"> <!---warnings--->
                    <div class="row">
                        <i class="bi bi-exclamation-circle yellow main_icons"></i>
                    </div>
                    <div class="row">
                        <p class="main_number n2"><?php echo $warnings; ?></p>
                    </div>
                    <div class="row">
                        <p>warnings</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 text-center maincont_color maincont" style="height: 150px;">
                <div class="container"> <!---tasks--->
                    <div class="row">
                        <i class="bi bi-list-check yellow main_icons"></i>
                    </div>
                    <div class="row">
                        <p class="main_number n3"><?php echo $tasks; ?></p>
                    </div>
                    <div class="row">
                        <p>tasks</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 maincont ">
                <div class="container"> <!---daily--->
                    <div class="row dailytitle">
                        <div class="col">
                            <p>Daily Notice</p>
                        </div>
                        <div class="col">
                            <a href="announcements.php" class="yellow text-end seeall">see all</a>
                        </div>
                    </div>
                    <div class="row maincont_color Daily">
                        <?php
                            if(!$no_annouc){
                                $secure_title = htmlspecialchars($title);
                                $secure_content = htmlspecialchars($truncatedContent);
                                echo "<div class='container'>
                                <div class='row dailycont'>
                                    <div class='col'><p>$username</p></div>
                                    <div class='col text-end'><p>$date</p></div>
                                </div>
                                <div class='row dailycont'>
                                    <p>$secure_title</p>
                                </div>
                                <div class='row dailycont'>
                                    <p>$secure_content</p>
                                </div>
                            </div>";
                            }else{
                                echo "<p>No Announcements Yet</p>";
                            }
                        ?>    
                    </div>
                </div>
            </div>
            <div class="row video">
                <video src="videos/Eco_Rice.mp4" controls autoplay muted></video>
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
        //////////////////////////////////////////////////////////////
        // JavaScript to increment the number from 0 to the target number
        // document.addEventListener('DOMContentLoaded', () => {
        //     const numberElement = document.querySelector('.n1');
        //     const targetNumber = parseInt(numberElement.textContent);
        //     let currentNumber = 0;  // Start from 0
        //     let incrementStep = 1;  // Define the increment step
        //     let incrementInterval = 30;  // Define the interval in milliseconds

        //     const incrementNumber = () => {
        //         if (currentNumber < targetNumber) {
        //             currentNumber += incrementStep;
        //             if (currentNumber > targetNumber) {
        //                 currentNumber = targetNumber;  // Ensure it doesn't go beyond target
        //             }
        //             numberElement.textContent = currentNumber;
        //             setTimeout(incrementNumber, incrementInterval);
        //         }
        //     };

        //     incrementNumber();
        // });
        // JavaScript to increment the number from 0 to the target number
        document.addEventListener('DOMContentLoaded', () => {
            const numberElement = document.querySelector('.n2');
            const targetNumber = parseInt(numberElement.textContent);
            let currentNumber = 0;  // Start from 0
            let incrementStep = 1;  // Define the increment step
            let incrementInterval = 600;  // Define the interval in milliseconds

            const incrementNumber = () => {
                if (currentNumber < targetNumber) {
                    currentNumber += incrementStep;
                    if (currentNumber > targetNumber) {
                        currentNumber = targetNumber;  // Ensure it doesn't go beyond target
                    }
                    numberElement.textContent = currentNumber;
                    setTimeout(incrementNumber, incrementInterval);
                }
            };

            incrementNumber();
        });
        // JavaScript to increment the number from 0 to the target number
        document.addEventListener('DOMContentLoaded', () => {
            const numberElement = document.querySelector('.n3');
            const targetNumber = parseInt(numberElement.textContent);
            let currentNumber = 0;  // Start from 0
            let incrementStep = 1;  // Define the increment step
            let incrementInterval = 200;  // Define the interval in milliseconds

            const incrementNumber = () => {
                if (currentNumber < targetNumber) {
                    currentNumber += incrementStep;
                    if (currentNumber > targetNumber) {
                        currentNumber = targetNumber;  // Ensure it doesn't go beyond target
                    }
                    numberElement.textContent = currentNumber;
                    setTimeout(incrementNumber, incrementInterval);
                }
            };

            incrementNumber();
        });
        ///////////////////////////////////////////////////////////////
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
   