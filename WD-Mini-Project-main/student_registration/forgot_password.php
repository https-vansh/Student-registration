<?php
require_once "includes/config.php";
require_once "includes/functions.php";

$username = $security_answer = $new_password = $confirm_password = "";
$username_err = $security_answer_err = $new_password_err = $confirm_password_err = "";
$security_question = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = sanitize_input($_POST["username"]);
    
    if(empty($username)){
        $username_err = "Please enter your username.";
    } else {
        $sql = "SELECT id, security_question, security_answer FROM student_login WHERE username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $id, $security_question, $hashed_security_answer);
                    mysqli_stmt_fetch($stmt);
                    
                    if(isset($_POST["security_answer"])){
                        $security_answer = $_POST["security_answer"];
                        if(password_verify($security_answer, $hashed_security_answer)){
                            // Security answer is correct, allow password reset
                            $new_password = trim($_POST["new_password"]);
                            $confirm_password = trim($_POST["confirm_password"]);
                            
                            if(empty($new_password)){
                                $new_password_err = "Please enter the new password.";     
                            } elseif(strlen($new_password) < 6){
                                $new_password_err = "Password must have atleast 6 characters.";
                            }
                            
                            if(empty($confirm_password)){
                                $confirm_password_err = "Please confirm the password.";     
                            } else{
                                if(empty($new_password_err) && ($new_password != $confirm_password)){
                                    $confirm_password_err = "Password did not match.";
                                }
                            }
                            
                            if(empty($new_password_err) && empty($confirm_password_err)){
                                $sql = "UPDATE student_login SET password = ? WHERE id = ?";
                                
                                if($stmt = mysqli_prepare($conn, $sql)){
                                    mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);
                                    
                                    $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                                    $param_id = $id;
                                    
                                    if(mysqli_stmt_execute($stmt)){
                                        // Password updated successfully. Redirect to login page
                                        redirect_to("index.php");
                                    } else{
                                        echo "Oops! Something went wrong. Please try again later.";
                                    }
                                }
                            }
                        } else {
                            $security_answer_err = "Incorrect security answer.";
                        }
                    }
                } else {
                    $username_err = "No account found with that username.";
                }
            } else {
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
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            <?php if(!empty($security_question)): ?>
                <div class="form-group">
                    <label>Security Question: <?php echo htmlspecialchars($security_question); ?></label>
                    <input type="text" name="security_answer" class="form-control <?php echo (!empty($security_answer_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $security_answer; ?>">
                    <span class="invalid-feedback"><?php echo $security_answer_err; ?></span>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $new_password; ?>">
                    <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Reset Password">
            </div>
            <p>Remember your password? <a href="index.php">Login here</a>.</p>
        </form>
    </div>
</body>
</html>