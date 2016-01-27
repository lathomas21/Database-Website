<?php session_start(); 


//connect  to the database
$db = new mysqli("localhost", "root", "root", "website");

if ($db->connect_errno) {
    printf("Connect failed: %s\n", $db->connect_error);
    exit();
}

class CartItem {
    public $product_id;
    public $quantity;
}

// Cart API: empty cart
if (isset($_GET['empty_cart'])) {
    $_SESSION['cart'] = array();
}

// Cart API: checkout
if (isset($_GET['checkout'])) {
    $sql= "INSERT INTO Orders VALUES(DEFAULT, CURDATE(), 0, ". $_SESSION['id'] . ")";
    $db->query($sql);
    $last_id = $db->insert_id;

    foreach($_SESSION['cart'] as $entry) {
        $sqlo ="UPDATE Product SET stockQuantity = (stockQuantity -" . $entry->quantity . ") 
        WHERE id =" . $entry->product_id; 
        $sqli = "INSERT INTO OrderDetails VALUES(" . $last_id . " ," . $entry->product_id . " , "
        . $entry->quantity . ")";
        $db->query($sqli);
        $db->query($sqlo);
    }

    //not working
    header("Location: /cart.php?empty_cart");
}

// Cart API: form processing
if (isset($_POST['submit'])) {

    // Add to Cart
    if(isset($_GET['add_to_cart'])) {
        $product_id = $_POST['cart_item'];

        if(empty($_SESSION['cart'])) {
            $_SESSION['cart'] = array();

            $item = new CartItem;
            $item->product_id = $product_id;
            $item->quantity = 1;
            array_push($_SESSION['cart'], $item);

        } else {
            foreach ($_SESSION['cart'] as $item) {
                if($item->product_id == $product_id) {
                    $item->quantity++;
                } else {
                    $item = new CartItem;
                    $item->product_id = $product_id;
                    $item->quantity = 1;
                    array_push($_SESSION['cart'], $item);
                }
            }
        }
    } // END add to cart
} // END Cart API: form processing


// Show Cart
if( empty($_SESSION['cart']) ) {
    echo "<p>Your cart is empty</p>";
    echo "<a href=\"index.php\">Search for products</a>";
} else {
    echo "<h4>User: " . $_SESSION['user'] . "</h4>";
    echo "<p><a href=\"/\"><button>Home</button></a></p>";
    echo "<p><a href=\"information.php\"><button>Account Information</button></a></p>";
    echo "<p><a href=\"cart.php\"><button>Cart</button></a></p>";
    $str = "<p><a href=\"query.php?signout\"><button>Sign Out</button></a></p>";
    echo $str;
    echo "<h2> Cart Items </h2>";
    $total_price= 0;
    foreach($_SESSION['cart'] as $entry) {
        $sql = "SELECT name, price FROM Product WHERE id= $entry->product_id";
        $result = $db->query($sql);
        $row = $result->fetch_assoc();
        echo "Name: " . $row['name']; 
        echo " Price: $" .$row['price'];
        echo " Quantity: " . $entry->quantity;
        $total_price += ($row['price'] * $entry->quantity);
        echo "<br />";
    }
    echo "<br />";
    echo "Total: $" . $total_price;

    echo "<p><a href=\"cart.php?checkout\"><button>Checkout and Pay</button>";
    echo "<a href=\"?empty_cart\"><button>Cancel Order</button></a></p>";
}

?>
