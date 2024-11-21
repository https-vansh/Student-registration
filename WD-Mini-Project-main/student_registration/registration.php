<?php
session_start();
require_once "includes/config.php";
require_once "includes/functions.php";

if(!is_logged_in()){
    redirect_to("index.php");
}

// Check if student has already registered
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
            }
        }
    }
    mysqli_stmt_close($stmt);
}

$full_name = $dob = $gender = $address = $phone = $course = "";
$full_name_err = $dob_err = $gender_err = $address_err = $phone_err = $course_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $full_name = sanitize_input($_POST["full_name"]);
    $dob = sanitize_input($_POST["dob"]);
    $gender = sanitize_input($_POST["gender"]);
    $address = sanitize_input($_POST["address"]);
    $phone = sanitize_input($_POST["phone"]);
    $course = sanitize_input($_POST["course"]);
    
    // Validate and process the form data
    // (Add your validation logic here)
    
    if(empty($full_name_err) && empty($dob_err) && empty($gender_err) && empty($address_err) && empty($phone_err) && empty($course_err)){
        $sql = "INSERT INTO student_registration (student_id, full_name, dob, gender, address, phone, course, registration_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'completed')";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "issssss", $param_student_id, $param_full_name, $param_dob, $param_gender, $param_address, $param_phone, $param_course);
            
            $param_student_id = $_SESSION["id"];
            $param_full_name = $full_name;
            $param_dob = $dob;
            $param_gender = $gender;
            $param_address = $address;
            $param_phone = $phone;
            $param_course = $course;
            
            if(mysqli_stmt_execute($stmt)){
                redirect_to("upload_documents.php");
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
    <title>Student Registration</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Student Registration</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control <?php echo (!empty($full_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $full_name; ?>">
                <span class="invalid-feedback"><?php echo $full_name_err; ?></span>
            </div>
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="dob" class="form-control <?php echo (!empty($dob_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $dob; ?>">
                <span class="invalid-feedback"><?php echo $dob_err; ?></span>
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="gender" class="form-control <?php echo (!empty($gender_err)) ? 'is-invalid' : ''; ?>">
                    <option value="">Select Gender</option>
                    <option value="Male" <?php echo ($gender == "Male") ? "selected" : ""; ?>>Male</option>
                    <option value="Female" <?php echo ($gender == "Female") ? "selected" : ""; ?>>Female</option>
                    <option value="Other" <?php echo ($gender == "Other") ? "selected" : ""; ?>>Other</option>
                </select>
                <span class="invalid-feedback"><?php echo $gender_err; ?></span>
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>"><?php echo $address; ?></textarea>
                <span class="invalid-feedback"><?php echo $address_err; ?></span>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone; ?>">
                <span class="invalid-feedback"><?php echo $phone_err; ?></span>
            </div>
            <div class="form-group">
                <label>Course</label>
                <input type="text" name="course" class="form-control <?php echo (!empty($course_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $course; ?>">
                <span class="invalid-feedback"><?php echo $course_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-secondary ml-2" value="Reset">
            </div>
        </form>
    </div>
</body>
</html>