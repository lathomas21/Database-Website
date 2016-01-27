<?php session_start();

//connect  to the database
$db = new mysqli("localhost", "root", "root", "website");

if ($db->connect_errno) {
  printf("Connect failed: %sn", $db->connect_error);
  exit();
}

if(isset($_POST['submit'])) {

  // Process search form
  if(isset($_GET['search'])){

    if(preg_match("/^[  a-zA-Z]+/", $_POST['name'])){
      $name=$_POST['name'];

      $sql = "SELECT name, price, description, id FROM Product WHERE name LIKE '%" . $name . "%' OR description LIKE '%" . $name ."%'";
      $result = $db->query($sql) or die(mysqli_error($db));

      if( isset($_SESSION['user']) ) {
        echo "<h4>User: " . $_SESSION['user'] . "</h4>";
    	echo "<p><a href=\"/\"><button>Home</button></a></p>";
    	echo "<p><a href=\"information.php\"><button>Account Information</button></a></p>";
    	echo "<p><a href=\"cart.php\"><button>Cart</button></a></p>";
    	$str = "<p><a href=\"query.php?signout\"><button>Sign Out</button></a></p>";
    	echo $str;
      }
      else {
        echo "<p><a href=\"/\"><button>Home</button></a></p>";
      }

      echo "<h1> Results </h1>"; 

      echo "<form method=\"post\" action=\"cart.php?add_to_cart\">";
      while( $row = $result->fetch_assoc() ) {
          $ret_name = $row['name'];
          $ret_price = $row['price'];
          $ret_id = $row['id'];
          $ret_description = $row['description'];


        if( isset($_SESSION["user"])) {
          //-display the result of the array
          echo "<ul>\n";
          echo "<input type=\"radio\" name=\"cart_item\" value=\" " .$ret_id." \"> Name: " . $ret_name . " Description: " . $ret_description. " Price: $ " . $ret_price. " </input> <input type=\"submit\" name=\"submit\" value=\"Add to Cart\"></input>\n";
          echo "</ul>";
        }

        else{
          // display the result of the array
          echo "<ul>\n";
          echo "<h4><li>" . "Name: " . $ret_name . " Description: " . $ret_description. " Price: $" . $ret_price. "</li></h4>";
          echo "</ul>";
        }
            } // END render loop     
            echo "</form>";
        } // END render results

        else{
          echo "<p>Please enter a search query</p>";
        }
    } // End process search form

  // Process login form
  elseif ( isset($_GET['login']) ) {

    if( isset($_POST['email']) && isset($_POST['password']) ) {
      $email = $_POST['email'];
      $password = $_POST['password'];

      $sql = "SELECT name, is_Staff, id FROM User WHERE email = '" . $email . "' AND password = '" . $password . "'";
      $result = $db->query($sql) or die(mysqli_error($db));
      $row = $result->fetch_assoc();

      if( !is_null($row) ) {
        $_SESSION["user"] = $row['name'];
        $_SESSION["staff"] = $row['is_Staff'];
        $_SESSION["id"] = $row['id'];

        if( $_SESSION['staff'] == 1 ) {
          header('Location: staff.php');
        }
        else {
          header('Location: logged-in.php');
        }

        echo "User: " . $_SESSION["user"];
        echo "Staff: " . $_SESSION["staff"];
      } else echo  "<p>Please enter a valid email and password</p>";  
    }
  } // End process login form

  elseif ( isset($_GET['create']) ) {

    if( isset($_POST['name']) && isset($_POST['password']) && isset($_POST['email'])&& isset($_POST['address']) ) {
      $name = $_POST['name'];
      $email = $_POST['email'];
      $password = $_POST['password'];
      $address = $_POST['address'];


      $sql = "INSERT INTO User (name, password, email, address, id, is_Staff) VALUES ('".$name."', '".$password."', '".$email."', '".$address."', DEFAULT, 0)";

      $result = $db->query($sql);
      
      // if INSERT failed
      if(!$result) {
        header("Location: create.php");
        echo "There was an error, please enter your information again";
      } else {
        $sql = "SELECT name, is_Staff, id FROM User WHERE email = '" . $email . "' AND password = '" . $password . "'";
        $result = $db->query($sql) or die(mysqli_error($db));
        $row = $result->fetch_assoc();

        $_SESSION["user"] = $row['name'];
        $_SESSION["staff"] = $row['is_Staff'];
        $_SESSION["id"] = $row['id'];

        header("Location: information.php");
      }
    }
  } // End process create form

  elseif ( isset($_GET['information']) ) {
    echo "made it";
    if( $_POST['name'] != "" ) {
      $name = $_POST['name'];
      $sql = "UPDATE User SET name = '" . $name . "' WHERE id = " . $_SESSION['id'];
      $result = $db->query($sql) or die(mysqli_error($db));
    }

    if( $_POST['password'] != "" ) {
      $password = $_POST['password'];
      $sql = "UPDATE User SET password = '". $password ."' WHERE id = " . $_SESSION['id'];
      $result = $db->query($sql) or die(mysqli_error($db));
    }

    if( $_POST['address'] != "" ) {
      $address = $_POST['address'];
      $sql = "UPDATE User SET address = '". $address ."' WHERE id = " . $_SESSION['id'];
      $result = $db->query($sql) or die(mysqli_error($db));
    }

    $sql = "SELECT name, password, address FROM User WHERE id = " . $_SESSION['id'];
    $result = $db->query($sql) or die(mysqli_error($db));
    $row = $result->fetch_assoc();

    $_SESSION["user"] = $row['name'];

    header('Location: information.php');
  }

  elseif ( isset($_GET['staff_add'])) {
    if( $_POST['Action'] == "Product") {
      echo "good";
      echo "\
      <form method=\"post\" action=\"query.php?search\" id=\"searchform\">
        <input type=\"text\" name=\"name\">
        <input type=\"submit\" name=\"submit\" value=\"Search\">
      </form>";
    }
    if( $_POST['Action'] == "User") {
      echo "good also";
    }

    if( $_POST['Action'] == "Order") {
      echo "hell yea";
    }
  }
} // END form processing



// User signout
if ( isset($_GET['signout']) ) {
  session_unset();
  header('Location: /');
}

function account () {
  global $db;

  $sql = "SELECT name, password, address FROM User WHERE id = " . $_SESSION['id'];
  $result = $db->query($sql) or die(mysqli_error($db));
  $row = $result->fetch_assoc();

  echo "<h3><p>Current information:</h3></p>";
  echo "<p>Name: " . $row['name'] . "</p>";
  echo "<p>Password: " . $row['password'] . "</p>";
  echo "<p>Address: " . $row['address'] . "</p>";
}