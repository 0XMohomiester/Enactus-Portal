<?php
session_start();
require_once 'config.php';
// ini_set('display_errors', 0);
$wrongUorP = false;

// Prevent Resubmission.
function gen_token(){
    if(empty($_SESSION['token_login'])){
        $_SESSION['token_login'] = bin2hex(random_bytes(32));
    }
}
gen_token();

if(isset($_POST['submit']) && isset($_POST['username']) && isset($_POST['password']) && !empty($_POST['username']) && !empty($_POST['password'])){
        if(is_string($_POST['username']) && is_string($_POST['password']) && is_string($_POST['submit']) && isset($_POST['token']) && is_string($_POST['token']) && hash_equals($_SESSION['token_login'], $_POST['token'])){
                $username =  $_POST['username'];
                $password =  $_POST['password'];
                $token_value = bin2hex(random_bytes(20));
                $usersql= "SELECT users.id, users.username, users.password, users.role, personal_info.Did FROM users JOIN personal_info ON users.id = personal_info.id where username = ? AND password = ?"; // select all from users
                try{
                    $stmt = $conn->prepare($usersql);
                    $stmt->bind_param("ss" , $username, $password);
                    $stmt->execute();
                    $stmt->store_result();
                }
                catch(Exception $e){
                    header("Location: login.php");
                }
                if($stmt->num_rows === 1){
                    $stmt->bind_result($id, $username, $password, $role, $Did);
                    $stmt->fetch();
                    $_SESSION['session'] = $token_value;
                    $_SESSION['id'] = $id;
                    setcookie("session", $token_value , time() + (86400 * 30), "/");
                    unset($_SESSION['token_login']);
                    header('Location: dashboard.php');
                }else{
                    $wrongUorP = true;
                    unset($_SESSION['token_login']);
                    gen_token();
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
    <title>login</title>
    <link rel="stylesheet" href="CSS/login_css/login.css">
<link rel="stylesheet" href="CSS/bootstrap/css/bootstrap.css">
</head>
<body>  
        <div class="header">
            <img class="w-50" src="CSS/imgs/Enactus_Logo_W.png" alt="Enactus_Logo">
        </div>
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col">
                    <img class="imgH" src="CSS/imgs/enactus.png">
                    <p class="textH">Welcome back</p>
                </div>
            </div>  
            <div class="wrapper d-flex justify-content-center m-auto">
                <form action="" method="post">
                    <h1>Login</h1>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token_login']; ?>">
                    <div class="input-box">
                        <input type="text" placeholder="Username" name="username" required>
                        <i class='bx bxs-user'></i>
                    </div>
                    <div class="input-box">
                        <input type="password" id="password" placeholder="Password"  name="password" required>
                        <i class='bx bxs-lock-alt'></i>
                        <span class="toggle-password" onclick="togglePasswordVisibility()"></span>
                    </div>
                    <p id="incorrect"><?php if($wrongUorP){echo "Incorrect username or password.";} ?></p>
                    <div class="btn-wrapper">
                        <button type="submit" name="submit" class="btn">Login</button>
                    </div>
                </form>
            </div>
        </div>
</body>
</html>