<?php session_start();

$db = new mysqli("localhost", "root", "root", "website");

if ($db->connect_errno) {
	printf("Connect failed: %sn", $db->connect_error);
	exit();
}

if( isset($_SESSION['user']) ) {
	echo "<h4>User: " . $_SESSION['user'] . "</h4>";
	echo "<p><a href=\"staff.php\"><button>Home</button></a></p>";
	echo "<p><a href=\"information.php\"><button>Account Information</button></a></p>";
	echo "<p><a href=\"alert.php\"><button>Alerts</button></a></p>";
	$str = "<p><a href=\"query.php?signout\"><button>Sign Out</button></a></p>";
	echo $str;
} 

$sql= "SELECT * FROM Product WHERE alert =1";
$result=$db->query($sql);
echo "<h2> Alerts </h2>";
while($row = $result->fetch_assoc()) {
	$prod_name=$row['name'];
	$prod_quantity=$row['stockQuantity'];
	echo $prod_name . " only has " . $prod_quantity . " left in stock. Contact the supplier for more.";
	echo "<br />";
}