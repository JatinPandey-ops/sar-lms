<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
{   
    header('location:index.php');
}
else{ 

if(isset($_POST['update']))
{
    $bookname=$_POST['bookname'];
    $category=$_POST['category'];
    $authorname=$_POST['authorname'];
    $publisher=$_POST['publisher'];
    $totalcopies=$_POST['totalcopies'];
    $availablecopies=$_POST['availablecopies'];
    $bookid=$_GET['bookID'];
    
    $sql="UPDATE tblbooks SET Title=:bookname, CatId=:category, AuthorName=:authorname,
          Publisher=:publisher, TotalCopies=:totalcopies, AvailableCopies=:availablecopies 
          WHERE BookID=:bookid";
          
    $query = $dbh->prepare($sql);
    $query->bindParam(':bookname',$bookname,PDO::PARAM_STR);
    $query->bindParam(':category',$category,PDO::PARAM_STR);
    $query->bindParam(':authorname',$authorname,PDO::PARAM_STR);
    $query->bindParam(':publisher',$publisher,PDO::PARAM_STR);
    $query->bindParam(':totalcopies',$totalcopies,PDO::PARAM_INT);
    $query->bindParam(':availablecopies',$availablecopies,PDO::PARAM_INT);
    $query->bindParam(':bookid',$bookid,PDO::PARAM_STR);
    $query->execute();
    
    $_SESSION['msg']="Book info updated successfully";
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
    <title>Online Library Management System | Edit Book</title>
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
                    <h4 class="header-line">Edit Book</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            Book Info
                        </div>
                        <div class="panel-body">
                            <form role="form" method="post">
                                <?php 
                                $bookid=$_GET['bookID'];
                                $sql = "SELECT tblbooks.BookID, tblbooks.Title, tblbooks.AuthorName, 
                                        tblbooks.Publisher, tblbooks.TotalCopies, tblbooks.AvailableCopies,
                                        tblcategory.CategoryName, tblcategory.id as cid
                                        FROM tblbooks 
                                        JOIN tblcategory ON tblcategory.id=tblbooks.CatId 
                                        WHERE tblbooks.BookID=:bookid";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':bookid',$bookid,PDO::PARAM_STR);
                                $query->execute();
                                $results=$query->fetchAll(PDO::FETCH_OBJ);
                                $cnt=1;
                                if($query->rowCount() > 0)
                                {
                                foreach($results as $result)
                                {               ?>  

                                <div class="form-group">
                                    <label>Book ID</label>
                                    <input class="form-control" type="text" value="<?php echo htmlentities($result->BookID);?>" readonly />
                                </div>

                                <div class="form-group">
                                    <label>Book Title<span style="color:red;">*</span></label>
                                    <input class="form-control" type="text" name="bookname" value="<?php echo htmlentities($result->Title);?>" required />
                                </div>

                                <div class="form-group">
                                    <label>Category<span style="color:red;">*</span></label>
                                    <select class="form-control" name="category" required="required">
                                        <option value="<?php echo htmlentities($result->cid);?>"> <?php echo htmlentities($catname=$result->CategoryName);?></option>
                                        <?php 
                                        $status=1;
                                        $sql1 = "SELECT * from tblcategory where Status=:status";
                                        $query1 = $dbh->prepare($sql1);
                                        $query1->bindParam(':status',$status, PDO::PARAM_STR);
                                        $query1->execute();
                                        $resultss=$query1->fetchAll(PDO::FETCH_OBJ);
                                        if($query1->rowCount() > 0)
                                        {
                                        foreach($resultss as $row)
                                        {           
                                        if($catname==$row->CategoryName)
                                        {
                                        continue;
                                        }
                                        else
                                        {
                                            ?>  
                                        <option value="<?php echo htmlentities($row->id);?>"><?php echo htmlentities($row->CategoryName);?></option>
                                        <?php }}} ?> 
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Author Name<span style="color:red;">*</span></label>
                                    <input class="form-control" type="text" name="authorname" value="<?php echo htmlentities($result->AuthorName);?>" required="required" />
                                </div>
                                
                                <div class="form-group">
                                    <label>Publisher<span style="color:red;">*</span></label>
                                    <input class="form-control" type="text" name="publisher" value="<?php echo htmlentities($result->Publisher);?>" required="required" />
                                </div>

                                <div class="form-group">
                                    <label>Total Copies<span style="color:red;">*</span></label>
                                    <input class="form-control" type="number" name="totalcopies" value="<?php echo htmlentities($result->TotalCopies);?>" required="required" />
                                </div>

                                <div class="form-group">
                                    <label>Available Copies<span style="color:red;">*</span></label>
                                    <input class="form-control" type="number" name="availablecopies" value="<?php echo htmlentities($result->AvailableCopies);?>" required="required" />
                                </div>

                                <?php }} ?>
                                <button type="submit" name="update" class="btn btn-info">Update</button>
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