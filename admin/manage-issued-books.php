<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
} else { 
    // For returning books
    if(isset($_GET['return'])) {
        $bookId = $_GET['return'];
        $studentId = $_GET['studentid'];
        $fine = intval($_GET['fine']);
        
        // Update issue record
        $sql = "UPDATE tblissuedbookdetails SET ReturnDate=:returndate, RetrunStatus=1, fine=:fine 
                WHERE BookId=:bookid AND StudentID=:studentid AND RetrunStatus=0";
        $returndate = date('Y-m-d H:i:s');
        $query = $dbh->prepare($sql);
        $query->bindParam(':bookid', $bookId, PDO::PARAM_STR);
        $query->bindParam(':studentid', $studentId, PDO::PARAM_STR);
        $query->bindParam(':returndate', $returndate, PDO::PARAM_STR);
        $query->bindParam(':fine', $fine, PDO::PARAM_INT);
        $query->execute();
        
        // Update book available copies manually since trigger might not work
        $updateBook = "UPDATE tblbooks SET AvailableCopies = AvailableCopies + 1 
                       WHERE BookID = :bookid AND AvailableCopies < TotalCopies";
        $updateQuery = $dbh->prepare($updateBook);
        $updateQuery->bindParam(':bookid', $bookId, PDO::PARAM_STR);
        $updateQuery->execute();
        
        $_SESSION['msg'] = "Book returned successfully";
        header('location:manage-issued-books.php');
    }
?>
<!DOCTYPE html>
<html>
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
<?php include('includes/header.php');?>

<div class="content-wrapper">
    <div class="container">
        <div class="row pad-botm">
            <div class="col-md-12">
                <h4 class="header-line">Manage Issued Books</h4>
            </div>
            <div class="col-md-6">
                <a href="issue-book.php" class="btn btn-success">Issue New Book</a>
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
            
            <?php if(isset($_SESSION['msg']) && $_SESSION['msg']!="") { ?>
            <div class="col-md-6">
                <div class="alert alert-success">
                    <strong>Success:</strong> <?php echo htmlentities($_SESSION['msg']); ?>
                    <?php $_SESSION['msg']=""; ?>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- Filter Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Filter Options
                    </div>
                    <div class="panel-body">
                        <button id="showAll" class="btn btn-default">All Books</button>
                        <button id="showIssued" class="btn btn-warning">Currently Issued</button>
                        <button id="showReturned" class="btn btn-success">Returned Books</button>
                        <button id="showOverdue" class="btn btn-danger">Overdue Books</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Issued Books Listing
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Book ID</th>
                                        <th>Book Title</th>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Issue Date</th>
                                        <th>Return Date</th>
                                        <th>Status</th>
                                        <th>Fine</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $sql = "SELECT tblissuedbookdetails.BookId, 
                                            tblbooks.Title, tblissuedbookdetails.StudentID, 
                                            tblstudents.FullName, tblissuedbookdetails.IssuesDate, 
                                            tblissuedbookdetails.ReturnDate, tblissuedbookdetails.fine,
                                            tblissuedbookdetails.RetrunStatus
                                            FROM tblissuedbookdetails 
                                            JOIN tblbooks ON tblbooks.BookID=tblissuedbookdetails.BookId 
                                            JOIN tblstudents ON tblstudents.StudentId=tblissuedbookdetails.StudentID
                                            ORDER BY tblissuedbookdetails.IssuesDate DESC";
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                    $cnt = 1;
                                    if($query->rowCount() > 0) {
                                        foreach($results as $result) { 
                                            // Calculate days since issue for overdue detection
                                            $issueDate = strtotime($result->IssuesDate);
                                            $today = time();
                                            $daysIssued = round(($today - $issueDate) / (60 * 60 * 24));
                                            $isOverdue = ($result->RetrunStatus == 0 && $daysIssued > 14); // 14 days policy
                                            $rowClass = $isOverdue ? 'class="overdue"' : '';
                                        ?>                                      
                                            <tr <?php echo $rowClass; ?> data-status="<?php echo $result->RetrunStatus; ?>" data-overdue="<?php echo $isOverdue ? '1' : '0'; ?>">
                                                <td><?php echo htmlentities($cnt);?></td>
                                                <td><?php echo htmlentities($result->BookId);?></td>
                                                <td><?php echo htmlentities($result->Title);?></td>
                                                <td><?php echo htmlentities($result->StudentID);?></td>
                                                <td><?php echo htmlentities($result->FullName);?></td>
                                                <td><?php echo htmlentities(date('d-m-Y', strtotime($result->IssuesDate)));?></td>
                                                <td>
                                                    <?php if($result->ReturnDate == null) {
                                                        if($isOverdue) {
                                                            echo "<span class='text-danger'>Overdue by " . ($daysIssued - 14) . " days</span>";
                                                        } else {
                                                            echo "Not Returned Yet";
                                                        }
                                                    } else {
                                                        echo htmlentities(date('d-m-Y', strtotime($result->ReturnDate)));
                                                    } ?>
                                                </td>
                                                <td>
                                                    <?php if($result->RetrunStatus == 0): ?>
                                                        <span class="label label-warning">Issued</span>
                                                    <?php else: ?>
                                                        <span class="label label-success">Returned</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $result->fine === null ? "N/A" : ("$" . htmlentities($result->fine));?></td>
                                                <td>
                                                    <?php if($result->RetrunStatus == 0) { 
                                                        // Calculate suggested fine based on days overdue
                                                        $suggestedFine = $isOverdue ? (($daysIssued - 14) * 1) : 0; // $1 per day overdue
                                                    ?>
                                                    <a href="#" data-toggle="modal" data-target="#returnModal<?php echo $cnt; ?>" class="btn btn-primary btn-sm">
                                                        <i class="fa fa-undo"></i> Return
                                                    </a>
                                                    
                                                    <!-- Return Book Modal -->
                                                    <div class="modal fade" id="returnModal<?php echo $cnt; ?>" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                                    <h4 class="modal-title" id="returnModalLabel">Return Book</h4>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <form role="form" method="get" action="manage-issued-books.php">
                                                                        <input type="hidden" name="return" value="<?php echo htmlentities($result->BookId); ?>">
                                                                        <input type="hidden" name="studentid" value="<?php echo htmlentities($result->StudentID); ?>">
                                                                        
                                                                        <div class="form-group">
                                                                            <label>Book Title</label>
                                                                            <input class="form-control" type="text" value="<?php echo htmlentities($result->Title); ?>" readonly>
                                                                        </div>
                                                                        
                                                                        <div class="form-group">
                                                                            <label>Student ID</label>
                                                                            <input class="form-control" type="text" value="<?php echo htmlentities($result->StudentID); ?>" readonly>
                                                                        </div>
                                                                        
                                                                        <div class="form-group">
                                                                            <label>Issue Date</label>
                                                                            <input class="form-control" type="text" value="<?php echo htmlentities(date('d-m-Y', strtotime($result->IssuesDate))); ?>" readonly>
                                                                        </div>
                                                                        
                                                                        <div class="form-group">
                                                                            <label>Days Issued</label>
                                                                            <input class="form-control" type="text" value="<?php echo $daysIssued; ?> days" readonly>
                                                                        </div>
                                                                        
                                                                        <?php if($isOverdue): ?>
                                                                        <div class="alert alert-warning">
                                                                            This book is overdue by <?php echo ($daysIssued - 14); ?> days.
                                                                            Suggested fine: $<?php echo $suggestedFine; ?>
                                                                        </div>
                                                                        <?php endif; ?>
                                                                        
                                                                        <div class="form-group">
                                                                            <label>Fine (if any)</label>
                                                                            <input class="form-control" type="number" name="fine" min="0" value="<?php echo $suggestedFine; ?>">
                                                                        </div>
                                                                        
                                                                        <button type="submit" class="btn btn-primary">Confirm Return</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php } else { ?>
                                                        <button disabled class="btn btn-default btn-sm">Returned</button>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                    <?php 
                                            $cnt++; 
                                        } 
                                    } ?>                                      
                                </tbody>
                            </table>
                        </div>   
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php');?>

<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/dataTables/jquery.dataTables.js"></script>
<script src="assets/js/dataTables/dataTables.bootstrap.js"></script>
<script>
    $(document).ready(function () {
        var table = $('#dataTables-example').DataTable({
            "order": [[ 5, "desc" ]] // Sort by issue date by default (newest first)
        });
        
        // Filter buttons functionality
        $('#showAll').click(function() {
            table.search('').columns().search('').draw();
        });
        
        $('#showIssued').click(function() {
            table.columns(7).search('Issued').draw();
        });
        
        $('#showReturned').click(function() {
            table.columns(7).search('Returned').draw();
        });
        
        $('#showOverdue').click(function() {
            // Filter rows with overdue attribute
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var row = table.row(dataIndex).node();
                    return $(row).attr('data-overdue') === '1';
                }
            );
            table.draw();
            $.fn.dataTable.ext.search.pop(); // Remove the filter after drawing
        });
    });
</script>
<script src="assets/js/custom.js"></script>

</body>
</html>
<?php } ?>