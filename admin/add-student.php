<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
{   
    header('location:index.php');
}
else{

// Generate a random student ID
function generateStudentId() {
    $letters = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 2));
    $numbers = substr(str_shuffle('0123456789'), 0, 7);
    return $letters . $numbers;
}

if(isset($_POST['add'])) {
    $studentid = strtoupper($_POST['studentid']);
    $fname = $_POST['fullname'];
    $mobileno = $_POST['mobileno'];
    $email = $_POST['email'];
    $course = $_POST['course'];
    $password = md5($_POST['password']);
    $status = 1;
    
    // Check if student ID is already registered
    $sql = "SELECT StudentId FROM tblstudents WHERE StudentId=:studentid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':studentid', $studentid, PDO::PARAM_STR);
    $query->execute();
    
    if($query->rowCount() > 0) {
        $_SESSION['error'] = "Student ID already exists. Please try another ID.";
    } else {
        // Check if email is already registered
        $sql = "SELECT EmailId FROM tblstudents WHERE EmailId=:email";
        $query = $dbh->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        
        if($query->rowCount() > 0) {
            $_SESSION['error'] = "Email already registered. Please use a different email.";
        } else {
            $sql = "INSERT INTO tblstudents(StudentId,FullName,EmailId,MobileNumber,Password,Status,Course) 
                   VALUES(:studentid,:fname,:email,:mobileno,:password,:status,:course)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':studentid', $studentid, PDO::PARAM_STR);
            $query->bindParam(':fname', $fname, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
            $query->bindParam(':password', $password, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':course', $course, PDO::PARAM_STR);
            $query->execute();
            
            $lastInsertId = $dbh->lastInsertId();
            if($lastInsertId) {
                $_SESSION['msg'] = "Student registered successfully";
                header('location:reg-students.php');
                exit();
            } else {
                $_SESSION['error'] = "Something went wrong. Please try again";
            }
        }
    }
}

// Generate a random student ID for the form
$suggestedId = generateStudentId();
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | Add Student</title>
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
                    <h4 class="header-line">Register New Student</h4>
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
                            <form role="form" method="post">
                                <div class="form-group">
                                    <label>Student ID</label>
                                    <input class="form-control" type="text" name="studentid" value="<?php echo htmlentities($suggestedId); ?>" autocomplete="off" required />
                                    <p class="help-block">Student ID must be unique. You can use the suggested ID or create your own.</p>
                                </div>

                                <div class="form-group">
                                    <label>Student Full Name</label>
                                    <input class="form-control" type="text" name="fullname" autocomplete="off" required />
                                </div>
                                
                                <div class="form-group">
                                    <label>Mobile Number</label>
                                    <input class="form-control" type="text" name="mobileno" maxlength="11" autocomplete="off" required />
                                </div>
                                
                                <div class="form-group">
                                    <label>Email</label>
                                    <input class="form-control" type="email" name="email" id="emailid" autocomplete="off" required />
                                </div>
                                
                                <div class="form-group">
                                    <label>Course/Program</label>
                                    <select class="form-control" name="course" required>
                                        <option value="">Select Course</option>
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
                                    <label>Password</label>
                                    <input class="form-control" type="password" name="password" id="password" autocomplete="off" required />
                                </div>

                                <div class="form-group">
                                    <label>Confirm Password</label>
                                    <input class="form-control" type="password" name="confirmpassword" id="confirmpassword" autocomplete="off" required />
                                </div>

                                <button type="submit" name="add" id="submit" class="btn btn-info">Register Student</button>
                            </form>
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