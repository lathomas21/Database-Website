<?php require_once("query.php") ?>
<html>
  <head>
    <title>Product's R Us</title>
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  </head>
  <body>
    <?php if( isset($_SESSION['user']) ) {
    	echo "<h4>User: " . $_SESSION['user'] . "</h4>";
    	if( $_SESSION['staff']==0){
    	echo "<p><a href=\"/\"><button>Home</button></a></p>";
    	echo "<p><a href=\"information.php\"><button>Account Information</button></a></p>";
    	echo "<p><a href=\"cart.php\"><button>Cart</button></a></p>";}
    	else {	
    	echo "<p><a href=\"staff.php\"><button>Home</button></a></p>";
    	echo "<p><a href=\"information.php\"><button>Account Information</button></a></p>";
    	echo "<p><a href=\"alert.php\"><button>Alerts</button></a></p>";}
    	$str = "<p><a href=\"query.php?signout\"><button>Sign Out</button></a></p>";
    	echo $str;
    } ?>
    <h2>Search</h2>
    <form method="post" action="query.php?search" id="searchform">
      <input type="text" name="name"/>
      <input type="submit" name="submit" value="Search"/>
    </form>
    <h2>
       
      <?php account(); ?>
    </h2>
    <h3>Update Information</h3>
    <form method="post" action="query.php?information" id="searchform">
      <p>Name: </p>
      <input type="text" name="name"/>
      <p>Email: </p>
      <input type="email" name="email"/>
      <p>Password: </p>
      <input type="password" name="password"/>
      <p>Address: </p>
      <input type="text" name="address"/><br/><br/>
      <input type="submit" name="submit" value="Update"/>
    </form>
  </body>
</html>