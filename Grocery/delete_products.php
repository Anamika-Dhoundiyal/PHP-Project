<?php


if(isset($_POST["submit"])){
        $cpid= $_POST['cpid'];
        include 'db_connection.php';
        echo $cpid;
        $result1 = mysqli_query($connect, "delete FROM PRODUCTS where ID=$cpid");
           // header('Location: Admin_logged.php');
}
?>

