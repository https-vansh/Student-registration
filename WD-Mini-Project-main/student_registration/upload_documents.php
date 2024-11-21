<?php
session_start();
require_once "includes/config.php";
require_once "includes/functions.php";

if(!is_logged_in()){
    redirect_to("index.php");
}

$upload_err = $success_msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $allowed_types = array('jpg', 'jpeg', 'png', 'pdf');
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if(!empty($_FILES["document"]["name"])){
        $file_name = $_FILES["document"]["name"];
        $file_size = $_FILES["document"]["size"];
        $file_tmp = $_FILES["document"]["tmp_name"];
        $file_type = $_FILES["document"]["type"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if(in_array($file_ext, $allowed_types)){
            if($file_size <= $max_size){
                $upload_dir = "uploads/";
                $new_file_name = uniqid() . "." . $file_ext;
                
                if(move_uploaded_file($file_tmp, $upload_dir . $new_file_name)){
                    $sql = "INSERT INTO document_track (student_id, document_type, file_name) VALUES (?, ?, ?)";
                    
                    if($stmt = mysqli_prepare($conn, $sql)){
                        mysqli_stmt_bind_param($stmt, "iss", $param_student_id, $param_document_type, $param_file_name);
                        
                        $param_student_id = $_SESSION["id"];
                        $param_document_type = $_POST["document_type"];
                        $param_file_name = $new_file_name;
                        
                        if(mysqli_stmt_execute($stmt)){
                            $success_msg = "Document uploaded successfully.";
                        } else{
                            $upload_err = "Oops! Something went wrong. Please try again later.";
                        }

                        mysqli_stmt_close($stmt);
                    }
                } else {
                    $upload_err = "Failed to upload file.";
                }
            } else {
                $upload_err = "File size is too large. Maximum size is 5MB.";
            }
        } else {
            $upload_err = "Invalid file type. Allowed types: jpg, jpeg, png, pdf.";
        }
    } else {
        $upload_err = "Please select a file to upload.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Documents</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Upload Documents</h2>
        <?php 
        if(!empty($upload_err)){
            echo '<div class="alert alert-danger">' . $upload_err . '</div>';
        }
        if(!empty($success_msg)){
            echo '<div class="alert alert-success">' . $success_msg . '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Document Type</label>
                <select name="document_type" class="form-control" required>
                    <option value="">Select Document Type</option>
                    <option value="ID Proof">ID Proof</option>
                    <option value="Address Proof">Address Proof</option>
                    <option value="Marksheet">Marksheet</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Select Document</label>
                <input type="file" name="document" class="form-control-file" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Upload">
            </div>
        </form>
        <p><a href="edit_profile.php">Edit Profile</a></p>
        <p><a href="logout.php">Logout</a></p>
    </div>
</body>
</html>