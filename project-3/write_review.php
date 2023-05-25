<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Write Review</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font: 14px sans-serif;
        }

        .wrapper {
            width: 360px;
            padding: 20px;
        }
    </style>
    <script type="text/javascript">
        function closeWindow() {
            var now = Date.now();
            newWindow = window.open('welcome.php', 'Welcome Page' + now, 'width=500,height=500');
            window.close();
        }
    </script>
</head>

<body>
    <div class="wrapper">
        <h2>Write Review</h2>
        <p>Please fill out this form to write a review:</p>
        <?php
        if (!empty($error)) {
            echo '<div class="alert alert-danger">' . $error . '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <input id="item_id" type="hidden" name="item_id" value="" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Rating</label>
                <select name="rating" class="form-control">
                    <option value="excellent">Excellent</option>
                    <option value="good">Good</option>
                    <option value="fair">Fair</option>
                    <option value="poor">Poor</option>
                </select>
            </div>
            <div class="form-group">
                <label>Review</label>
                <textarea name="review" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
            </div>
        </form>
    </div>
    <button class="btn btn-primary" onclick="closeWindow()">Return To Main Menu</button>

</body>

</html>
<?php

// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

$error = "";
$item_id = "";
if (isset($_GET['value'])) {
    $item_id = $_GET['value'];
}
echo "<script type='text/javascript'>
document.getElementById('item_id').value =" . $item_id . "; 
</script>";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["rating"]) && isset($_POST["review"]) && isset($_POST["item_id"])) {
        $rating = trim($_POST["rating"]);
        $review = trim($_POST["review"]);
        $item_id = trim($_POST["item_id"]);
        $user_id = $_SESSION["id"];

        //mySQL check if the user has already put in 3 reviews for the day
        $sql =
            "SELECT user_id 
            FROM items
            WHERE id = $item_id";
        $result = $link->query($sql);

        // Check for errors
        if (!$result) {
            die("Query failed: " . $link->error);
        }

        // Get the count of items from the result set
        $row = $result->fetch_assoc();
        $user_id_check = $row["user_id"];

        // if $count_items  => 3, don't insert
        if ($user_id_check == $user_id) {
            echo "<script type='text/javascript'>
            alert('Error! Cant Review Your Own Items');
            var now = Date.now();
            newWindow = window.open('welcome.php', 'Insert Item Form' + now,'width=500,height=500');            
            window.close(); 
            </script>";
            exit();
        }

        //mySQL check if the user has already put in 3 reviews for the day
        $user_id = $_SESSION["id"];
        $sql =
            "SELECT COUNT(*) as count_items 
                FROM reviews 
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
            echo "<script type='text/javascript'>
            alert('Error! Cant Review More Than 3 Items A Day');
            var now = Date.now();
            newWindow = window.open('welcome.php', 'Insert Item Form' + now,'width=500,height=500');
            window.close(); 
            </script>";
            exit();
        }

        $sql = "INSERT INTO reviews (user_id, item_id, review_rating, review_description) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iiss", $param_user_id, $param_item_id, $param_rating, $param_review);
            $param_user_id = $user_id;
            $param_item_id = $item_id;
            $param_rating = $rating;
            $param_review = $review;

            if (mysqli_stmt_execute($stmt)) {
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
            } else {
                $error = "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}
?>