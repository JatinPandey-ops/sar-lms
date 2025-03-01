<?php 
require_once("includes/config.php");
// Code for checking Student ID availability
if(!empty($_POST["studentid"])) {
    $studentid = $_POST["studentid"];
    $sql = "SELECT StudentId FROM tblstudents WHERE StudentId=:studentid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':studentid', $studentid, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    
    if($query->rowCount() > 0) {
        echo "<span style='color:red'> Student ID already exists. Try another ID.</span>";
        echo "<script>$('#submit').prop('disabled',true);</script>";
    } else {
        echo "<span style='color:green'> Student ID available for Registration.</span>";
        echo "<script>$('#submit').prop('disabled',false);</script>";
    }
}
?>