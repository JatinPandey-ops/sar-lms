<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
{   
    header('location:index.php');
}
else{ 

// code for block student    
if(isset($_GET['inid']))
{
    $id=$_GET['inid'];
    $status=0;
    $sql = "UPDATE tblstudents SET Status=:status WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id',$id, PDO::PARAM_STR);
    $query->bindParam(':status',$status, PDO::PARAM_STR);
    $query->execute();
    
    // Check if student has any issued books
    $checkSql = "SELECT COUNT(*) as bookcount FROM tblissuedbookdetails 
                WHERE StudentID = (SELECT StudentId FROM tblstudents WHERE id=:id) 
                AND RetrunStatus=0";
    $checkQuery = $dbh->prepare($checkSql);
    $checkQuery->bindParam(':id',$id, PDO::PARAM_STR);
    $checkQuery->execute();
    $booksIssued = $checkQuery->fetch(PDO::FETCH_OBJ);
    
    if($booksIssued->bookcount > 0) {
        $_SESSION['msg'] = "Student has been blocked. Note: Student still has " . $booksIssued->bookcount . " book(s) issued.";
    } else {
        $_SESSION['msg'] = "Student has been blocked successfully";
    }
    
    header('location:reg-students.php');
}

//code for active students
if(isset($_GET['id']))
{
    $id=$_GET['id'];
    $status=1;
    $sql = "UPDATE tblstudents SET Status=:status WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id',$id, PDO::PARAM_STR);
    $query->bindParam(':status',$status, PDO::PARAM_STR);
    $query->execute();
    $_SESSION['msg'] = "Student has been activated successfully";
    header('location:reg-students.php');
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | Manage Reg Students</title>
    <!-- BOOTSTRAP CORE STYLE  -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE  -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- DATATABLE STYLE  -->
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
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
                    <h4 class="header-line">Manage Registered Students</h4>
                </div>
                <div class="col-md-6">
                    <a href="add-student.php" class="btn btn-success">Register New Student</a>
                </div>
            </div>
            
            <div class="row">
                <?php if(isset($_SESSION['msg']) && $_SESSION['msg']!="") { ?>
                <div class="col-md-6">
                    <div class="alert alert-success">
                        <strong>Success:</strong> <?php echo htmlentities($_SESSION['msg']); ?>
                        <?php $_SESSION['msg']=""; ?>
                    </div>
                </div>
                <?php } ?>
                
                <?php if(isset($_SESSION['error']) && $_SESSION['error']!="") { ?>
                <div class="col-md-6">
                    <div class="alert alert-danger">
                        <strong>Error:</strong> <?php echo htmlentities($_SESSION['error']); ?>
                        <?php $_SESSION['error']=""; ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <!-- Advanced Tables -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                          Registered Students
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="studentsTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <th>Email</th>
                                            <th>Mobile Number</th>
                                            <th>Course</th>
                                            <th>Reg Date</th>
                                            <th>Books Issued</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                                    $sql = "SELECT s.*, 
                                            (SELECT COUNT(*) FROM tblissuedbookdetails 
                                             WHERE StudentID = s.StudentId AND RetrunStatus = 0) as BooksIssued,
                                            (SELECT COUNT(*) FROM tblissuedbookdetails 
                                             WHERE StudentID = s.StudentId) as TotalHistory
                                            FROM tblstudents s 
                                            ORDER BY s.RegDate DESC";
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                    $cnt = 1;
                                    if($query->rowCount() > 0)
                                    {
                                        foreach($results as $result)
                                        {
                                            $rowClass = $result->Status == 0 ? 'warning' : '';
                                            $rowClass = $result->BooksIssued > 0 && $result->Status == 0 ? 'danger' : $rowClass;
                                    ?>                                      
                                            <tr class="<?php echo $rowClass; ?>">
                                                <td class="center"><?php echo htmlentities($cnt);?></td>
                                                <td class="center"><?php echo htmlentities($result->StudentId);?></td>
                                                <td class="center"><?php echo htmlentities($result->FullName);?></td>
                                                <td class="center"><?php echo htmlentities($result->EmailId);?></td>
                                                <td class="center"><?php echo htmlentities($result->MobileNumber);?></td>
                                                <td class="center"><?php echo htmlentities($result->Course);?></td>
                                                <td class="center"><?php echo htmlentities(date('d-m-Y', strtotime($result->RegDate)));?></td>
                                                <td class="center">
                                                    <?php if($result->BooksIssued > 0): ?>
                                                        <span class="label label-<?php echo $result->Status == 0 ? 'danger' : 'warning'; ?>">
                                                            <?php echo htmlentities($result->BooksIssued); ?> Book(s)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="label label-success">None</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="center">
                                                    <?php if($result->Status==1): ?>
                                                        <span class="label label-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="label label-danger">Blocked</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="center">
                                                    <div class="btn-group">
                                                        <?php if($result->Status==1): ?>
                                                            <a href="reg-students.php?inid=<?php echo htmlentities($result->id);?>" onclick="return confirm('Are you sure you want to block this student?');" class="btn btn-danger btn-sm">
                                                                <i class="fa fa-ban"></i> Block
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="reg-students.php?id=<?php echo htmlentities($result->id);?>" onclick="return confirm('Are you sure you want to activate this student?');" class="btn btn-success btn-sm">
                                                                <i class="fa fa-check"></i> Activate
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="edit-student.php?stdid=<?php echo htmlentities($result->id);?>" class="btn btn-primary btn-sm">
                                                            <i class="fa fa-edit"></i> Edit
                                                        </a>
                                                        <a href="student-history.php?stdid=<?php echo htmlentities($result->id);?>" class="btn btn-info btn-sm">
                                                            <i class="fa fa-history"></i> History
                                                            <?php if($result->TotalHistory > 0): ?>
                                                                <span class="badge"><?php echo $result->TotalHistory; ?></span>
                                                            <?php endif; ?>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                    <?php 
                                            $cnt=$cnt+1;
                                        }
                                    } 
                                    ?>                                      
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--End Advanced Tables -->
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
    <!-- DATATABLE SCRIPTS  -->
    <script src="assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="assets/js/dataTables/dataTables.bootstrap.js"></script>
      <!-- CUSTOM SCRIPTS  -->
    <script src="assets/js/custom.js"></script>
    <script>
    $(document).ready(function() {
        $('#studentsTable').DataTable({
            responsive: true,
            "order": [[ 6, "desc" ]] // Sort by registration date by default (newest first)
        });
    });
    </script>
</body>
</html>
<?php } ?>