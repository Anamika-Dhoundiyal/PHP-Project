<!--
author: W3layouts
author URL: http://w3layouts.com
License: Creative Commons Attribution 3.0 Unported
License URL: http://creativecommons.org/licenses/by/3.0/
-->
<!DOCTYPE html>
<html>
<head>
<title>Grocery Store a Ecommerce Online Shopping Category Flat Bootstrap Responsive Website Template | Products :: w3layouts</title>
<!-- for-mobile-apps -->
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="Grocery Store Responsive web template, Bootstrap Web Templates, Flat Web Templates, Android Compatible web template, 
Smartphone Compatible web template, free webdesigns for Nokia, Samsung, LG, SonyEricsson, Motorola web design" />
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false);
      function hideURLbar(){ window.scrollTo(0,1); } </script>
<!-- //for-mobile-apps -->
<link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
<link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
<!-- font-awesome icons -->
<link href="css/font-awesome.css" rel="stylesheet" type="text/css" media="all" /> 
<!-- //font-awesome icons -->
<!-- js -->
<script src="js/jquery-1.11.1.min.js"></script>
<!-- //js -->
<link href='//fonts.googleapis.com/css?family=Ubuntu:400,300,300italic,400italic,500,500italic,700,700italic' rel='stylesheet' type='text/css'>
<link href='//fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600,600italic,700,700italic,800,800italic' rel='stylesheet' type='text/css'>
<!-- start-smoth-scrolling -->
<script type="text/javascript" src="js/move-top.js"></script>
<script type="text/javascript" src="js/easing.js"></script>
<script type="text/javascript">
   jQuery(document).ready(function($) {
      $(".scroll").click(function(event){    
         event.preventDefault();
         $('html,body').animate({scrollTop:$(this.hash).offset().top},1000);
      });
   });
</script>
<!-- start-smoth-scrolling -->
</head>
   
<body>
<?php include 'partials/navbar_simple.php'; ?>
<!-- products-breadcrumb -->
   <div class="products-breadcrumb">
      <div class="container">
         <ul>
            <li><i class="fa fa-home" aria-hidden="true"></i><a href="manager_logged.php">Home</a><span>|</span></li>
            <li>Transaction History</li>
         </ul>
      </div>
   </div>
   <?php
   include 'dbconnection.php';
   $query = mysqli_query($connect,"SELECT * FROM purchase");
   
   $Cart_total=0;
      echo "<center>";
      ?><table cellpadding="45" cellspacing="45">
         <?php
      echo "<tr>";
      echo "<th>"; echo "Customer ID"; echo "</th>";
      echo "<th>"; echo "Product ID"; echo "</th>";
      echo "<th>"; echo "No of items"; echo "</th>";
      echo "<th>"; echo "cost of items"; echo "</th>";
      echo "<th>"; echo "Date of purchase"; echo "</th>";
      echo "</tr>";
 while ($row = mysqli_fetch_array ($query)) {
      echo "<tr>";
      echo "<td>"; echo $row["pcid"]; echo "</td>";
      echo "<td>"; echo $row["ppid"]; echo "</td>";
      echo "<td>"; echo $row["no_of_items"]; echo "</td>";
      echo "<td>"; echo $row["cost_of_items"]; echo "</td>";
      echo "<td>"; echo $row["date_time"]; echo "</td>";
      echo "</tr>";
  }
  echo "<tr>";
  
  echo "</table>";
  echo "</center>";

?>
<!-- //products-breadcrumb -->
<!-- banner -->
</body>
<?php include 'partials/footer.php'; ?>
</html>
