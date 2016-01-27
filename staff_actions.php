<?php session_start();

if( isset($_SESSION['user']) ) {
	echo "<h4>User: " . $_SESSION['user'] . "</h4>";
	echo "<p><a href=\"staff.php\"><button>Home</button></a></p>";
	echo "<p><a href=\"information.php\"><button>Account Information</button></a></p>";
	echo "<p><a href=\"alert.php\"><button>Alerts</button></a></p>";
	$str = "<p><a href=\"query.php?signout\"><button>Sign Out</button></a></p>";
	echo $str;

} 


$db = new mysqli("localhost", "root", "root", "website");

if ($db->connect_errno) {
	printf("Connect failed: %sn", $db->connect_error);
	exit();
}

$tables['user'] = ["name","password", "email", "address", "id", "is_Staff"];
$tables['product'] = ["id","name", "price", "stockQuantity","description", "active", "supplierid"];
$tables['orders'] = ["id", "date", "paid", "user_id", "order_id", "product_id", "quantity"];
$tables['order_details'] = ["order_id", "product_id", "quantity"];

$_SESSION['selected_table'];
$selected_table = $_SESSION['selected_table'];

function render_fields($query_result) {
	global $selected_table;
	while( $row = $query_result->fetch_assoc() ) {
		echo "<form method=\"post\" action=\"?update_entry\">";
		$target_id;
		$target_product_id;
		$target_product_id = "";
		foreach($row as $field => $value) {
			if ($field == "alert") {
				continue;
			} else {
				echo $field . ": ";
				if ($field == "id") {
					$target_id = $value;
					echo "<input type=\"hidden\" name=\"{$field}\" value=\"{$value}\">";
				}
				if($field== "product_id") {
					$target_product_id = $value;
					echo "<input type=\"text\" name=\"{$field}\" value=\"{$value}\">";
				}
				else {
					echo "<input type=\"text\" name=\"{$field}\" value=\"{$value}\">";
				}
			}
		}
		echo "<input type=\"submit\" name=\"submit\" value=\"update\"/>";

		if($selected_table == 'orders'){
			echo "<a href=\"staff_actions.php?delete&id={$target_id}&order_id={$target_product_id}\"> (-) </a>";
		}
		else {
			echo "<a href=\"staff_actions.php?delete&id={$target_id}\"> (-) </a>";
		}
		echo "</form>";
	}
}

if(isset($_GET['update_entry'])) {
	if( $_SESSION['selected_table'] == 'orders') {
		$sql_base = "UPDATE Orders JOIN orderDetails";
	}
	else {
		$sql_base = "UPDATE {$selected_table}";
	}
	$prim_key = "id";
	$sql_where = "WHERE {$prim_key} = {$_POST[$prim_key]}";
	$sql_set = "";
	$sql = "";
	$product_id;
	$quanity;

	foreach ($tables[$selected_table] as $field) {
		if(isset($_POST[$field])) {
			$sql_set = " SET {$field} = \"{$_POST[$field]} \" ";
			$sql = $sql_base . $sql_set . $sql_where;
			$db->query($sql);

			if($field=="product_id") {
				$product_id = $_POST[$field];
			}
			if($field == "quantity") {
				$quantity = $_POST[$field];
			}
		}
	}
	if( $_SESSION['selected_table'] == 'orders') {
		$sql="UPDATE Product set stockquantity = (stockquantity - " . $quantity . " where id = " . $product_id;
			$db->query($sql);
		}
		echo "<h3> Information updated.</h3>";
		echo "<br />";
		echo "<a href=\"staff.php\">Select Action</a>";
	}

	if (isset($_GET['delete'])) {
		$order_id;
		$product_id;
		if ($selected_table != "orders") {
			$sql="DELETE FROM {$selected_table} WHERE id = {$_GET['id']}";
			$result = $db->query($sql) or die(mysqli_error($db));
		}
		else {

			$sql="SELECT * from orderDetails WHERE order_id = {$_GET['id']}";
			$result=$db->query($sql);
			$row = $result->fetch_assoc();
			$row_cnt = $result->num_rows;

			if($row_cnt==1) {
				echo "made it here";
				$sql="DELETE FROM OrderDetails WHERE order_id = {$_GET['id']}";
				echo $sql;
				$db->query($sql);
				$sql= "DELETE FROM Orders where id = {$_GET['id']}";
				$db->query($sql);
			}
			else{
				$sql="DELETE FROM OrderDetails WHERE product_id= {$row['product_id']} AND order_id 
				= {$_GET['id']}";
				$db->query($sql);
				echo $sql;
			}
			$sql="UPDATE Product set stockquantity = (stockquantity + " . $row['quantity'] . ") where id = " . $row['product_id'];
			$db->query($sql);
			echo $sql;
		}
	}



// Staff API routes
// Fetch
	if(isset($_GET['fetch'])) {
		$_SESSION['selected_table'] = $_POST['table'];

	// Render Table and Fields
		echo "<a href=\"staff.php\">Select Action</a>";
		echo "<p><a href=\"?render_add\">+ {$_SESSION['selected_table']}</a></p>";

		if($_POST['table']=="user") {
			$sql = "SELECT * FROM User";
			$result = $db->query($sql) or die(mysqli_error($db));
			render_fields($result);
		}
		elseif($_POST['table']=="product") {
			$sql = "SELECT * FROM Product";
			$result = $db->query($sql) or die(mysqli_error($db));
			render_fields($result);
		}
		elseif($_POST['table']=="orders") {
			$sql = "SELECT * FROM orderdetails left join Orders on orderdetails.order_id =orders.id";
			$result = $db->query($sql) or die(mysqli_error($db));
			render_fields($result);
		}
} // END Fetch

elseif(isset($_GET['add'])) {
	$selected_table=$_SESSION['selected_table'];
	$values = "";
	$values_details = "";
	$product_id;
	$quantity;

	foreach ($tables[$selected_table] as $field) {
		if( isset($_POST[$field]) ) {
			if($field == "date") {
				$values .= "CURDATE(), ";
			}
			elseif($field == "id") {
				$values .= "DEFAULT, ";
			}
			elseif($selected_table== "orders") {
				if($field == "order_id") {
					$values = $values;				
				}
				elseif($field =="quantity" ) {
					$values_details .= "'{$_POST[$field]}'" . ", ";
					$quantity = $_POST[$field];

				}
				elseif(($field == "product_id")) {
					$values_details .= "'{$_POST[$field]}'" . ", ";
					$product_id = $_POST[$field];
				}
				else {
					$values .= "'{$_POST[$field]}'" . ", ";
				}
			}
			else{
				$values .= "'{$_POST[$field]}'" . ", ";
			}			
		}
	}

	$clean_values = trim($values, ", '',");
	$clean_values .= "'";
	$clean_values_details = trim($values_details, ", ");
	if($selected_table=="orders") {
		$sql="INSERT INTO Orders VALUES({$clean_values})";
		$result = $db->query($sql);
		$last_id = $db->insert_id;
		$sqli ="INSERT INTO OrderDetails VALUES( " .$last_id . ", " . $clean_values_details . ")";
		$result = $db->query($sqli);
		$sql="UPDATE Product set stockquantity = (stockquantity - " . $quantity . ") where id = " . $product_id;
		$result = $db->query($sql);
	}
	else{
		$sql = "INSERT INTO $selected_table VALUES({$clean_values})";
		$result = $db->query($sql);}
	}

	elseif(isset($_GET['render_add'])) {
		echo "<h2>New {$_SESSION['selected_table']}</h2>";
		echo "<form method=\"post\" action=\"?add\">";

		foreach($tables[$_SESSION['selected_table']] as $field) {
			if ($field == "password")
				echo "<p>{$field}: <input type=\"password\" name=\"{$field}\"></p>";
			elseif ($field == "id") 
				echo "<p><input type=\"hidden\" name=\"{$field}\"></p>";
			elseif($field == "date"){
				echo "<p><input type=\"hidden\" name=\"{$field}\"></p>";
			}
			elseif($field == "order_id"){
				echo "<p><input type=\"hidden\" name=\"{$field}\"></p>";
			}
			else {
				echo "<p>{$field}: <input type=\"text\" name=\"{$field}\"></p>";
			}
		}
		echo "<input type=\"submit\" name=\"submit\">";
		echo "</form>";
	}

	else header("Location: /staff.php");

