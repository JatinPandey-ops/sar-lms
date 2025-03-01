<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['login'])==0)
{   
    header('location:index.php');
}
else{ 
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | Manage Issued Books</title>
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
    <style>
        .overdue {
            background-color: #FFEEEE !important;
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
                    <h4 class="header-line">My Issued Books</h4>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <!-- Advanced Tables -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                          Issued Books 
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="issuedBooksTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Book ID</th>
                                            <th>Book Name</th>
                                            <th>Issued Date</th>
                                            <th>Return Date</th>
                                            <th>Status</th>
                                            <th>Fine in(USD)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php 
$sid = $_SESSION['stdid'];
$sql = "SELECT tblbooks.BookID, tblbooks.Title, tblissuedbookdetails.IssuesDate, 
        tblissuedbookdetails.ReturnDate, tblissuedbookdetails.RetrunStatus, tblissuedbookdetails.fine 
        FROM tblissuedbookdetails 
        JOIN tblstudents ON tblstudents.StudentId = tblissuedbookdetails.StudentId 
        JOIN tblbooks ON tblbooks.BookID = tblissuedbookdetails.BookId 
        WHERE tblstudents.StudentId = :sid 
        ORDER BY tblissuedbookdetails.IssuesDate DESC";

$query = $dbh->prepare($sql);
$query->bindParam(':sid', $sid, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);
$cnt = 1;

if($query->rowCount() > 0)
{
    foreach($results as $result)
    {
        // Calculate days since issue for overdue detection
        $issueDate = strtotime($result->IssuesDate);
        $today = time();
        $daysIssued = round(($today - $issueDate) / (60 * 60 * 24));
        $isOverdue = ($result->RetrunStatus == 0 && $daysIssued > 14); // 14 days policy
        $rowClass = $isOverdue ? 'class="overdue"' : '';
?>                                      
                                        <tr <?php echo $rowClass; ?>>
                                            <td class="center"><?php echo htmlentities($cnt);?></td>
                                            <td class="center"><?php echo htmlentities($result->BookID);?></td>
                                            <td class="center"><?php echo htmlentities($result->Title);?></td>
                                            <td class="center"><?php echo htmlentities(date('d-m-Y', strtotime($result->IssuesDate)));?></td>
                                            <td class="center">
                                                <?php if($result->ReturnDate == null) {
                                                    if($isOverdue) {
                                                        echo "<span style='color:red'>Overdue by " . ($daysIssued - 14) . " days</span>";
                                                    } else {
                                                        echo "<span style='color:blue'>Not Returned Yet</span><br>";
                                                        echo "<small>Due in " . (14 - $daysIssued) . " days</small>";
                                                    }
                                                } else {
                                                    echo htmlentities(date('d-m-Y', strtotime($result->ReturnDate)));
                                                }
                                                ?>
                                            </td>
                                            <td class="center">
                                                <?php if($result->RetrunStatus == 0): ?>
                                                    <span class="label label-warning">Issued</span>
                                                <?php else: ?>
                                                    <span class="label label-success">Returned</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="center">
                                                <?php 
                                                if($result->fine === null || $result->fine == 0) {
                                                    echo "N/A";
                                                } else {
                                                    echo "<span style='color:red'>$" . htmlentities($result->fine) . "</span>";
                                                }
                                                ?>
                                            </td>
                                        </tr>
<?php 
        $cnt = $cnt + 1;
    }
} else { ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No books currently issued to you.</td>
                                        </tr>
<?php } ?>                                      
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
        // Check if DataTable is already initialized
        if (!$.fn.DataTable.isDataTable('#issuedBooksTable')) {
            $('#issuedBooksTable').DataTable({
                responsive: true,
                "order": [[ 3, "desc" ]]
            });
        }
    });
    </script>
</body>
</html>
<?php } ?>