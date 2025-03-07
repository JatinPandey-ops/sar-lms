<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0) {   
    header('location:index.php');
} else { 
    // For returning books
    if(isset($_GET['return'])) {
        $id = $_GET['return'];
        $studentId = $_GET['studentid'];
        $fine = intval($_GET['fine']);
        $returnDate = isset($_GET['returndate']) ? $_GET['returndate'] : date('Y-m-d H:i:s');
        
        $sql = "UPDATE tblissuedbookdetails SET ReturnDate=:returndate, RetrunStatus=1, fine=:fine 
                WHERE BookId=:id AND StudentID=:studentid AND RetrunStatus=0";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->bindParam(':studentid', $studentId, PDO::PARAM_STR);
        $query->bindParam(':returndate', $returnDate, PDO::PARAM_STR);
        $query->bindParam(':fine', $fine, PDO::PARAM_STR);
        $query->execute();
        
        // Update the book available copies
        $updateBook = "UPDATE tblbooks SET AvailableCopies = AvailableCopies + 1 
                      WHERE BookID = :id AND AvailableCopies < TotalCopies";
        $updateQuery = $dbh->prepare($updateBook);
        $updateQuery->bindParam(':id', $id, PDO::PARAM_STR);
        $updateQuery->execute();
        
        $_SESSION['msg'] = "Book returned successfully";
        
        // If the request came from the student-history page, redirect back there
        if(isset($_GET['from']) && $_GET['from'] == 'history') {
            header('location:student-history.php?stdid=' . $_GET['stdid']);
        } else {
            header('location:manage-issued-books.php');
        }
        exit;
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
                                        <th>Fine</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $sql = "SELECT i.BookId, i.StudentID, i.IssuesDate, i.ReturnDate, i.fine,
                                            i.RetrunStatus, b.Title, s.FullName
                                            FROM tblissuedbookdetails i
                                            JOIN tblbooks b ON b.BookID=i.BookId 
                                            JOIN tblstudents s ON s.StudentId=i.StudentID
                                            ORDER BY i.IssuesDate DESC";
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
                                            <tr <?php echo $rowClass; ?>>
                                                <td><?php echo htmlentities($cnt);?></td>
                                                <td><?php echo htmlentities($result->BookId);?></td>
                                                <td><?php echo htmlentities($result->Title);?></td>
                                                <td><?php echo htmlentities($result->StudentID);?></td>
                                                <td><?php echo htmlentities($result->FullName);?></td>
                                                <td><?php echo htmlentities(date('d-m-Y', strtotime($result->IssuesDate)));?></td>
                                                <td>
                                                    <?php if($result->ReturnDate == null) {
                                                        echo "Not Returned Yet";
                                                    } else {
                                                        echo htmlentities(date('d-m-Y', strtotime($result->ReturnDate)));
                                                    } ?>
                                                </td>
                                                <td><?php echo $result->fine === null ? "N/A" : ("RM " . htmlentities($result->fine));?></td>
                                                <td>
                                                    <?php if($result->RetrunStatus == 0) { ?>
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
                                                                            <label>Return Date</label>
                                                                            <input class="form-control" type="date" name="returndate" id="returndate<?php echo $cnt; ?>" value="<?php echo date('Y-m-d'); ?>" onchange="calculateFine<?php echo $cnt; ?>()" required>
                                                                        </div>
                                                                        
                                                                        <div class="form-group">
                                                                            <label>Days Kept</label>
                                                                            <input class="form-control" type="text" id="daysKept<?php echo $cnt; ?>" value="<?php echo $daysIssued; ?> days" readonly>
                                                                        </div>
                                                                        
                                                                        <div id="overdueInfo<?php echo $cnt; ?>">
                                                                            <?php if($isOverdue): ?>
                                                                            <div class="alert alert-warning">
                                                                                <p>This book is overdue by <?php echo ($daysIssued - 14); ?> days.</p>
                                                                                <p>Fine rate: 5 RM per day after the 14-day period.</p>
                                                                                <p>Calculated fine: <?php echo (($daysIssued - 14) * 5); ?> RM</p>
                                                                            </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        
                                                                        <div class="form-group">
                                                                            <label>Fine (RM)</label>
                                                                            <input class="form-control" type="number" name="fine" id="fine<?php echo $cnt; ?>" min="0" value="<?php echo $isOverdue ? (($daysIssued - 14) * 5) : 0; ?>" readonly>
                                                                            <small class="text-muted">Fine is calculated at 5 RM per day after the 14-day period</small>
                                                                        </div>
                                                                        
                                                                        <div class="form-group">
                                                                            <label>Override Fine (if needed)</label>
                                                                            <div class="input-group">
                                                                                <span class="input-group-addon">
                                                                                    <input type="checkbox" id="overrideFine<?php echo $cnt; ?>" onchange="toggleFineEdit<?php echo $cnt; ?>()">
                                                                                </span>
                                                                                <input type="number" class="form-control" id="manualFine<?php echo $cnt; ?>" min="0" disabled>
                                                                            </div>
                                                                            <small class="text-muted">Check this box to manually override the calculated fine</small>
                                                                        </div>
                                                                        
                                                                        <button type="submit" class="btn btn-primary">Confirm Return</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- JavaScript for this specific modal -->
                                                    <script>
                                                        // Calculate fine based on selected return date
                                                        function calculateFine<?php echo $cnt; ?>() {
                                                            // Get the issue date and selected return date
                                                            var issueDate = new Date("<?php echo date('Y-m-d', strtotime($result->IssuesDate)); ?>");
                                                            var returnDate = new Date(document.getElementById('returndate<?php echo $cnt; ?>').value);
                                                            
                                                            // Calculate days between dates
                                                            var timeDiff = returnDate.getTime() - issueDate.getTime();
                                                            var daysKept = Math.ceil(timeDiff / (1000 * 3600 * 24));
                                                            
                                                            // Update days kept field
                                                            document.getElementById('daysKept<?php echo $cnt; ?>').value = daysKept + " days";
                                                            
                                                            // Calculate fine (5 RM per day after 14 days)
                                                            var fine = 0;
                                                            if (daysKept > 14) {
                                                                fine = (daysKept - 14) * 5;
                                                            }
                                                            
                                                            // Update fine field
                                                            document.getElementById('fine<?php echo $cnt; ?>').value = fine;
                                                            
                                                            // Update overdue information
                                                            var overdueInfo = document.getElementById('overdueInfo<?php echo $cnt; ?>');
                                                            if (daysKept > 14) {
                                                                overdueInfo.innerHTML = `
                                                                    <div class="alert alert-warning">
                                                                        <p>This book is overdue by ${daysKept - 14} days.</p>
                                                                        <p>Fine rate: 5 RM per day after the 14-day period.</p>
                                                                        <p>Calculated fine: ${fine} RM</p>
                                                                    </div>
                                                                `;
                                                            } else {
                                                                overdueInfo.innerHTML = `
                                                                    <div class="alert alert-success">
                                                                        <p>This book is being returned within the 14-day period.</p>
                                                                        <p>No fine will be charged.</p>
                                                                    </div>
                                                                `;
                                                            }
                                                        }
                                                        
                                                        // Toggle between automatic and manual fine
                                                        function toggleFineEdit<?php echo $cnt; ?>() {
                                                            var checkbox = document.getElementById('overrideFine<?php echo $cnt; ?>');
                                                            var fineField = document.getElementById('fine<?php echo $cnt; ?>');
                                                            var manualFineField = document.getElementById('manualFine<?php echo $cnt; ?>');
                                                            
                                                            if (checkbox.checked) {
                                                                // Enable manual override
                                                                fineField.readOnly = false;
                                                                manualFineField.disabled = false;
                                                                manualFineField.value = fineField.value;
                                                                
                                                                // Add an event listener to update the actual fine field
                                                                manualFineField.addEventListener('input', function() {
                                                                    fineField.value = this.value;
                                                                });
                                                            } else {
                                                                // Disable manual override and recalculate
                                                                fineField.readOnly = true;
                                                                manualFineField.disabled = true;
                                                                calculateFine<?php echo $cnt; ?>();
                                                            }
                                                        }
                                                        
                                                        // Run calculation when the modal is shown
                                                        $('#returnModal<?php echo $cnt; ?>').on('shown.bs.modal', function() {
                                                            calculateFine<?php echo $cnt; ?>();
                                                        });
                                                    </script>
                                                    <?php } else { ?>
                                                        <span class="label label-success">Returned</span>
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
$(document).ready(function() {
    $('#dataTables-example').DataTable({
        responsive: true,
        "order": [[ 5, "desc" ]] /
    });
});
</script>

</body>
</html>
<?php } ?>