<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>
<?php
// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$title = $description = $categories = $price = $str_arr = $sqlCat = $user_id = $item_id = "";
$title_err = $description_err = $categories_err = $price_err = "";

//Validates that user_id hasn't added the cap of 3 items today
$user_id = $_SESSION["id"];
$sql =
    "SELECT COUNT(*) as count_items 
    FROM items 
    WHERE user_id = $user_id AND DATE(date) = CURDATE()";
$result = $link->query($sql);

// Check for errors
if (!$result) {
    die("Query failed: " . $link->error);
}

// Get the count of items from the result set
$row = $result->fetch_assoc();
$count_items = $row["count_items"];

// if $count_items  => 3, don't insert
if ($count_items >= 3) {
    echo "Error Too many inserts";
    echo "<script type='text/javascript'>
            alert('Error! Cant Insert More Than 3 Items a Day');
            var now = Date.now();
            newWindow = window.open('welcome.php', 'Insert Item Form' + now,'width=500,height=500');            
            window.close(); 
            </script>";
    exit();
}
// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //Validate Title
    if (empty(trim($_POST["title"]))) {
        $discription_err = "Please enter your title.";
    } elseif (!preg_match('/^[\.a-zA-Z0-9,!? ]*$/', trim($_POST["title"]))) {
        $discription_err = "title can contain letters, numbers, and underscores.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM items WHERE title = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_title);

            // Set parameters
            $param_title = trim($_POST["title"]);

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                /* store result */
                mysqli_stmt_store_result($stmt);


                $title = trim($_POST["title"]);
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    //Validate Description
    if (empty(trim($_POST["description"]))) {
        $discription_err = "Please enter your Description.";
    } elseif (!preg_match('/^[\.a-zA-Z0-9,!? ]*$/', trim($_POST["description"]))) {
        $discription_err = "Description can contain letters, numbers, and underscores.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM items WHERE description = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_description);

            // Set parameters
            $param_description = trim($_POST["description"]);

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                /* store result */
                mysqli_stmt_store_result($stmt);


                $description = trim($_POST["description"]);
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    //Validate Categories
    if (empty(trim($_POST["categories"]))) {
        $discription_err = "Please enter your categories.";
    } elseif (!preg_match('/^[\.a-zA-Z0-9,!? ]*$/', trim($_POST["categories"]))) {
        $discription_err = "categories can contain letters, numbers, and underscores.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM items WHERE categories = ?";


        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_categories);

            // Set parameters
            $param_categories = trim($_POST["categories"]);

            //seperate the categories available

            $str_arr = explode(",", $param_categories);

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                /* store result */
                mysqli_stmt_store_result($stmt);


                $categories = trim($_POST["categories"]);
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    //Validate Price
    if (empty(trim($_POST["price"]))) {
        $price_err = "Please enter a price.";
    } elseif (!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', trim($_POST["price"]))) {
        $price_err = "Price can only include valid price numbers.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM items WHERE price = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "d", $param_price);

            // Set parameters
            $param_price = trim($_POST["price"]);

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                /* store result */
                mysqli_stmt_store_result($stmt);


                $price = trim($_POST["price"]);
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // // Check input errors before inserting in database
    if (empty($title_err) && empty($description_err) && empty($categories_err) && empty($price_err)) {

        // Prepare an insert statement
        $sql = 'INSERT INTO items (user_id, title, description, categories, price) VALUES (?, ?, ?, ?, ?)';
        $sqlCat = 'INSERT INTO categories (user_id, item_id, category) VALUES (?, ?, ?)';

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "isssd", $param_user_id, $param_title, $param_description, $param_categories, $param_price);

            // Set parameters
            $param_user_id = $user_id;
            $param_title = $title;
            $param_description = $description;
            $param_categories = $categories;
            $param_price = $price;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                $result = $link->query("SELECT id FROM items WHERE user_id = $user_id ORDER BY date DESC LIMIT 1");

                // Check for errors
                if (!$result) {
                    echo "Failed to execute query: " . $mysqli->error;
                    exit();
                }

                // Check if there are any rows returned
                if ($result->num_rows > 0) {
                    // Get the last item ID as an integer
                    $item_id = (int) $result->fetch_assoc()["id"];
                } else {
                    // No items found for the user
                    $item_id = null;
                }
                if ($stmt = mysqli_prepare($link, $sql)) {
                    //allows you to insert each item by idividual category into a seperate database
                    //to search
                    foreach ($str_arr as $category) {
                        $query =
                            "INSERT INTO categories (user_id, item_id, category) 
                                VALUES ($user_id, $item_id, '$category')";

                        //Some error below
                        if ($link->query($query)) {
                            echo "New category added successfully!";
                        } else {
                            echo "Error adding category: " . $link->error;
                        }
                    }
                    // Redirect to login page

                    echo
                    " <script language='javascript'>
                            var now = Date.now();
                            newWindow = window.open('welcome.php', 'Insert Item Form' + now,'width=500,height=500');
                            window.close();
                            document.onmousedown = focusPopup;
                            function focusPopup() {
                                if (!newWindow.closed) {
                                newWindow.close();
                                }
                            }
                        </script>";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // Close connection
    mysqli_close($link);
}
