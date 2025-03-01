<?php
require_once("includes/config.php");

if(!empty($_POST["studentid"])) {
    $studentid = strtoupper($_POST["studentid"]);
    $sql = "SELECT FullName, Status FROM tblstudents WHERE (StudentId=:studentid)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':studentid', $studentid, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    
    if($query->rowCount() > 0) {
        foreach ($results as $result) {
            // Check if student is active
            if($result->Status == 1) {
                echo "<span style='color:green'>Student Found: </span>" . htmlentities($result->FullName);
                echo "<script>$('#submit').prop('disabled',false);</script>";
            } else {
                echo "<span style='color:red'>Student ID Blocked.</span>";
                echo "<script>$('#submit').prop('disabled',true);</script>";
            }
        }
    } else {
        echo "<span style='color:red'>Invalid Student ID. Please try again.</span>";
        echo "<script>$('#submit').prop('disabled',true);</script>";
    }
}
?>