<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";


//Functions used to correct for faulty database building
//get userid and usernames stored in database
function get_user_id_and_username()
{
    global $link;


    $sqlUsername = "SELECT u.id, u.username
                FROM users u
                ";

    $result = mysqli_query($link, $sqlUsername);

    $userIds = array();
    $usernames = array();

    //stores the userIDs and the usernames of those userIDs into an array
    while ($row = mysqli_fetch_assoc($result)) {
        $userIds[] = $row['id'];
        $usernames[] = $row['username'];
    }
    mysqli_free_result($result);
    $tempArray = array($userIds, $usernames);
    return $tempArray;
}

function get_items_by_users($userIds)
{

    global $link;

    $holder[] = $userIds;
    $userIds = $holder;

    $itemsByUser = [];
    for ($i = 0; $i < count($userIds); $i++) {

        $sqlItems = "SELECT id 
                     FROM items 
                     WHERE user_id = " . $userIds[$i];

        $result = mysqli_query($link, $sqlItems);

        $tempArray = array();
        //all the items that a user_id submitted
        while ($row = mysqli_fetch_assoc($result)) {
            $tempArray[] = $row['id'];
        }

        $itemsByUser[] = $tempArray;

        mysqli_free_result($result);
    }
    return $itemsByUser;
}


function get_user_items_excellent_good_reviews($userId)
{
    global $link;
    //retrieves the user id[0] and username[1] 2D array
    //retrieves user's items mapped by the index of the username[0][$index]
    //to the $userItems[$index]
    $userItems = get_items_by_users($userId)[0];

    $goodExcellentReviews = [];

    foreach ($userItems as $i => $item) {
        $sqlChecker = "SELECT COUNT(item_id) as counted
                       FROM reviews
                       WHERE item_id = " . $item . "
                        ";
        $result = mysqli_query($link, $sqlChecker);

        if ($row = mysqli_fetch_assoc($result)) {
            if ($row['counted'] == 0) {
                continue;
            }
        }

        $sqlGoodExcellent = "SELECT COUNT(item_id) as count, item_id
                             FROM reviews
                             WHERE (review_rating = 'poor' OR review_rating = 'fair') AND item_id = " . $item . "
                                ";


        $result = mysqli_query($link, $sqlGoodExcellent);

        if ($row = mysqli_fetch_assoc($result)) {
            if ($row['count'] == 0) {
                $goodExcellentReviews[] = $item;
            }
        }
    }
    $item_name = [];
    foreach ($goodExcellentReviews as $i => $item) {
        $sqlItemName = "SELECT title
                        FROM items
                        WHERE id = " . $item . "
        ";
        $result = mysqli_query($link, $sqlItemName);
        $row = mysqli_fetch_assoc($result);
        $item_name[] = $row['title'];
    }
    echo '</ul>';
    if (count($goodExcellentReviews) > 0) {
        echo "<table class='table'>";
        echo "<thead><tr><th>User With No Excellent Items</th></tr></thead>";
        echo "<thead><tr><th>USER ITEM</th><th>ITEM NAME</th></tr></thead>";
        echo "<tbody>";
        for ($i = 0; $i < count($goodExcellentReviews); $i++) {
            echo "<tr><td>{$goodExcellentReviews[$i]}</td><td>{$item_name[$i]}</td>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "No Such Criteria Met.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Display Value</title>
    <meta charset="UTF-8">
    <title>Part 3</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font: 14px sans-serif;
        }

        .wrapper {
            width: 800px;
            padding: 20px;
        }
    </style>
</head>

<body>
    <?php
    if (isset($_GET['value'])) {
        $value = $_GET['value'];
        // do something with $value
        echo "<h1>The selected User ID is: " . $value . "</h1>";
    }
    get_user_items_excellent_good_reviews($value);
    ?>
</body>

</html>