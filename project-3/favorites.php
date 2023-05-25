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

function get_curr_user_id()
{
    return $_SESSION['id'];
}

function set_favorites($user_id, $selected_option)
{
    global $link;

    $id_and_username = get_user_id_and_username();

    $user_id_array = $id_and_username[0];
    $username_array = $id_and_username[1];

    $favorited_by = $username_array[array_search($user_id, array_values($user_id_array))];
    $username = $username_array[array_search($selected_option, array_values($user_id_array))];


    $sqlSelectFavorites = "INSERT INTO favorites (`username`, `favorited_by`) 
                           SELECT '" . $username . "', '" . $favorited_by . "' 
                           FROM DUAL
                           WHERE NOT EXISTS (SELECT * FROM favorites
                           WHERE username = '" . $username . "'
                           AND favorited_by = '" . $favorited_by . "' LIMIT 1);
                           ";
    $result = mysqli_query($link, $sqlSelectFavorites);

    echo $username . " is Part of Your Favorites";
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
    <script>
        function closeWindow() {
            var now = Date.now();
            newWindow = window.open('welcome.php', 'Welcome Page' + now, 'width=500,height=500');
            window.close();
        }
    </script>
</head>

<body>
    <h3>Select Which User to Favorite!</h3>
    <div id="selectFavorite" sytle="display:block">
        <form method="post">
            <select name="options" id="options" ;this.selectedIndex=0>
                <?php
                // PHP code to generate dropdown options dynamically
                $options = get_user_id_and_username();
                $user_id = get_curr_user_id();
                $options_user_id = $options[0];
                $options_username = $options[1];
                //default value of "" so that the onchange can work
                echo '<option value="" selected disabled>Please Select</option>';
                foreach ($options_user_id as $index => $option) {
                    if ($option != $user_id) {
                        echo "<option value='" . $option . "'>" . $option . " " . $options_username[$index] .  "</option>";
                    } else {
                        echo "<option value='" . $option . "' selected disabled>" . $option . " " . $options_username[$index] .  "</option>";
                    }
                }
                ?>
                <input type="submit" value="Submit">
            </select>
        </form>
    </div>
    <div id="favoriteSelected" style="display:block">
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $selected_option = $_POST["options"];
            $user_id = get_curr_user_id();
            set_favorites($user_id, $selected_option);
        }
        ?>
    </div>
    <button class="btn btn-primary" onclick="closeWindow()">Return to Main Menu</button>


</body>

</html>