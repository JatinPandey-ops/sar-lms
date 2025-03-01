<?php
session_start();
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
{   
    header('location:index.php');
}
else{ 

if(isset($_POST['issue']))
{
    $studentid = strtoupper($_POST['studentid']);
    $bookid = $_POST['bookdetails'];
    
    try {
        // Check if student exists and is active
        $studentSql = "SELECT Status FROM tblstudents WHERE StudentId=:studentid";
        $studentQuery = $dbh->prepare($studentSql);
        $studentQuery->bindParam(':studentid', $studentid, PDO::PARAM_STR);
        $studentQuery->execute();
        $studentResult = $studentQuery->fetch(PDO::FETCH_OBJ);
        
        if($studentQuery->rowCount() == 0) {
            $_SESSION['error'] = "Student ID not found";
            header('location:issue-book.php');
            exit;
        }
        
        if($studentResult->Status != 1) {
            $_SESSION['error'] = "Student account is blocked";
            header('location:issue-book.php');
            exit;
        }
        
        // Check if book is available
        $checkSql = "SELECT AvailableCopies FROM tblbooks WHERE BookID=:bookid";
        $checkQuery = $dbh->prepare($checkSql);
        $checkQuery->bindParam(':bookid', $bookid, PDO::PARAM_STR);
        $checkQuery->execute();
        $result = $checkQuery->fetch(PDO::FETCH_OBJ);
        
        if($checkQuery->rowCount() == 0) {
            $_SESSION['error'] = "Book ID not found";
            header('location:issue-book.php');
            exit;
        }
        
        if($result->AvailableCopies < 1) {
            $_SESSION['error'] = "Book is not available for issue";
            header('location:issue-book.php');
            exit;
        }
        
        // Check if student already has this book
        $checkIssueSql = "SELECT * FROM tblissuedbookdetails WHERE BookId=:bookid AND StudentID=:studentid AND RetrunStatus=0";
        $checkIssueQuery = $dbh->prepare($checkIssueSql);
        $checkIssueQuery->bindParam(':bookid', $bookid, PDO::PARAM_STR);
        $checkIssueQuery->bindParam(':studentid', $studentid, PDO::PARAM_STR);
        $checkIssueQuery->execute();
        
        if($checkIssueQuery->rowCount() > 0) {
            $_SESSION['error'] = "This book is already issued to this student";
            header('location:issue-book.php');
            exit;
        }
        
        // Write to log file for debugging
        $logFile = fopen("issue_log.txt", "a") or die("Unable to open log file!");
        fwrite($logFile, "Attempting to issue book: $bookid to student: $studentid at " . date("Y-m-d H:i:s") . "\n");
        
        // Insert new issue record - DO EVERYTHING MANUALLY WITHOUT RELYING ON TRIGGERS
        $dbh->beginTransaction();
        
        // 1. Insert the issue record
        $sql = "INSERT INTO tblissuedbookdetails(StudentID, BookId) VALUES(:studentid, :bookid)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':studentid', $studentid, PDO::PARAM_STR);
        $query->bindParam(':bookid', $bookid, PDO::PARAM_STR);
        $insertResult = $query->execute();
        
        fwrite($logFile, "INSERT result: " . ($insertResult ? "Success" : "Failed") . "\n");
        fwrite($logFile, "Rows affected by INSERT: " . $query->rowCount() . "\n");
        
        if($insertResult && $query->rowCount() > 0)
        {
            // 2. Update the available copies
            $updateSql = "UPDATE tblbooks SET AvailableCopies = AvailableCopies - 1 WHERE BookID = :bookid AND AvailableCopies > 0";
            $updateQuery = $dbh->prepare($updateSql);
            $updateQuery->bindParam(':bookid', $bookid, PDO::PARAM_STR);
            $updateResult = $updateQuery->execute();
            
            fwrite($logFile, "UPDATE result: " . ($updateResult ? "Success" : "Failed") . "\n");
            fwrite($logFile, "Rows affected by UPDATE: " . $updateQuery->rowCount() . "\n");
            
            if($updateResult && $updateQuery->rowCount() > 0) {
                // Commit the transaction if both operations succeeded
                $dbh->commit();
                fwrite($logFile, "Transaction committed successfully\n");
                $_SESSION['msg'] = "Book issued successfully";
                fclose($logFile);
                header('location:manage-issued-books.php');
                exit;
            } else {
                // Rollback if update failed
                $dbh->rollBack();
                fwrite($logFile, "Rollback due to update failure\n");
                $_SESSION['error'] = "Failed to update book availability";
                fclose($logFile);
                header('location:issue-book.php');
                exit;
            }
        }
        else 
        {
            // Rollback if insert failed
            $dbh->rollBack();
            fwrite($logFile, "Rollback due to insert failure\n");
            $_SESSION['error'] = "Failed to issue book. Please check database structure.";
            fclose($logFile);
            header('location:issue-book.php');
            exit;
        }
    } catch (PDOException $e) {
        // Log and handle any exceptions
        if(isset($logFile)) {
            fwrite($logFile, "ERROR: " . $e->getMessage() . "\n");
            fclose($logFile);
        }
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header('location:issue-book.php');
        exit;
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
    <title>Online Library Management System | Issue a new Book</title>
    <!-- BOOTSTRAP CORE STYLE  -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE  -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- CUSTOM STYLE  -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
<script>
// function for get student name
function getstudent() {
    $("#loaderIcon").show();
    jQuery.ajax({
        url: "get_student.php",
        data:'studentid='+$("#studentid").val(),
        type: "POST",
        success:function(data){
            $("#get_student_name").html(data);
            $("#loaderIcon").hide();
        },
        error:function (){}
    });
}

//function for book details
function getbook() {
    $("#loaderIcon").show();
    jQuery.ajax({
        url: "get_book.php",
        data:'bookid='+$("#bookid").val(),
        type: "POST",
        success:function(data){
            $("#get_book_name").html(data);
            $("#loaderIcon").hide();
        },
        error:function (){}
    });
}

// Alternative function to search by title
function getbookByTitle() {
    $("#loaderIcon").show();
    jQuery.ajax({
        url: "get_book.php",
        data:'booktitle='+$("#booktitle").val(),
        type: "POST",
        success:function(data){
            $("#get_book_name").html(data);
            $("#loaderIcon").hide();
        },
        error:function (){}
    });
}
</script> 
<style type="text/css">
  .others{
    color:red;
}
.loader {
    border: 6px solid #f3f3f3;
    border-radius: 50%;
    border-top: 6px solid #3498db;
    width: 30px;
    height: 30px;
    -webkit-animation: spin 2s linear infinite;
    animation: spin 2s linear infinite;
    display: none;
}

@-webkit-keyframes spin {
    0% { -webkit-transform: rotate(0deg); }
    100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
</head>
<body>
      <!------MENU SECTION START-->
<?php include('includes/header.php');?>
<!-- MENU SECTION END-->
    <div class="content-wrapper">
        <div class="container">
            <div class="row pad-botm">
                <div class="col-md-12">
                    <h4 class="header-line">Issue a New Book</h4>
                </div>
            </div>

            <div class="row">
                <?php if(isset($_SESSION['error']) && $_SESSION['error']!=""){ ?>
                <div class="col-md-6">
                    <div class="alert alert-danger">
                        <strong>Error:</strong> <?php echo htmlentities($_SESSION['error']); ?>
                        <?php $_SESSION['error']=""; ?>
                    </div>
                </div>
                <?php } ?>
                
                <?php if(isset($_SESSION['msg']) && $_SESSION['msg']!=""){ ?>
                <div class="col-md-6">
                    <div class="alert alert-success">
                        <strong>Success:</strong> <?php echo htmlentities($_SESSION['msg']); ?>
                        <?php $_SESSION['msg']=""; ?>
                    </div>
                </div>
                <?php } ?>
            </div>

            <div class="row">
                <div class="col-md-10 col-sm-6 col-xs-12 col-md-offset-1">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            Issue a New Book
                        </div>
                        <div class="panel-body">
                            <form role="form" method="post">
                                <div class="form-group">
                                    <label>Student ID<span style="color:red;">*</span></label>
                                    <input class="form-control" type="text" name="studentid" id="studentid" onBlur="getstudent()" autocomplete="off" required />
                                </div>

                                <div class="form-group">
                                    <span id="get_student_name" style="font-size:16px;"></span> 
                                    <div id="loaderIcon" class="loader"></div>
                                </div>

                                <div class="form-group">
                                    <label>Search By Book ID</label>
                                    <input class="form-control" type="text" name="bookid" id="bookid" onBlur="getbook()" />
                                </div>

                                <div class="form-group">
                                    <label>OR Search By Book Title</label>
                                    <input class="form-control" type="text" name="booktitle" id="booktitle" onBlur="getbookByTitle()" />
                                    <p class="help-block">You can search by either Book ID or Title</p>
                                </div>

                                <div class="form-group">
                                    <label>Selected Book<span style="color:red;">*</span></label>
                                    <select class="form-control" name="bookdetails" id="get_book_name" required>
                                        <option value="">Select Book</option>
                                    </select>
                                </div>

                                <button type="submit" name="issue" id="submit" class="btn btn-info">Issue Book</button>
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

</body>
</html>
<?php } ?>