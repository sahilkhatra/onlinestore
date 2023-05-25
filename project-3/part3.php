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





//1. Function to get the most expensive items in each category
function get_most_expensive_items()
{
    global $link;

    $sql = "SELECT categories, MAX(price) AS max_price
            FROM items
            GROUP BY categories";
    $result = mysqli_query($link, $sql);

    if (mysqli_num_rows($result) > 0) {
        echo "<table class='table'>";
        echo "<thead><tr><th>Category</th><th>Max Price</th></tr></thead>";
        echo "<tbody>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr><td>{$row['categories']}</td><td>{$row['max_price']}</td></tr>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "No results found.";
    }
}

//2. Function to get users who posted items with specified categories on the same day
function get_users_same_day_items($category1, $category2)
{
    global $link;

    $sql = "SELECT u.id, u.username
            FROM users u
            JOIN items i1 ON u.id = i1.user_id
            JOIN items i2 ON u.id = i2.user_id
            WHERE i1.categories = ? AND i2.categories = ?
            AND DATE(i1.date) = DATE(i2.date)
            GROUP BY u.id, u.username";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $category1, $category2);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo "<table class='table'>";
        echo "<thead><tr><th>User ID</th><th>Username</th></tr></thead>";
        echo "<tbody>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr><td>{$row['id']}</td><td>{$row['username']}</td></tr>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "No results found.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category1 = trim($_POST["category1"]);
    $category2 = trim($_POST["category2"]);

    get_users_same_day_items($category1, $category2);
}



//List all the items posted by user X, such that all the comments are "Excellent" or "good"
//for these items
//3.
//This needs to be a dropdown menu
function get_items_only_excellent_good_items()
{
    //3. was built part3problem3.php as to not clash with form
}

//List the users who posted the most number of items since 5/1/2020 (inclusive);
//if there's a tie list all the users who have a tie
//4.
function get_users_most_items_since_date($date)
{
    global $link;

    $sql = "SELECT user_id, COUNT(*) AS items_count 
            FROM items 
            WHERE date >= ? 
            GROUP BY user_id 
            HAVING items_count = (
                SELECT MAX(items_count) 
                FROM (
                    SELECT user_id, COUNT(*) AS items_count 
                    FROM items 
                    WHERE date >= ? 
                    GROUP BY user_id
                ) AS subquery
            )";

    try {
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("ss", $param_date, $param_date2);
            $param_date = $date;
            $param_date2 = $date;

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                echo "<table class='table table-striped'>";
                echo "<thead><tr><th>Username</th><th>Items Count</th></tr></thead>";
                echo "<tbody>";

                //have to prepare a second mySQL statement because
                //username is only held in the users table
                $user_id = [];
                $item_count = "";
                while ($row = $result->fetch_assoc()) {
                    $user_id[] = $row["user_id"];
                    $item_count = $row["items_count"];
                }
                $items_to_check = implode(",", $user_id);
                $sqlUsernames = "SELECT username
                                 FROM users
                                 WHERE id IN (" . $items_to_check . ")
                                 ";

                $result = mysqli_query($link, $sqlUsernames);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr><td>" . $row["username"] . " </td><td>" . $item_count . "</td></tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "Error executing the query.";
            }
        }

        $stmt->close();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}



//List the other uses who are favorited by both users x, and y. usernames x and y
//will be selected from dropdown menus by instructor
//in other words, the user (or users) C are the favorite of both x and y
//5.
function get_users_favorited_by_both($user1, $user2)
{
    //impemented in part3problem5.php
    //as to not mess with the form in this page
}

//Display all the users who never posted any "excellent" items
//6. - Very similar to item #9 so I pasted that code here
function get_user_no_excellent_items()
{
    global $link;
    //retrieves the user id[0] and username[1] 2D array
    $userAndName = get_user_id_and_username();
    //retrieves user's items mapped by the index of the username[0][$index]
    //to the $userItems[$index]
    $userItems = get_items_by_users($userAndName[0]);

    $indexOfNoPoors = array();

    foreach ($userItems as $index => $items) {
        $items_to_check = implode(",", $items);
        if (empty($items_to_check)) {
            continue;
        }
        $sqlReviews = "SELECT COUNT(review_rating) as count
        FROM reviews
        WHERE review_rating = 'excellent' AND item_id IN (" . $items_to_check . ")
        ";

        $result = mysqli_query($link, $sqlReviews);

        if ($row = mysqli_fetch_assoc($result)) {
            if ($row['count'] < 3) {
                $indexOfNoPoors[] = $index;
            }
        }
    }

    if (count($indexOfNoPoors) > 0) {
        echo "<table class='table'>";
        echo "<thead><tr><th>User With No Excellent Items</th></tr></thead>";
        echo "<thead><tr><th>User ID</th><th>Username</th></tr></thead>";
        echo "<tbody>";
        for ($i = 0; $i < count($indexOfNoPoors); $i++) {
            echo "<tr><td>{$userAndName[0][$indexOfNoPoors[$i]]}</td><td>{$userAndName[1][$indexOfNoPoors[$i]]}</td>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "No Such Criteria Met.";
    }
}




//Display all users who never posted a "poor" review
//7.
function get_user_never_poor_review()
{
    global $link;
    //retrieves the user id[0] and username[1] 2D array
    $userAndName = get_user_id_and_username();

    $indexOfOnlyPoors = array();

    //Checks to see if all 
    for ($i = 0; $i < count($userAndName[0]); $i++) {
        $sqlReviews = "SELECT user_id, COUNT(review_rating) as count
                       FROM reviews
                       WHERE review_rating <> 'poor' AND user_id = '" . $userAndName[0][$i] . "'
                        ";
        $sqlChecker = "SELECT COUNT(review_rating) as count
                       FROM reviews
                       WHERE user_id = '" . $userAndName[0][$i] . "'
                        ";

        $resultReviews = mysqli_query($link, $sqlReviews);
        $resultChceker = mysqli_query($link, $sqlChecker);

        $rowReviews = mysqli_fetch_assoc($resultReviews);
        $rowChecker = mysqli_fetch_assoc($resultChceker);
        //Remove the count checker if you also want to retrieve people
        //who have never posted a review so they've never posted
        //a poor review
        if ($rowChecker['count'] != 0) {
            if ($rowReviews['count'] == $rowChecker['count']) {
                $indexOfOnlyPoors[] = $i;
            }
        }
    }



    if (count($indexOfOnlyPoors) > 0) {
        echo "<table class='table'>";
        echo "<thead><tr><th>User Who Never Posted a Poor Review</th></tr></thead>";
        echo "<thead><tr><th>User ID</th><th>Username</th></tr></thead>";
        echo "<tbody>";
        for ($i = 0; $i < count($indexOfOnlyPoors); $i++) {
            echo "<tr><td>{$userAndName[0][$indexOfOnlyPoors[$i]]}</td><td>{$userAndName[1][$indexOfOnlyPoors[$i]]}</td>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "No Such Criteria Met.";
    }
}


//Display all the users who posted some reviews, but each of them is "poor"
//8.

function get_user_only_poor_review()
{
    global $link;
    //retrieves the user id[0] and username[1] 2D array
    $userAndName = get_user_id_and_username();

    $indexOfOnlyPoors = array();

    for ($i = 0; $i < count($userAndName[0]); $i++) {
        $sqlReviews = "SELECT user_id, COUNT(review_rating) as count
                       FROM reviews
                       WHERE review_rating = 'poor' AND user_id = '" . $userAndName[0][$i] . "'
                        ";
        $sqlChecker = "SELECT COUNT(review_rating) as count
                       FROM reviews
                       WHERE user_id = '" . $userAndName[0][$i] . "'
                        ";


        $resultReviews = mysqli_query($link, $sqlReviews);
        $resultChceker = mysqli_query($link, $sqlChecker);

        $rowReviews = mysqli_fetch_assoc($resultReviews);
        $rowChecker = mysqli_fetch_assoc($resultChceker);
        if ($rowChecker['count'] != 0) {
            if ($rowReviews['count'] == $rowChecker['count']) {
                $indexOfOnlyPoors[] = $i;
            }
        }
    }



    if (count($indexOfOnlyPoors) > 0) {
        echo "<table class='table'>";
        echo "<thead><tr><th>User Who ONLY Posts Poor Reviews</th></tr></thead>";
        echo "<thead><tr><th>User ID</th><th>Username</th></tr></thead>";
        echo "<tbody>";
        for ($i = 0; $i < count($indexOfOnlyPoors); $i++) {
            echo "<tr><td>{$userAndName[0][$indexOfOnlyPoors[$i]]}</td><td>{$userAndName[1][$indexOfOnlyPoors[$i]]}</td>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "No Such Criteria Met.";
    }
}



//Display those users such that each item they posted so far never received any "poor" reviews.
//9
function get_user_no_poor_review()
{
    global $link;
    //retrieves the user id[0] and username[1] 2D array
    $userAndName = get_user_id_and_username();
    //retrieves user's items mapped by the index of the username[0][$index]
    //to the $userItems[$index]
    $userItems = get_items_by_users($userAndName[0]);

    $indexOfNoPoors = array();

    foreach ($userItems as $index => $items) {
        $items_to_check = implode(",", $items);
        if (empty($items_to_check)) {
            continue;
        }
        $sqlReviews = "SELECT COUNT(review_rating) as count
        FROM reviews
        WHERE review_rating = 'poor' AND item_id IN (" . $items_to_check . ")
        ";

        $result = mysqli_query($link, $sqlReviews);

        if ($row = mysqli_fetch_assoc($result)) {
            if ($row['count'] == 0) {
                $indexOfNoPoors[] = $index;
            }
        }
    }

    if (count($indexOfNoPoors) > 0) {
        echo "<table class='table'>";
        echo "<thead><tr><th>User With No Poor Reviews</th></tr></thead>";
        echo "<thead><tr><th>User ID</th><th>Username</th></tr></thead>";
        echo "<tbody>";
        for ($i = 0; $i < count($indexOfNoPoors); $i++) {
            echo "<tr><td>{$userAndName[0][$indexOfNoPoors[$i]]}</td><td>{$userAndName[1][$indexOfNoPoors[$i]]}</td>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "No Such Criteria Met.";
    }
}
//Function Gets Users Where they only posted positive reviews to Each Other
//10. 
function get_user_pair_excellent()
{
    global $link;

    //retrieve the userids and usernames from the database
    $userIdandUsername = get_user_id_and_username();

    $userIds = $userIdandUsername[0];
    $usernames = $userIdandUsername[1];

    //retrieve the items by userIds from databse
    $itemsByUser = get_items_by_users($userIds);


    $finalPairings = array();

    for ($i = 0; $i < count($userIds); $i++) {
        $excellentUser = array();
        for ($j = 0; $j < count($userIds) - 1; $j++) {

            $index = ($i + $j + 1) % count($userIds);
            //makes a list of all the items user has submitted to use under reviews
            $items_to_check = implode(",", $itemsByUser[$i]);

            if (empty($items_to_check)) {
                continue;
            }

            //Checks to see that all items are reviewed excellent by all other users
            $sqlCompare = "SELECT COUNT(DISTINCT user_id, item_id) AS count, user_id
                           FROM reviews
                           WHERE review_rating = 'excellent' AND item_id IN (" . $items_to_check . ") and user_id = '" . $userIds[$index] . "'";

            //Checks for duplicate reviews of same item and sees if it's anything but excellent
            //Code allows for duplicate reviews, but requirment needs to see if it's anything but excellent
            $sqlContrast = "SELECT COUNT(DISTINCT user_id, item_id) AS count, user_id
                            FROM reviews
                            WHERE review_rating <> 'excellent' AND item_id IN (" . $items_to_check . ") and user_id =" . $userIds[$index];

            $resultExcellent = mysqli_query($link, $sqlCompare);
            $resultFailure = mysqli_query($link, $sqlContrast);

            $excellent = mysqli_fetch_assoc($resultExcellent);
            $failure = mysqli_fetch_assoc($resultFailure);

            if ($excellent['count'] != count($itemsByUser[$i]) || $failure['count'] == 0) {
                $excellentUser[] = $excellent['user_id'];
            }
        }
        $finalPairings[$userIds[$i]] = $excellentUser;
    }
    //the pairs of indexes that matchese all the contraints
    //and finally gives us the users that only give each other
    //excellent reviews
    $index_pairs = array();

    for ($i = 0; $i < count($finalPairings); $i++) {
        for ($j = 0; $j < count($finalPairings[$userIds[$i]]); $j++) {
            for ($k = 0; $k < count($finalPairings[$finalPairings[$userIds[$i]][$j]] ?? []); $k++)
                if ($userIds[$i] == $finalPairings[$finalPairings[$userIds[$i]][$j]][$k]) {
                    $index_pairs[] = array($userIds[$i], $finalPairings[$userIds[$i]][$j]);
                }
        }
    }

    $unique_pairs = [];
    //sorts and removes duplicate values
    foreach ($index_pairs as $value) {
        sort($value);
        if (!in_array($value, $unique_pairs)) {
            $unique_pairs[] = $value;
        }
    }

    $index_pairs = $unique_pairs;
    $username_pairs = [];

    foreach ($index_pairs as $pairs) {
        $indexA = array_search($pairs[0], $userIds);
        $indexB = array_search($pairs[1], $userIds);

        $username_pairs[] = array($usernames[$indexA], $usernames[$indexB]);
    }

    if (count($index_pairs) > 0) {
        echo "<table class='table'>";
        echo "<thead><tr><th>User A</th><th>User B</th></tr></thead>";
        echo "<tbody>";
        for ($i = 0; $i < count($index_pairs); $i++) {
            echo "<tr><td>USER ID: {$index_pairs[$i][0]} <br>USERNAME: {$username_pairs[$i][0]}</td><td> USER ID: {$index_pairs[$i][1]} <br>USERNAME: {$username_pairs[$i][1]}</td></tr>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "No Such Criteria Met.";
    }
}
//end of php
?>

<!DOCTYPE html>
<html lang="en">

<head>
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
    <script type="text/javascript">
        //Implement a way to check 
        function closeWindow() {
            var now = Date.now();
            newWindow = window.open('welcome.php', 'Welcome Page' + now, 'width=500,height=500');
            window.close();
        }

        function focusPopup() {
            if (!newWindow.closed) {
                newWindow.close();
            }
        }

        function openProblem5() {
            window.open("part3problem5.php", "problem5", "width=600,height=400,scrollbars=yes")
        }
    </script>
</head>

<body>
    <div class="wrapper">
        <h2>Part 3: Advanced Search</h2>
        <hr>
        <h3>1. Most Expensive Items in Each Category</h3>
        <?php get_most_expensive_items(); ?>

        <hr>
        <h3>2. Users Who Posted Items with Specified Categories on the Same Day</h3>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="category1">Category 1:</label>
                    <input type="text" id="category1" name="category1" required class="form-control">
                </div>
                <div class="form-group col-md-6">
                    <label for="category2">Category 2:</label>
                    <input type="text" id="category2" name="category2" required class="form-control">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        <br>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            get_users_same_day_items($category1, $category2);
        }
        ?>


        <hr>
        <h3>3. Display All The Items Posted By User X, Such That all Comments are "Excellent" or "Good" For These Items</h3>
        <select name="options" onchange="showPopup(this.value)" ;this.selectedIndex=0>
            <?php
            // PHP code to generate dropdown options dynamically
            $options = get_user_id_and_username();
            $options_user_id = $options[0];
            $options_username = $options[1];
            //default value of "" so that the onchange can work
            echo '<option value="" selected disabled>Please Select</option>';
            foreach ($options_user_id as $index => $option) {
                echo "<option value='" . $option . "'>" . $option . " " . $options_username[$index] .  "</option>";
            }
            ?>
            <script>
                function showPopup(value) {
                    var now = Date.now();

                    window.open("part3problem3.php?value=" + value, "Part 3 Problem 3" + now, "width=600,height=400,scrollbars=yes")
                }
            </script>
        </select>

        <h2>4. Users with most items since 5/1/2020</h2>
        <?php
        $date = "2020-05-01";
        get_users_most_items_since_date($date);
        ?>

        <h2>5. Users favorited by both X and Y</h2>
        <button class="btn btn-primary" onclick="openProblem5()">Open Favorited X and Y Button</button>

        <hr>
        <h3>6. Display All The Users Who Never Posted Excellent Item (Excellent Item = 3+ Excellent Reviews)</h3>
        <?php get_user_no_excellent_items() ?>
        <hr>


        <hr>
        <h3>7. Display Users Who Never Posted A "Poor" Review</h3>
        <?php get_user_never_poor_review() ?>
        <hr>


        <hr>
        <h3>8. Display All The Users Who Posted Some Reviews, But Each of Them is "Poor"</h3>
        <?php get_user_only_poor_review() ?>
        <hr>


        <hr>
        <h3>9. Display Users Such They They Never Recieved Poor Reviews</h3>
        <?php get_user_no_poor_review() ?>
        <hr>


        <h3>10. Pair of Users (A,B) Such That They Always Gave Each Other "excellent" Reviews For Every Single Item They Posted</h3>
        <?php get_user_pair_excellent() ?>
    </div>
    <button class="btn btn-primary" onclick="closeWindow()">Return to Main Menu</button>

</body>

</html>