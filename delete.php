<?php 
session_start();
 require('dbconnect.php');

 if (isset($_SESSION['member_id'])) {
     $id = $_GET['id'];
 
     $sql = sprintf('SELECT * FROM `tweets` WHERE `tweet_id`=%d',
 	     mysqli_real_escape_string($db,$id)
 	     );
     $record = mysqli_query($db,$sql) or die(mysql_error());
     $table = mysqli_fetch_assoc($record);
 
         if ($table['member_id']==$_SESSION['member_id']) {
             $sql = sprintf('DELETE FROM `tweets` WHERE `tweet_id`=%d',
     	          mysqli_real_escape_string($db,$id));
             $record = mysqli_query($db,$sql) or die(mysql_error());
         }
 }
 header('Location: index.php');
 exit();
?>