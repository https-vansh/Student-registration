<?php
require_once "includes/config.php";
require_once "includes/functions.php";

$username = $password = $confirm_password = $email = $security_question = $security_answer = "";
$username_err = $password_err = $confirm_password_err = $email_err = $security_question_err = $security_answer_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = sanitize_input($_POST["username"]);
    $email = sanitize_input($_POST["email"]);
    $security_question = sanitize_input($_POST["security_question"]);
    $security_answer = sanitize_input($_POST["security_answer"]);
    
    // Validate username
    if(empty($username)){
        $username_err = "Please enter a username.";
    } elseif(!preg_match('/^[a-zA-Z0-9_]+$/', $username)){
        $username_err = "Username can only contain letters, numbers, and underscores.";
    } else{
        $sql = "SELECT id FROM student_login WHERE username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "This username is already taken.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate email
    if(empty($email)){
        $email_err = "Please enter an email.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $email_err = "Please enter a valid email address.";
    } else{
        $sql = "SELECT id FROM student_login WHERE email = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $email_err = "This email is already registered.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have atleast 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Validate security question and answer
    if(empty($security_question)){
        $security_question_err = "Please enter a security question.";
    }
    if(empty($security_answer)){
        $security_answer_err = "Please enter an answer to your security question.";
    }
    
    // Check input errors before inserting in database
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($email_err) && empty($security_question_err) && empty($security_answer_err)){
        
        $sql = "INSERT INTO student_login (username, password, email, security_question, security_answer) VALUES (?, ?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "sssss", $param_username, $param_password, $param_email, $param_security_question, $param_security_answer);
            
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_email = $email;
            $param_security_question = $security_question;
            $param_security_answer = password_hash($security_answer, PASSWORD_DEFAULT);
            
            if(mysqli_stmt_execute($stmt)){
                redirect_to("index.php");
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
    <title>Sign Up</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Sign Up</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Security Question</label>
                <input type="text" name="security_question" class="form-control <?php echo (!empty($security_question_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $security_question; ?>">
                <span class="invalid-feedback"><?php echo $security_question_err; ?></span>
            </div>
            <div class="form-group">
                <label>Security Answer</label>
                <input type="text" name="security_answer" class="form-control <?php echo (!empty($security_answer_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $security_answer; ?>">
                <span class="invalid-feedback"><?php echo $security_answer_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-secondary ml-2" value="Reset">
            </div>
            <p>Already have an account? <a href="index.php">Login here</a>.</p>
        </form>
    </div>    
</body>
</html>