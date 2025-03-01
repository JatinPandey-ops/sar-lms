<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
    {   
header('location:index.php');
}
else{ 

    if(isset($_POST['add']))
    {
        $bookid=$_POST['bookid'];
        $title=$_POST['title'];
        $category=$_POST['category'];
        $author=$_POST['author'];
        $publisher=$_POST['publisher'];
        $copies=$_POST['copies'];
    
        $sql="INSERT INTO tblbooks(BookID,Title,CatId,AuthorName,Publisher,TotalCopies,AvailableCopies) VALUES(:bookid,:title,:category,:author,:publisher,:copies,:copies)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':bookid',$bookid,PDO::PARAM_STR);
        $query->bindParam(':title',$title,PDO::PARAM_STR);
        $query->bindParam(':category',$category,PDO::PARAM_STR);
        $query->bindParam(':author',$author,PDO::PARAM_STR);
        $query->bindParam(':publisher',$publisher,PDO::PARAM_STR);
        $query->bindParam(':copies',$copies,PDO::PARAM_INT);
        
        if($query->execute())
        {
            $_SESSION['msg']="Book Listed successfully";
            header('location:manage-books.php');
            exit();
        }
        else 
        {
            $_SESSION['error']="Something went wrong. Please try again";
            header('location:manage-books.php');
            exit();
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
    <title>Online Library Management System | Add Book</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
</head>
<body>
<?php include('includes/header.php');?>

<div class="content-wrapper">
    <div class="container">
        <div class="row pad-botm">
            <div class="col-md-12">
                <h4 class="header-line">Add Book</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                <div class="panel panel-info">
                    <div class="panel-heading">Book Info</div>
                    <div class="panel-body">
                        <form role="form" method="post">
                            
                            <div class="form-group">
                                <label>Book ID<span style="color:red;">*</span></label>
                                <input class="form-control" type="text" name="bookid" required />
                            </div>

                            <div class="form-group">
                                <label>Title<span style="color:red;">*</span></label>
                                <input class="form-control" type="text" name="title" required />
                            </div>

                            <div class="form-group">
                                <label>Category<span style="color:red;">*</span></label>
                                <select class="form-control" name="category" required>
                                    <option value="">Select Category</option>
                                    <?php 
                                    $status=1;
                                    $sql = "SELECT * FROM tblcategory WHERE Status=:status";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':status', $status, PDO::PARAM_STR);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                    if($query->rowCount() > 0)
                                    {
                                        foreach($results as $result)
                                        { ?>  
                                            <option value="<?php echo htmlentities($result->id);?>">
                                                <?php echo htmlentities($result->CategoryName); ?>
                                            </option>
                                        <?php }} ?> 
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Author Name<span style="color:red;">*</span></label>
                                <input class="form-control" type="text" name="author" required />
                            </div>

                            <div class="form-group">
                                <label>Publisher<span style="color:red;">*</span></label>
                                <input class="form-control" type="text" name="publisher" required />
                            </div>

                            <div class="form-group">
                                <label>Copies Available<span style="color:red;">*</span></label>
                                <input class="form-control" type="number" name="copies" required />
                            </div>

                            <button type="submit" name="add" class="btn btn-info">Add</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php');?>

<script src="assets/js/jquery-1.10.2.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/custom.js"></script>
</body>
</html>
<?php } ?>
