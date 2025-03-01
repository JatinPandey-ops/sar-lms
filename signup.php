<?php 
session_start();
include('includes/config.php');
error_reporting(0);
if(isset($_POST['signup']))
{   
// Get the student ID from the form instead of generating it
$StudentId = $_POST['studentid'];
$fname = $_POST['fullname'];
$mobileno = $_POST['mobileno'];
$email = $_POST['email']; 
$password = md5($_POST['password']); 
$course = $_POST['course']; // Added course field
$status = 1;

// Check if the student ID already exists
$check_sql = "SELECT StudentId FROM tblstudents WHERE StudentId=:StudentId";
$check_query = $dbh->prepare($check_sql);
$check_query->bindParam(':StudentId', $StudentId, PDO::PARAM_STR);
$check_query->execute();
$count = $check_query->rowCount();

if($count > 0) {
    echo "<script>alert('Student ID already exists. Please use a different ID.');</script>";
} else {
    // Insert new student with course field
    $sql = "INSERT INTO tblstudents(StudentId, FullName, MobileNumber, EmailId, Password, Course, Status) 
            VALUES(:StudentId, :fname, :mobileno, :email, :password, :course, :status)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':StudentId', $StudentId, PDO::PARAM_STR);
    $query->bindParam(':fname', $fname, PDO::PARAM_STR);
    $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->bindParam(':course', $course, PDO::PARAM_STR);
    $query->bindParam(':status', $status, PDO::PARAM_STR);
    $query->execute();
    $lastInsertId = $dbh->lastInsertId();
    
    if($lastInsertId) {
        echo '<script>alert("Your Registration is successful. Your student ID is: '.$StudentId.'");</script>';
    } else {
        echo "<script>alert('Something went wrong. Please try again');</script>";
    }
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
    <title>Online Library Management System | Student Signup</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
<script type="text/javascript">
function valid() {
    if(document.signup.password.value != document.signup.confirmpassword.value) {
        alert("Password and Confirm Password Field do not match!");
        document.signup.confirmpassword.focus();
        return false;
    }
    return true;
}
</script>
<script>
function checkAvailability() {
    $("#loaderIcon").show();
    jQuery.ajax({
        url: "check_availability.php",
        data:'emailid='+$("#emailid").val(),
        type: "POST",
        success:function(data){
            $("#user-availability-status").html(data);
            $("#loaderIcon").hide();
        },
        error:function (){}
    });
}

function checkStudentIdAvailability() {
    $("#studentIdLoaderIcon").show();
    jQuery.ajax({
        url: "check_studentid_availability.php",
        data:'studentid='+$("#studentid").val(),
        type: "POST",
        success:function(data){
            $("#studentid-availability-status").html(data);
            $("#studentIdLoaderIcon").hide();
        },
        error:function (){}
    });
}
</script>    
</head>
<body>
<?php include('includes/header.php');?>
    <div class="content-wrapper">
         <div class="container">
        <div class="row pad-botm">
            <div class="col-md-12">
                <h4 class="header-line">User Signup</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-9 col-md-offset-1">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        SIGNUP FORM
                    </div>
                    <div class="panel-body">
                        <form name="signup" method="post" onSubmit="return valid();">
                            <div class="form-group">
                                <label>Enter Student ID (Alphanumeric)</label>
                                <input class="form-control" type="text" name="studentid" id="studentid" onBlur="checkStudentIdAvailability()" autocomplete="off" required />
                                <span id="studentid-availability-status" style="font-size:12px;"></span>
                                <p class="help-block">Example: CS2023001, ENG2023045, etc.</p>
                            </div>
                            
                            <div class="form-group">
                                <label>Enter Full Name</label>
                                <input class="form-control" type="text" name="fullname" autocomplete="off" required />
                            </div>
                            
                            <div class="form-group">
                                <label>Mobile Number:</label>
                                <input class="form-control" type="text" name="mobileno" maxlength="10" autocomplete="off" required />
                            </div>
                            
                            <div class="form-group">
                                <label>Enter Email</label>
                                <input class="form-control" type="email" name="email" id="emailid" onBlur="checkAvailability()" autocomplete="off" required />
                                <span id="user-availability-status" style="font-size:12px;"></span> 
                            </div>
                            
                            <div class="form-group">
                                <label>Select Course</label>
                                <select class="form-control" name="course" required>
                                    <option value="">Select Course</option>
                                    <option value="Computer Science">Computer Science</option>
                                    <option value="Information Technology">Information Technology</option>
                                    <option value="Engineering">Engineering</option>
                                    <option value="Arts">Arts</option>
                                    <option value="Science">Science</option>
                                    <option value="Commerce">Commerce</option>
                                    <option value="Management">Management</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Enter Password</label>
                                <input class="form-control" type="password" name="password" autocomplete="off" required />
                            </div>
                            
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input class="form-control" type="password" name="confirmpassword" autocomplete="off" required />
                            </div>
                            
                            <button type="submit" name="signup" class="btn btn-danger" id="submit">Register Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <?php include('includes/footer.php');?>
    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/custom.js"></script>
</body>
</html>