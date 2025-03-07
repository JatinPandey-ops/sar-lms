<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
{   
    header('location:index.php');
}
else{ 
    if(isset($_GET['del']))
    {
        $id=$_GET['del'];
        // Check if book is currently issued
        $checkIssueSql = "SELECT COUNT(*) as count FROM tblissuedbookdetails WHERE BookId=:id AND RetrunStatus=0";
        $checkQuery = $dbh->prepare($checkIssueSql);
        $checkQuery->bindParam(':id', $id, PDO::PARAM_STR);
        $checkQuery->execute();
        $issuedCount = $checkQuery->fetch(PDO::FETCH_OBJ);
        
        if($issuedCount->count > 0) {
            $_SESSION['error'] = "Cannot delete book. It is currently issued to " . $issuedCount->count . " student(s).";
            header('location:manage-books.php');
            exit;
        }
        
        $sql = "DELETE FROM tblbooks WHERE BookID=:id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->execute();
        $_SESSION['delmsg'] = "Book deleted successfully";
        header('location:manage-books.php');
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | Manage Books</title>
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
                    <h4 class="header-line">Manage Books</h4>
                </div>
                <div class="col-md-6">
                    <a href="add-book.php" class="btn btn-success">Add New Book</a>
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
                
                <?php if(isset($_SESSION['updatemsg']) && $_SESSION['updatemsg']!="") { ?>
                <div class="col-md-6">
                    <div class="alert alert-success">
                        <strong>Success:</strong> <?php echo htmlentities($_SESSION['updatemsg']); ?>
                        <?php $_SESSION['updatemsg']=""; ?>
                    </div>
                </div>
                <?php } ?>
                
                <?php if(isset($_SESSION['delmsg']) && $_SESSION['delmsg']!="") { ?>
                <div class="col-md-6">
                    <div class="alert alert-success">
                        <strong>Success:</strong> <?php echo htmlentities($_SESSION['delmsg']); ?>
                        <?php $_SESSION['delmsg']=""; ?>
                    </div>
                </div>
                <?php } ?>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <!-- Advanced Tables -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                           Books Listing
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Book ID</th>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Author</th>
                                            <th>Publisher</th>
                                            <th>Total Copies</th>
                                            <th>Available</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                                    $sql = "SELECT tblbooks.BookID, tblbooks.Title, tblbooks.AuthorName, 
                                            tblbooks.Publisher, tblbooks.TotalCopies, tblbooks.AvailableCopies,
                                            tblcategory.CategoryName 
                                            FROM tblbooks 
                                            JOIN tblcategory ON tblcategory.id = tblbooks.CatId
                                            ORDER BY tblbooks.Title ASC";
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                    $cnt = 1;
                                    if($query->rowCount() > 0) {
                                        foreach($results as $result) { 
                                            $availabilityClass = ($result->AvailableCopies < 1) ? 'danger' : '';
                                        ?>                                      
                                            <tr class="<?php echo $availabilityClass; ?>">
                                                <td><?php echo htmlentities($cnt);?></td>
                                                <td><?php echo htmlentities($result->BookID);?></td>
                                                <td><?php echo htmlentities($result->Title);?></td>
                                                <td><?php echo htmlentities($result->CategoryName);?></td>
                                                <td><?php echo htmlentities($result->AuthorName);?></td>
                                                <td><?php echo htmlentities($result->Publisher);?></td>
                                                <td><?php echo htmlentities($result->TotalCopies);?></td>
                                                <td>
                                                    <?php if($result->AvailableCopies < 1): ?>
                                                        <span class="label label-danger">Not Available</span>
                                                    <?php else: ?>
                                                        <?php echo htmlentities($result->AvailableCopies); ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="edit-book.php?bookID=<?php echo htmlentities($result->BookID);?>">
                                                        <button class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Edit</button>
                                                    </a>
                                                    <a href="manage-books.php?del=<?php echo htmlentities($result->BookID);?>" onclick="return confirm('Are you sure you want to delete?');">
                                                        <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Delete</button>
                                                    </a>
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
</body>
</html>
<?php } ?>