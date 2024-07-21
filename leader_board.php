<?php
session_start();
include 'config.php';
$DEFAULT_IMG = "https://as1.ftcdn.net/v2/jpg/03/46/83/96/1000_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg";
$time_period = isset($_POST['time_period']) && is_string($_POST['time_period']) ? $_POST['time_period'] : 'all_time';


if(isset($_COOKIE['session']) && is_string($_COOKIE['session']) &&!empty($_COOKIE['session']) && isset($_SESSION['session']) && !empty($_SESSION['session']) ){
    if($_COOKIE['session'] === $_SESSION['session']){

        $id = $_SESSION['id'];
        $usersql = "SELECT users.role, statistics.hours, statistics.HLastMonth, statistics.HLastSeason FROM users JOIN statistics ON users.id = statistics.user_id WHERE users.id = ?";
        $stmt = $conn->prepare($usersql);
        $stmt->bind_param("i", $id);
        $stmt->execute();  // send to the database
        $stmt->store_result();
        $stmt->bind_result($role, $hours, $HLastMonth, $HLastSeason);
        $stmt->fetch();
        $stmt->close();

        $column = 'hours';
        if ($time_period === 'last_month') {
            $column = 'HLastMonth';
            $hours = $HLastMonth;
        }else if($time_period === 'last_season'){
            $column = 'HLastSeason';
            $hours = $HLastSeason;
        }
        // Query to get users ordered by hours
        $sql = "SELECT user_id, $column FROM statistics ORDER BY $column DESC";
        $result = $conn->query($sql);
        
        // Display leaderboard
        $current_rank;
        $current_hours;
        if ($result->num_rows > 0) {
            $rank = 1;
            while ($row = $result->fetch_assoc()) {
                if($row['user_id'] == $id){
                    $current_rank = $rank;
                    break;
                }
                $rank++;
            }
            $number = $hours/60.0;
            $current_hours = number_format($number, 2);
        } else {
            echo "No users found.";
        }

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
    <link rel="stylesheet" href="CSS/leaderboard_css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <title>Leaader board</title>
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
                <li><a href="#" class="navlink3">Leader board</a></li>
                <li><a href="announcements.php" class="navlink4">Announcements</a></li>
                <li><a href="report.php" class="navlink5">Report</a></li>
                <li><?php
                    if($role === "admin_hr"){
                        echo '<a href="admininfo.php" class="navlink7">admin</a><br>';
                    }?></li>
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
        <div class="container-md text-center">
            <div class="row justify-content-center leaderboard">
                <div class="col-lg-8">
                    <div class="container">
                        <div class="row">
                            <p class="take_action lato_thin">Take action and top the leader board!</p>
                            <p class="take_actionsm lato_thin">Do your tasks to gain more hours.</p>
                       </div>
                       <div class="row lato_thin">
                            <div class="col">
                                <p class="personal_number"><?php echo $current_rank ?></p>
                                <p>position</p>
                            </div>
                            <div class="col">
                                <p class="personal_number"><?php echo $current_hours?></p>
                                <p>hours</p>
                            </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                            <form action="" method="post">
                                <select name="time_period" class="select">
                                    <option value="all_time">All Time</option>
                                    <option value="last_season">Last Season</option>
                                    <option value="last_month">Last Month</option>
                                </select>
                                <input class="submit_select" type="submit" value="Select">
                            </form>
                    </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center leaderboard">
                <div class="col-lg-8">
                    <div class="container-fluid">
                        <div class="row">
                            <table class="table align-middle lato_thin">
                                <tbody>
                                    <?php
                                        // Query to get users ordered by hours
                                        
                                        $sql_all_stat = "SELECT user_id, $column FROM statistics ORDER BY $column DESC";
                                        $result_stat = $conn->query($sql_all_stat);
                                        // Display leaderboard
                                        $rank_lb = 1;
                                        while ($row = $result_stat->fetch_assoc()) {
                                                
                                                $row_id= $row['user_id'];
                                                $row_h = $row[$column];
                                                $sql_user_lb = "SELECT * FROM personal_info WHERE id = '$row_id'";
                                                $result_lb = mysqli_query($conn, $sql_user_lb);
                                                if(mysqli_num_rows($result_lb) == 1){
                                                    $row_user = mysqli_fetch_assoc($result_lb);
                                                    $full_name_lb = $row_user['Full_Name'];
                                                    // $nameParts_lb = explode(" ", $full_name_lb);
                                                    // $firstName_lb = $nameParts_lb[0] . " " . end($nameParts_lb);
                                                    $user_avatar_lb = $row_user['Avatar'];
                                                    $null_lb = false;
                                                    if($user_avatar_lb == NULL || !(file_exists(__DIR__ . '/images/' . $user_avatar_lb))){
                                                        $null_lb = true;
                                                        $user_avatar_lb = $DEFAULT_IMG;
                                                    }
                                                }
                                                $number = $row_h/60.0;
                                                $formatted_number = number_format($number, 2);
                                                echo "<tr>" . "<th scope = 'row'>" . htmlspecialchars($rank_lb) . "</th>"; 
                                                if($null_lb){
                                                    echo "<td><img style='border-radius: 50%; border: solid 1px #FEBF0F; width: 40px; height: 40px;' src='$user_avatar_lb' alt='profile'></td>";
                                                }else{
                                                    echo "<td><img style='border-radius: 50%; border: solid 1px #FEBF0F; width: 40px; height: 40px;' src='images/$user_avatar_lb' alt='profile'></td>";
                                                }                        
                                                echo "<td>" . htmlspecialchars($full_name_lb) ."</td>" . "<td>" . htmlspecialchars($formatted_number)  . "</td>"
                                                . "</tr>";
                                                $rank_lb++;
                                                if($rank_lb == 6){
                                                    break;
                                                }
                                            }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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