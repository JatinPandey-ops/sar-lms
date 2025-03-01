<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
{   
    header('location:index.php');
}
else{
    // Get student ID from URL
    $stdid = intval($_GET['stdid']);
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | Student History</title>
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
        @media print {
            .no-print, .no-print * {
                display: none !important;
            }
            header, footer, .sidebar-menu {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
                background: #fff !important;
            }
            .content-wrapper {
                margin-left: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }
            .container {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .printHeader {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
            }
            .panel {
                border: none !important;
                box-shadow: none !important;
            }
            .panel-heading {
                background-color: #f5f5f5 !important;
                color: #000 !important;
                border-bottom: 1px solid #ddd !important;
            }
        }
        .printHeader {
            display: none;
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
                    <?php
                    $sql = "SELECT FullName, StudentId FROM tblstudents WHERE id=:stdid";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':stdid', $stdid, PDO::PARAM_STR);
                    $query->execute();
                    $studentInfo = $query->fetch(PDO::FETCH_OBJ);
                    
                    if($query->rowCount() > 0) {
                    ?>
                    <h4 class="header-line">Book History for: <?php echo htmlentities($studentInfo->FullName); ?> (<?php echo htmlentities($studentInfo->StudentId); ?>)</h4>
                    
                    <!-- Print Header (only visible when printing) -->
                    <div class="printHeader">
                        <h2>Library Book History Report</h2>
                        <h3>Student: <?php echo htmlentities($studentInfo->FullName); ?> (ID: <?php echo htmlentities($studentInfo->StudentId); ?>)</h3>
                        <p>Report generated on: <?php echo date('d-m-Y H:i:s'); ?></p>
                        <hr>
                    </div>
                    
                    <div class="no-print" style="float: right; margin-bottom: 10px;">
                        <a href="javascript:void(0);" onclick="printReport();" class="btn btn-info">
                            <i class="fa fa-print"></i> Print Report
                        </a>
                    </div>
                    <?php } else { ?>
                    <h4 class="header-line">Student Not Found</h4>
                    <?php } ?>
                </div>
            </div>
            
            <?php
            // If student exists, show their history
            if($query->rowCount() > 0) {
                $studentId = $studentInfo->StudentId;
            ?>
            
            <div class="row">
                <div class="col-md-12">
                    <!-- Current Issued Books -->
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            Currently Issued Books
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Book ID</th>
                                            <th>Book Title</th>
                                            <th>Issued Date</th>
                                            <th>Days Issued</th>
                                            <th>Status</th>
                                            <th class="no-print">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                                    $sql = "SELECT i.BookId, b.Title, i.IssuesDate 
                                            FROM tblissuedbookdetails i
                                            JOIN tblbooks b ON b.BookID = i.BookId
                                            WHERE i.StudentID = :studentid AND i.RetrunStatus = 0
                                            ORDER BY i.IssuesDate DESC";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':studentid', $studentId, PDO::PARAM_STR);
                                    $query->execute();
                                    $currentBooks = $query->fetchAll(PDO::FETCH_OBJ);
                                    $cnt = 1;
                                    
                                    if($query->rowCount() > 0) {
                                        foreach($currentBooks as $book) {
                                            // Calculate days issued and check if overdue
                                            $issueDate = strtotime($book->IssuesDate);
                                            $today = time();
                                            $daysIssued = round(($today - $issueDate) / (60 * 60 * 24));
                                            $isOverdue = $daysIssued > 14; // 14 days policy
                                            $rowClass = $isOverdue ? 'class="overdue"' : '';
                                    ?>
                                        <tr <?php echo $rowClass; ?>>
                                            <td><?php echo htmlentities($cnt); ?></td>
                                            <td><?php echo htmlentities($book->BookId); ?></td>
                                            <td><?php echo htmlentities($book->Title); ?></td>
                                            <td><?php echo htmlentities(date('d-m-Y', strtotime($book->IssuesDate))); ?></td>
                                            <td>
                                                <?php echo htmlentities($daysIssued); ?> days
                                                <?php if($isOverdue): ?>
                                                    <span class="label label-danger">Overdue by <?php echo ($daysIssued - 14); ?> days</span>
                                                <?php else: ?>
                                                    <span class="label label-success"><?php echo (14 - $daysIssued); ?> days remaining</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="label label-warning">Issued</span>
                                            </td>
                                            <td class="no-print">
                                                <a href="#" data-toggle="modal" data-target="#returnModal<?php echo $cnt; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-undo"></i> Return
                                                </a>
                                                
                                                <!-- Return Modal -->
                                                <div class="modal fade" id="returnModal<?php echo $cnt; ?>" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                                <h4 class="modal-title" id="returnModalLabel">Return Book</h4>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form role="form" method="get" action="manage-issued-books.php">
                                                                    <input type="hidden" name="return" value="<?php echo htmlentities($book->BookId); ?>">
                                                                    <input type="hidden" name="studentid" value="<?php echo htmlentities($studentId); ?>">
                                                                    
                                                                    <div class="form-group">
                                                                        <label>Book Title</label>
                                                                        <input class="form-control" type="text" value="<?php echo htmlentities($book->Title); ?>" readonly>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label>Student ID</label>
                                                                        <input class="form-control" type="text" value="<?php echo htmlentities($studentId); ?>" readonly>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label>Issue Date</label>
                                                                        <input class="form-control" type="text" value="<?php echo htmlentities(date('d-m-Y', strtotime($book->IssuesDate))); ?>" readonly>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label>Days Issued</label>
                                                                        <input class="form-control" type="text" value="<?php echo $daysIssued; ?> days" readonly>
                                                                    </div>
                                                                    
                                                                    <?php if($isOverdue): ?>
                                                                    <div class="alert alert-warning">
                                                                        This book is overdue by <?php echo ($daysIssued - 14); ?> days.
                                                                        Suggested fine: $<?php echo ($daysIssued - 14); ?>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    
                                                                    <div class="form-group">
                                                                        <label>Fine (if any)</label>
                                                                        <input class="form-control" type="number" name="fine" min="0" value="<?php echo $isOverdue ? ($daysIssued - 14) : 0; ?>">
                                                                    </div>
                                                                    
                                                                    <button type="submit" class="btn btn-primary">Confirm Return</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php
                                            $cnt++;
                                        }
                                    } else {
                                    ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No books currently issued to this student.</td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <!-- Past Book History -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Past Book History
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Book ID</th>
                                            <th>Book Title</th>
                                            <th>Issued Date</th>
                                            <th>Returned Date</th>
                                            <th>Days Kept</th>
                                            <th>Fine Paid</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                                    $sql = "SELECT i.BookId, b.Title, i.IssuesDate, i.ReturnDate, i.fine 
                                            FROM tblissuedbookdetails i
                                            JOIN tblbooks b ON b.BookID = i.BookId
                                            WHERE i.StudentID = :studentid AND i.RetrunStatus = 1
                                            ORDER BY i.ReturnDate DESC";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':studentid', $studentId, PDO::PARAM_STR);
                                    $query->execute();
                                    $pastBooks = $query->fetchAll(PDO::FETCH_OBJ);
                                    $cnt = 1;
                                    
                                    if($query->rowCount() > 0) {
                                        foreach($pastBooks as $book) {
                                            // Calculate how many days the book was kept
                                            $issueDate = strtotime($book->IssuesDate);
                                            $returnDate = strtotime($book->ReturnDate);
                                            $daysKept = round(($returnDate - $issueDate) / (60 * 60 * 24));
                                            $wasOverdue = $daysKept > 14; // 14 days policy
                                    ?>
                                        <tr>
                                            <td><?php echo htmlentities($cnt); ?></td>
                                            <td><?php echo htmlentities($book->BookId); ?></td>
                                            <td><?php echo htmlentities($book->Title); ?></td>
                                            <td><?php echo htmlentities(date('d-m-Y', strtotime($book->IssuesDate))); ?></td>
                                            <td><?php echo htmlentities(date('d-m-Y', strtotime($book->ReturnDate))); ?></td>
                                            <td>
                                                <?php echo htmlentities($daysKept); ?> days
                                                <?php if($wasOverdue): ?>
                                                    <span class="label label-danger">Was overdue</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if($book->fine > 0) {
                                                    echo '<span class="text-danger">$' . htmlentities($book->fine) . '</span>';
                                                } else {
                                                    echo 'None';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php
                                            $cnt++;
                                        }
                                    } else {
                                    ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No book history found for this student.</td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            Student Statistics
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <?php
                                // Get total books issued (current and past)
                                $sql = "SELECT COUNT(*) as total FROM tblissuedbookdetails WHERE StudentID = :studentid";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':studentid', $studentId, PDO::PARAM_STR);
                                $query->execute();
                                $totalIssued = $query->fetch(PDO::FETCH_OBJ)->total;
                                
                                // Get total books returned
                                $sql = "SELECT COUNT(*) as returned FROM tblissuedbookdetails WHERE StudentID = :studentid AND RetrunStatus = 1";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':studentid', $studentId, PDO::PARAM_STR);
                                $query->execute();
                                $totalReturned = $query->fetch(PDO::FETCH_OBJ)->returned;
                                
                                // Get total fines paid
                                $sql = "SELECT SUM(fine) as total_fine FROM tblissuedbookdetails WHERE StudentID = :studentid AND RetrunStatus = 1";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':studentid', $studentId, PDO::PARAM_STR);
                                $query->execute();
                                $totalFines = $query->fetch(PDO::FETCH_OBJ)->total_fine;
                                if($totalFines == null) $totalFines = 0;
                                
                                // Get currently issued books
                                $sql = "SELECT COUNT(*) as current FROM tblissuedbookdetails WHERE StudentID = :studentid AND RetrunStatus = 0";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':studentid', $studentId, PDO::PARAM_STR);
                                $query->execute();
                                $currentlyIssued = $query->fetch(PDO::FETCH_OBJ)->current;
                                
                                // Get number of overdue books
                                $sql = "SELECT COUNT(*) as overdue FROM tblissuedbookdetails 
                                        WHERE StudentID = :studentid AND RetrunStatus = 0 
                                        AND DATEDIFF(NOW(), IssuesDate) > 14";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':studentid', $studentId, PDO::PARAM_STR);
                                $query->execute();
                                $overdueBooks = $query->fetch(PDO::FETCH_OBJ)->overdue;
                                ?>
                                
                                <div class="col-md-4 col-sm-4">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">Books History</div>
                                        <div class="panel-body">
                                            <p><strong>Total Books Borrowed:</strong> <?php echo $totalIssued; ?></p>
                                            <p><strong>Books Returned:</strong> <?php echo $totalReturned; ?></p>
                                            <p><strong>Currently Issued:</strong> <?php echo $currentlyIssued; ?></p>
                                            <p><strong>Overdue Books:</strong> <?php echo $overdueBooks; ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 col-sm-4">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">Fines Information</div>
                                        <div class="panel-body">
                                            <p><strong>Total Fines Paid:</strong> $<?php echo $totalFines; ?></p>
                                            <?php if($overdueBooks > 0): ?>
                                            <p><strong>Potential Current Fines:</strong> 
                                                <span class="text-danger">
                                                <?php
                                                // Calculate potential fines on current overdue books
                                                $sql = "SELECT SUM(DATEDIFF(NOW(), IssuesDate) - 14) as potential_fine 
                                                        FROM tblissuedbookdetails 
                                                        WHERE StudentID = :studentid AND RetrunStatus = 0 
                                                        AND DATEDIFF(NOW(), IssuesDate) > 14";
                                                $query = $dbh->prepare($sql);
                                                $query->bindParam(':studentid', $studentId, PDO::PARAM_STR);
                                                $query->execute();
                                                $potentialFine = $query->fetch(PDO::FETCH_OBJ)->potential_fine;
                                                if($potentialFine == null) $potentialFine = 0;
                                                echo '$' . $potentialFine . ' (estimated)';
                                                ?>
                                                </span>
                                            </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 col-sm-4">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">Account Status</div>
                                        <div class="panel-body">
                                            <?php 
                                            // Get student status
                                            $sql = "SELECT Status, RegDate FROM tblstudents WHERE StudentId=:studentid";
                                            $query = $dbh->prepare($sql);
                                            $query->bindParam(':studentid', $studentId, PDO::PARAM_STR);
                                            $query->execute();
                                            $statusInfo = $query->fetch(PDO::FETCH_OBJ);
                                            ?>
                                            <p><strong>Account Status:</strong> 
                                                <?php if($statusInfo->Status == 1): ?>
                                                <span class="label label-success">Active</span>
                                                <?php else: ?>
                                                <span class="label label-danger">Blocked</span>
                                                <?php endif; ?>
                                            </p>
                                            <p><strong>Registration Date:</strong> <?php echo date('d-m-Y', strtotime($statusInfo->RegDate)); ?></p>
                                            <p class="no-print">
                                                <a href="edit-student.php?stdid=<?php echo $stdid; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-edit"></i> Edit Student
                                                </a>
                                                <?php if($statusInfo->Status == 1): ?>
                                                <a href="reg-students.php?inid=<?php echo $stdid; ?>" onclick="return confirm('Are you sure you want to block this student?');" class="btn btn-danger btn-sm">
                                                    <i class="fa fa-ban"></i> Block
                                                </a>
                                                <?php else: ?>
                                                <a href="reg-students.php?id=<?php echo $stdid; ?>" onclick="return confirm('Are you sure you want to activate this student?');" class="btn btn-success btn-sm">
                                                    <i class="fa fa-check"></i> Activate
                                                </a>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row no-print">
                <div class="col-md-12">
                    <a href="reg-students.php" class="btn btn-primary">
                        <i class="fa fa-arrow-left"></i> Back to Students List
                    </a>
                </div>
            </div>
            
            <?php } else { ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-danger">Student not found.</div>
                    <a href="reg-students.php" class="btn btn-primary">Back to Students List</a>
                </div>
            </div>
            <?php } ?>
            
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
    function printReport() {
        window.print();
    }
    </script>
</body>
</html>
<?php } ?>