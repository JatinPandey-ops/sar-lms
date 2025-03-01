<?php
session_start();
error_reporting(E_ALL); // Enable error reporting for debugging
ini_set('display_errors', 1);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
{   
    header('location:index.php');
}
else{
    // Get student ID from URL
    $stdid = intval($_GET['stdid']);

    if(isset($_POST['update'])) {
        $fname = $_POST['fullname'];
        $mobileno = $_POST['mobileno'];
        $email = $_POST['email'];
        $course = $_POST['course'];
        $status = $_POST['status'];
        
        try {
            // If password is being updated
            if(!empty($_POST['password'])) {
                $password = md5($_POST['password']);
                $sql = "UPDATE tblstudents SET FullName=:fname, EmailId=:email, MobileNumber=:mobileno, 
                        Password=:password, Status=:status, Course=:course WHERE id=:stdid";
                $query = $dbh->prepare($sql);
                $query->bindParam(':password', $password, PDO::PARAM_STR);
            } else {
                // If password is not being updated
                $sql = "UPDATE tblstudents SET FullName=:fname, EmailId=:email, MobileNumber=:mobileno, 
                        Status=:status, Course=:course WHERE id=:stdid";
                $query = $dbh->prepare($sql);
            }
            
            $query->bindParam(':fname', $fname, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':course', $course, PDO::PARAM_STR);
            $query->bindParam(':stdid', $stdid, PDO::PARAM_INT);
            $query->execute();
            
            // Check if update was successful
            $_SESSION['msg'] = "Student information updated successfully";
            header('location:reg-students.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | Edit Student</title>
    <!-- BOOTSTRAP CORE STYLE  -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE  -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- CUSTOM STYLE  -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
</head>
<body>
    <!------MENU SECTION START-->
    <?php include('includes/header.php');?>
    <!-- MENU SECTION END-->
    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line">Edit Student</h4>
                </div>
            </div>
            
            <div class="row">
                <?php if(isset($_SESSION['error']) && $_SESSION['error']!="") { ?>
                <div class="col-md-6">
                    <div class="alert alert-danger">
                        <strong>Error:</strong> <?php echo htmlentities($_SESSION['error']); ?>
                        <?php $_SESSION['error']=""; ?>
                    </div>
                </div>
                <?php } ?>
                
                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            Student Information
                        </div>
                        <div class="panel-body">
                            <?php 
                            $sql = "SELECT * FROM tblstudents WHERE id=:stdid";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':stdid', $stdid, PDO::PARAM_INT);
                            $query->execute();
                            $result = $query->fetch(PDO::FETCH_OBJ);
                            
                            // Check if student exists
                            if($query->rowCount() > 0) {
                            ?>
                            <form role="form" method="post">
                                <div class="form-group">
                                    <label>Student ID (Cannot be changed)</label>
                                    <input class="form-control" type="text" value="<?php echo htmlentities($result->StudentId); ?>" readonly />
                                </div>

                                <div class="form-group">
                                    <label>Student Full Name</label>
                                    <input class="form-control" type="text" name="fullname" value="<?php echo htmlentities($result->FullName); ?>" required />
                                </div>
                                
                                <div class="form-group">
                                    <label>Mobile Number</label>
                                    <input class="form-control" type="text" name="mobileno" value="<?php echo htmlentities($result->MobileNumber); ?>" maxlength="11" required />
                                </div>
                                
                                <div class="form-group">
                                    <label>Email</label>
                                    <input class="form-control" type="email" name="email" value="<?php echo htmlentities($result->EmailId); ?>" required />
                                </div>
                                
                                <div class="form-group">
                                    <label>Course/Program</label>
                                    <select class="form-control" name="course" required>
                                        <option value="<?php echo htmlentities($result->Course); ?>"><?php echo htmlentities($result->Course); ?></option>
                                        <option value="Computer Science">Computer Science</option>
                                        <option value="Information Technology">Information Technology</option>
                                        <option value="Business Administration">Business Administration</option>
                                        <option value="Engineering">Engineering</option>
                                        <option value="Arts and Humanities">Arts and Humanities</option>
                                        <option value="Science">Science</option>
                                        <option value="Medicine">Medicine</option>
                                        <option value="Law">Law</option>
                                        <option value="Education">Education</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Status</label>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="status" value="1" <?php if($result->Status == 1) echo "checked"; ?> />
                                            Active
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="status" value="0" <?php if($result->Status == 0) echo "checked"; ?> />
                                            Blocked
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>New Password (Leave blank to keep current password)</label>
                                    <input class="form-control" type="password" name="password" id="password" />
                                    <p class="help-block">Only fill this if you want to change the password.</p>
                                </div>

                                <div class="form-group password-confirm" style="display:none;">
                                    <label>Confirm New Password</label>
                                    <input class="form-control" type="password" name="confirmpassword" id="confirmpassword" />
                                </div>

                                <button type="submit" name="update" id="submit" class="btn btn-info">Update Student</button>
                            </form>
                            <?php } else { ?>
                                <div class="alert alert-danger">Student not found.</div>
                                <a href="reg-students.php" class="btn btn-primary">Back to Student List</a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CONTENT-WRAPPER SECTION END-->
    <?php include('includes/footer.php');?>
    <!-- FOOTER SECTION END-->
    <!-- JAVASCRIPT FILES PLACED AT THE BOTTOM TO REDUCE THE LOADING TIME  -->
    <!-- CORE JQUERY  -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <!-- BOOTSTRAP SCRIPTS  -->
    <script src="assets/js/bootstrap.js"></script>
    <!-- CUSTOM SCRIPTS  -->
    <script src="assets/js/custom.js"></script>
    <script>
    $(document).ready(function() {
        // Show confirm password field only when password field has input
        $("#password").on('input', function() {
            if($(this).val() !== '') {
                $(".password-confirm").show();
                $("#confirmpassword").prop('required', true);
            } else {
                $(".password-confirm").hide();
                $("#confirmpassword").prop('required', false);
            }
        });
        
        // Validate matching passwords
        $("#confirmpassword").on('keyup', function() {
            var password = $("#password").val();
            var confirmPassword = $(this).val();
            
            if (password != confirmPassword) {
                $("#submit").prop('disabled', true);
                $(this).css('border-color', 'red');
            } else {
                $("#submit").prop('disabled', false);
                $(this).css('border-color', 'green');
            }
        });
    });
    </script>
</body>
</html>
<?php } ?>