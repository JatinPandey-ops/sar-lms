<?php
require_once("includes/config.php");

// Search by BookID
if(!empty($_POST["bookid"])) {
    $bookid = $_POST["bookid"];
    $sql = "SELECT Title, BookID, AvailableCopies FROM tblbooks WHERE (BookID=:bookid)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':bookid', $bookid, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    
    if($query->rowCount() > 0) {
        foreach ($results as $result) {
            if($result->AvailableCopies > 0) {
                ?>
                <option value="<?php echo htmlentities($result->BookID); ?>"><?php echo htmlentities($result->Title); ?></option>
                <b>Book Title:</b> <?php echo htmlentities($result->Title); ?><br />
                <b>Available Copies:</b> <?php echo htmlentities($result->AvailableCopies); ?>
                <?php
                echo "<script>$('#submit').prop('disabled',false);</script>";
            } else {
                echo "<option value=''>Book not available for issue</option>";
                echo "<b>Book Title:</b> " . htmlentities($result->Title) . " (Not Available)";
                echo "<script>$('#submit').prop('disabled',true);</script>";
            }
        }
    } else {
        ?>
        <option class="others">Invalid Book ID</option>
        <?php
        echo "<script>$('#submit').prop('disabled',true);</script>";
    }
}

// Search by Book Title
if(!empty($_POST["booktitle"])) {
    $booktitle = $_POST["booktitle"];
    $sql = "SELECT Title, BookID, AvailableCopies FROM tblbooks WHERE (Title LIKE :booktitle)";
    $query = $dbh->prepare($sql);
    $booktitle = "%$booktitle%"; // Add wildcards for partial match
    $query->bindParam(':booktitle', $booktitle, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    
    if($query->rowCount() > 0) {
        foreach ($results as $result) {
            if($result->AvailableCopies > 0) {
                ?>
                <option value="<?php echo htmlentities($result->BookID); ?>"><?php echo htmlentities($result->Title); ?></option>
                <?php
            }
        }
        echo "<script>$('#submit').prop('disabled',false);</script>";
    } else {
        ?>
        <option class="others">No books found with that title</option>
        <?php
        echo "<script>$('#submit').prop('disabled',true);</script>";
    }
}
?>