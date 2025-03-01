<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
  { 
header('location:index.php');
}
else{?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <![endif]-->
    <title>Online Library Management System | Admin Dash Board</title>
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
                <h4 class="header-line">ADMIN DASHBOARD</h4>
                
                            </div>

        </div>
             
             <div class="row">

 <div class="col-md-3 col-sm-3 col-xs-6">
                      <div class="alert alert-success back-widget-set text-center">
                            <i class="fa fa-book fa-5x"></i>
<?php 
$sql ="SELECT BookID from tblbooks ";
$query = $dbh->prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$listdbooks=$query->rowCount();
?>


                            <h3><?php echo htmlentities($listdbooks);?></h3>
                      Books Listed
                        </div>
                    </div>

            
                 <div class="col-md-3 col-sm-3 col-xs-6">
                      <div class="alert alert-info back-widget-set text-center">
                            <i class="fa fa-bars fa-5x"></i>
<?php 
$sql1 ="SELECT BookId, StudentID FROM tblissuedbookdetails ";
$query1 = $dbh->prepare($sql1);
$query1->execute();
$results1=$query1->fetchAll(PDO::FETCH_OBJ);
$issuedbooks=$query1->rowCount();
?>

                            <h3><?php echo htmlentities($issuedbooks);?> </h3>
                           Times Book Issued
                        </div>
                    </div>
             
               <div class="col-md-3 col-sm-3 col-xs-6">
                      <div class="alert alert-warning back-widget-set text-center">
                            <i class="fa fa-recycle fa-5x"></i>
<?php 
$status=1;
$sql2 ="SELECT BookId FROM tblissuedbookdetails WHERE RetrunStatus=:status";
$query2 = $dbh->prepare($sql2);
$query2->bindParam(':status',$status,PDO::PARAM_STR);
$query2->execute();
$results2=$query2->fetchAll(PDO::FETCH_OBJ);
$returnedbooks=$query2->rowCount();
?>

                            <h3><?php echo htmlentities($returnedbooks);?></h3>
                          Times  Books Returned
                        </div>
                    </div>
               <div class="col-md-3 col-sm-3 col-xs-6">
                      <div class="alert alert-danger back-widget-set text-center">
                            <i class="fa fa-users fa-5x"></i>
                            <?php 
$sql3 ="SELECT id from tblstudents ";
$query3 = $dbh->prepare($sql3);
$query3->execute();
$results3=$query3->fetchAll(PDO::FETCH_OBJ);
$regstds=$query3->rowCount();
?>
                            <h3><?php echo htmlentities($regstds);?></h3>
                           Registered Users
                        </div>
                    </div>

        </div>



 <div class="row">

 <div class="col-md-3 col-sm-3 col-xs-6">
                      <div class="alert alert-success back-widget-set text-center">
                            <i class="fa fa-book fa-5x"></i>
<?php 
// Count unique authors from books table since you don't have a separate authors table
$sq4 ="SELECT COUNT(DISTINCT AuthorName) as author_count FROM tblbooks";
$query4 = $dbh->prepare($sq4);
$query4->execute();
$result4=$query4->fetch(PDO::FETCH_OBJ);
$listdathrs = $result4->author_count;
?>

                            <h3><?php echo htmlentities($listdathrs);?></h3>
                      Unique Authors
                        </div>
                    </div>

            
                 <div class="col-md-3 col-sm-3 rscol-xs-6">
                      <div class="alert alert-info back-widget-set text-center">
                            <i class="fa fa-file-archive-o fa-5x"></i>
<?php 
$sql5 ="SELECT id from tblcategory ";
$query5 = $dbh->prepare($sql5);
$query5->execute();
$results5=$query5->fetchAll(PDO::FETCH_OBJ);
$listdcats=$query5->rowCount();
?>

                            <h3><?php echo htmlentities($listdcats);?> </h3>
                           Listed Categories
                        </div>
                    </div>

                <div class="col-md-3 col-sm-3 col-xs-6">
                      <div class="alert alert-warning back-widget-set text-center">
                            <i class="fa fa-exclamation-triangle fa-5x"></i>
<?php 
// Count books that are fully checked out (AvailableCopies = 0)
$sql6 ="SELECT COUNT(*) as out_of_stock FROM tblbooks WHERE AvailableCopies = 0";
$query6 = $dbh->prepare($sql6);
$query6->execute();
$result6=$query6->fetch(PDO::FETCH_OBJ);
$outofstock = $result6->out_of_stock;
?>

                            <h3><?php echo htmlentities($outofstock);?></h3>
                          Books Out of Stock
                        </div>
                    </div>

                <div class="col-md-3 col-sm-3 col-xs-6">
                      <div class="alert alert-danger back-widget-set text-center">
                            <i class="fa fa-clock-o fa-5x"></i>
<?php 
// Count overdue books (books issued more than 14 days ago and not returned)
$sql7 ="SELECT COUNT(*) as overdue_count FROM tblissuedbookdetails 
        WHERE RetrunStatus=0 AND DATEDIFF(NOW(), IssuesDate) > 14";
$query7 = $dbh->prepare($sql7);
$query7->execute();
$result7=$query7->fetch(PDO::FETCH_OBJ);
$overduebooks = $result7->overdue_count;
?>

                            <h3><?php echo htmlentities($overduebooks);?></h3>
                           Overdue Books
                        </div>
                    </div>
             

        </div>             

        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Books Availability Status
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Book ID</th>
                                        <th>Book Title</th>
                                        <th>Category</th>
                                        <th>Author</th>
                                        <th>Total Copies</th>
                                        <th>Available</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $sql8 = "SELECT b.BookID, b.Title, c.CategoryName, b.AuthorName, 
                                         b.TotalCopies, b.AvailableCopies 
                                         FROM tblbooks b
                                         JOIN tblcategory c ON c.id = b.CatId
                                         ORDER BY b.AvailableCopies ASC
                                         LIMIT 10";
                                $query8 = $dbh->prepare($sql8);
                                $query8->execute();
                                $results8 = $query8->fetchAll(PDO::FETCH_OBJ);
                                $cnt = 1;
                                if($query8->rowCount() > 0) {
                                    foreach($results8 as $result) { ?>
                                        <tr>
                                            <td><?php echo htmlentities($cnt);?></td>
                                            <td><?php echo htmlentities($result->BookID);?></td>
                                            <td><?php echo htmlentities($result->Title);?></td>
                                            <td><?php echo htmlentities($result->CategoryName);?></td>
                                            <td><?php echo htmlentities($result->AuthorName);?></td>
                                            <td><?php echo htmlentities($result->TotalCopies);?></td>
                                            <td><?php echo htmlentities($result->AvailableCopies);?></td>
                                            <td>
                                            <?php if($result->AvailableCopies == 0) { ?>
                                                <span class="label label-danger">Out of Stock</span>
                                            <?php } else if($result->AvailableCopies < ($result->TotalCopies * 0.3)) { ?>
                                                <span class="label label-warning">Low Stock</span>
                                            <?php } else { ?>
                                                <span class="label label-success">Available</span>
                                            <?php } ?>
                                            </td>
                                        </tr>
                                    <?php $cnt++; }
                                } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

             <div class="row">

              <div class="col-md-10 col-sm-8 col-xs-12 col-md-offset-1">
                    <div id="carousel-example" class="carousel slide slide-bdr" data-ride="carousel" >
                   
                    <div class="carousel-inner">
                        <div class="item active">

                            <img src="assets/img/1.jpg" alt="" />
                           
                        </div>
                        <div class="item">
                            <img src="assets/img/2.jpg" alt="" />
                          
                        </div>
                        <div class="item">
                            <img src="assets/img/3.jpg" alt="" />
                           
                        </div>
                    </div>
                    <!--INDICATORS-->
                     <ol class="carousel-indicators">
                        <li data-target="#carousel-example" data-slide-to="0" class="active"></li>
                        <li data-target="#carousel-example" data-slide-to="1"></li>
                        <li data-target="#carousel-example" data-slide-to="2"></li>
                    </ol>
                    <!--PREVIUS-NEXT BUTTONS-->
                     <a class="left carousel-control" href="#carousel-example" data-slide="prev">
    <span class="glyphicon glyphicon-chevron-left"></span>
  </a>
  <a class="right carousel-control" href="#carousel-example" data-slide="next">
    <span class="glyphicon glyphicon-chevron-right"></span>
  </a>
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