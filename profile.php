<?php 
session_start();
include("config.php");

$DEFAULT_IMG = "https://as1.ftcdn.net/v2/jpg/03/46/83/96/1000_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg"; 

$error_while_update_avatar = false;
$error_of_img_size = false;
$error_of_no_file_uploaded = false;

if(isset($_COOKIE['session']) && !empty($_COOKIE['session']) && isset($_SESSION['session']) && !empty($_SESSION['session'])){
    if($_COOKIE['session'] === $_SESSION['session']){
        $id_profile = $_SESSION['id'];
        $sql_username_profile = "SELECT * FROM personal_info WHERE id = '$id_profile'";
        $result = mysqli_query($conn, $sql_username_profile);
        if(mysqli_num_rows($result) == 1){
            $row = mysqli_fetch_assoc($result);
            $full_name = $row['Full_Name'];
            $user_avatar = $row['Avatar'];
            $user_id = $row['id'];
            if($user_avatar == NULL){
                $user_avatar = $DEFAULT_IMG;
            }
        }
        function change_avatar($user_avatar){
            include("config.php");
            // Array ( [name] => 1.png [full_path] => 1.png [type] => image/png [tmp_name] => /private/var/folders/qp/phsq7g5s167d7rs5xswpxql00000gn/T/phpZlNizt [error] => 0 [size] => 64369 )
            $img = $_FILES['myimg'];
            $img_name = $_FILES['myimg']['name'];
            $size_of_img =  $_FILES['myimg']['size'];
            $img_extension = pathinfo($img_name, PATHINFO_EXTENSION);
            $content_type_of_img = $_FILES['myimg']['type'];
            $tmp_path = $_FILES['myimg']['tmp_name'];
            if($size_of_img < 800000){
                if(strtolower($img_extension) === "png" || strtolower($img_extension) === "jpeg" || strtolower($img_extension) === "jpg"){
                    if($content_type_of_img === "image/png" || $content_type_of_img === "image/jpeg"){
                        if(mime_content_type($tmp_path) === "image/png" || mime_content_type($tmp_path) === "image/jpeg"){
                            if (file_exists("images/$user_avatar")){
                                unlink("images/$user_avatar");
                            }
                            $rename_img = bin2hex(random_bytes(5)) . "." ."$img_extension";
                            $full_upload_path = $_SERVER['DOCUMENT_ROOT'] . "/images/" . $rename_img;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
</head>
<body>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="myimg" value="" accept=".png, .jpeg, .jpg"><br>
        <input type="submit" name="upload" value="upload"><br>
    </form>
    <img id="profile_img" style="border-radius: 50%; padding: 5px; width: 100px; height: 100px;" src="<?php 
    // If there is an error it will set a default avatar.
    if($error_of_img_size == true || $error_while_update_avatar == true || $error_of_no_file_uploaded == true){ 
        // new user need to change avatar, if uploadded photo is large 
        if ($user_avatar == NULL){
            $user_avatar = $DEFAULT_IMG;
            echo $user_avatar;
        }
        else{
            echo 'images/' . $user_avatar;
        }
    }
    else {
        // if new user need to change avatar.
        if(!is_null($user_avatar) && str_starts_with($user_avatar, "https://")){
            echo $user_avatar;
        }
        // if old user need to change avatar.
        else{
            echo 'images/' . $user_avatar;
        }
} ?>">
    <h3 id="profile_name"><?php echo "Your Full name is: " . $full_name; ?></h3>
    <h4 id="profile_id"><?php echo "Your Enactus id is: " .  $user_id; ?></h4>
    <h3 id="error" style="color:red;"><?php if($error_of_img_size == true){echo "File is to large";} if($error_while_update_avatar == true){echo "There is an error while update your image";} if($error_of_no_file_uploaded == true){echo "Please Upload an image";} ?></h3>
    <a href="logout.php">Logout</a>
</body>
</html>