<?php 
session_start();
include "config.php";

$DEFAULT_IMG = "https://as1.ftcdn.net/v2/jpg/03/46/83/96/1000_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg"; 

$error_while_update_avatar = false;
$error_of_img_size = false;
$error_of_no_file_uploaded = false;

if(isset($_COOKIE['session']) && !empty($_COOKIE['session']) && is_string($_COOKIE['session']) && isset($_SESSION['session']) && !empty($_SESSION['session'])){
    if($_COOKIE['session'] === $_SESSION['session']){
        $id_profile = $_SESSION['id'];
        $sql_username_profile = "SELECT * FROM personal_info WHERE id = '$id_profile'";
        $result = mysqli_query($conn, $sql_username_profile);
        if(mysqli_num_rows($result) == 1){
            $row = mysqli_fetch_assoc($result);
            $full_name = $row['Full_Name'];
            $nameParts = explode(" ", $full_name);
            $firstName = $nameParts[0];
            $user_avatar = $row['Avatar'];
            $user_id = $row['id'];
            if($user_avatar == NULL){
                $user_avatar = $DEFAULT_IMG;
            }
            $age = $row['age'];
            $email = $row['Email'];
            $phone = $row['Phone'];
            $dep = $row['Did'];
            $major = $row['Major'];
            $level = $row['Level'];
        }
        function change_avatar($user_avatar){
            include "config.php";
            global $conn;
            // Array ( [name] => 1.png [full_path] => 1.png [type] => image/png [tmp_name] => /private/var/folders/qp/phsq7g5s167d7rs5xswpxql00000gn/T/phpZlNizt [error] => 0 [size] => 64369 )
            $max_size = 2 * 1024 * 1024;
            $img = $_FILES['myimg'];
            $img_name = $_FILES['myimg']['name'];
            $size_of_img =  $_FILES['myimg']['size'];
            $img_extension = pathinfo($img_name, PATHINFO_EXTENSION);
            $content_type_of_img = $_FILES['myimg']['type'];
            $tmp_path = $_FILES['myimg']['tmp_name'];
            if($size_of_img <= $max_size){
                if(strtolower($img_extension) === "png" || strtolower($img_extension) === "jpeg" || strtolower($img_extension) === "jpg"){
                    if($content_type_of_img === "image/png" || $content_type_of_img === "image/jpeg"){
                        if(mime_content_type($tmp_path) === "image/png" || mime_content_type($tmp_path) === "image/jpeg"){
                            if (file_exists(__DIR__ . "/images/".  $user_avatar)){
                                unlink(__DIR__ . "/images/".  $user_avatar);
                            }
                            $rename_img = bin2hex(random_bytes(5)) . "." . "$img_extension";
                            $full_upload_path =  __DIR__ . "/images/" . $rename_img;
                            move_uploaded_file($tmp_path, $full_upload_path);
                            $id_logged_in_avatar = $_SESSION['id'];
                            $sql_avatar = "UPDATE personal_info set Avatar = '$rename_img' where id = '$id_logged_in_avatar'";
                            $result = mysqli_query($conn, $sql_avatar);
                            if($result){
                                return $rename_img;
                            }
                            else{
                                $error_while_update_avatar = true;
                                return;
                            }
                        }
                    }
                }
            }
            else{
                global $error_of_img_size;
                $error_of_img_size = true;
                return;
            }
        }

        if(isset($_POST['upload'])){
            if($_FILES['myimg']['size'] > 0){
                // Function take old image and return new image.
                $user_avatar = change_avatar($user_avatar);
                if($user_avatar == NULL){
                    $user_avatar = $row['Avatar'];
                }
            }
            else{
                $error_of_no_file_uploaded = true;
            }
        }
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
    <link rel="stylesheet" href="CSS/profile_css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <title>Profile</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body style="background-color: #3B3B3B; color: white;">
    <div class="container-lg ">
        <div class="row text-center justify-content-center">
            <div class="col">
                <img class="photo" src="<?php 
                        // If there is an error it will set a default avatar.
                        if($error_of_img_size == true || $error_while_update_avatar == true || $error_of_no_file_uploaded == true){ 
                            // new user need to change avatar, if uploadded photo is large, or any erro occur.
                            if($user_avatar == NULL){
                                $user_avatar = $DEFAULT_IMG;
                                echo $user_avatar;
                            }
                            // old user need to change avatar but there is an error occur while upload, it will check if the last avatar still exist in server and it will print it.
                            else{
                                if(file_exists("images/" . $user_avatar)){
                                    echo "images/" . $user_avatar;
                                }
                                else{
                                    $user_avatar = $DEFAULT_IMG;
                                    echo $user_avatar;
                                }
                            }
                        }
                        else {
                            // if new user visit profile page.
                            if($user_avatar == NULL){
                                $user_avatar = $DEFAULT_IMG;
                                echo $user_avatar;
                            }
                            // if old user visit profile page.
                            else{
                                // if photo exist in server and db.
                                if(file_exists('images/' . $user_avatar)){
                                    echo 'images/' . $user_avatar;
                                }
                                // photo maybe stored in db and not in server for some reasons, to   
                                else{
                                    $user_avatar = $DEFAULT_IMG;
                                    echo $user_avatar;
                                }
                                
                            }
                    } ?>">
                <p class="name"><?php echo $firstName ?></p>
                <p style="color:red;"><?php if($error_of_img_size == true){echo "File is to large<br>Maximum size is 2MB";} if($error_while_update_avatar == true){echo "There is an error while update your image";} if($error_of_no_file_uploaded == true){echo "Please Upload an image";} ?></p>
                <form action="" method="post" enctype="multipart/form-data">
                    <label for="file" class="custom-file-upload">
                        Select a photo
                    </label>
                    <input id="file" type="file" name="myimg" value="" accept=".png, .jpeg, .jpg">
                    <input class="upload" type="submit" name="upload" value="upload"><br>
                </form>
            </div>
        </div>
        <div class="row info">
            <div class="col-lg-6">
                <p><?php echo "ID: " .  @$user_id; ?></p>
                <p><?php echo "Name: " . @$full_name; ?></p>
                <p><?php echo "Age: " .  @$age; ?></p>
                <p><?php echo "Dep: " . @$dep; ?></p>
            </div>
            <div class="col-lg-6 info_right" >
                <p><?php echo "Email: " .  @$email; ?></p>
                <p><?php echo "Phone: " . @$phone; ?></p>
                <p><?php echo "Major: " .  @$major; ?></p>
                <p><?php echo "Level: " . @$level; ?></p>
            </div>
        </div>
    </div>
    <div class="back">
        <a href="dashboard.php"><i class="bi bi-arrow-left-short"></i></i></a>
    </div>
    <div class="logout">
        <a href="logout.php">Logout</a>
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
        //loader////
        $(window).on("load", function(){
            setTimeout(function(){
                $(".loader_warper").hide();
            }, 800);  // 3000 milliseconds = 3 seconds
        });
    </script>
    <script src="CSS/bootstrap/js/bootstrap.js"></scrip>
</body>
</html>