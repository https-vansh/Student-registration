<?php
session_start();
require_once "includes/config.php";
require_once "includes/functions.php";

if(is_logged_in()){
    // Check registration status
    $sql = "SELECT registration_status FROM student_registration WHERE student_id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) == 1){
                mysqli_stmt_bind_result($stmt, $registration_status);
                mysqli_stmt_fetch($stmt);
                if($registration_status == 'completed'){
                    redirect_to("upload_documents.php");
                } else {
                    redirect_to("registration.php");
                }
            } else {
                redirect_to("registration.php");
            }
        }
        mysqli_stmt_close($stmt);
    }
    exit;
}

$username = $password = "";
$username_err = $password_err = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = sanitize_input($_POST["username"]);
    $password = $_POST["password"];
    
    if(empty($username_err) && empty($password_err)){
        $sql = "SELECT id, username, password FROM student_login WHERE username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            session_start();
                            
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;                            
                            
                            // Check registration status
                            $reg_sql = "SELECT registration_status FROM student_registration WHERE student_id = ?";
                            if($reg_stmt = mysqli_prepare($conn, $reg_sql)){
                                mysqli_stmt_bind_param($reg_stmt, "i", $id);
                                if(mysqli_stmt_execute($reg_stmt)){
                                    mysqli_stmt_store_result($reg_stmt);
                                    if(mysqli_stmt_num_rows($reg_stmt) == 1){
                                        mysqli_stmt_bind_result($reg_stmt, $registration_status);
                                        mysqli_stmt_fetch($reg_stmt);
                                        if($registration_status == 'completed'){
                                            redirect_to("upload_documents.php");
                                        } else {
                                            redirect_to("registration.php");
                                        }
                                    } else {
                                        redirect_to("registration.php");
                                    }
                                }
                                mysqli_stmt_close($reg_stmt);
                            }
                        } else{
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    $login_err = "Invalid username or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Student Login</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p>Don't have an account? <a href="signup.php">Sign up now</a>.</p>
            <p>Forgot your password? <a href="forgot_password.php">Reset it here</a>.</p>
        </form>
    </div>
</body>
</html>