<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

//due to the way the databas was built this function will allow 
//us to get a username/id easier
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

function get_users_favorited_by_both($user1, $user2)
{
    global $link;

    echo $user1;
    echo $user2;
    $sql = "SELECT DISTINCT username 
            FROM favorites 
            WHERE username IN (
                SELECT username 
                FROM favorites 
                WHERE favorited_by = ?
            ) AND favorited_by = ?";

    try {
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("ss", $param_user1, $param_user2);
            $param_user1 = $user1;
            $param_user2 = $user2;

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                echo "<table class='table table-striped'>";
                echo "<thead><tr><th>Username</th></tr></thead>";
                echo "<tbody>";
                if (mysqli_num_rows($result) == 0) {
                    echo "<tr><td> No Data on That Exists in Favorites Databse </td></tr>";
                }
                while ($row = $result->fetch_assoc()) {
                    echo "<tr><td>" . $row["username"] . "</td></tr>";
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

?>

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
    <div class="container">
        <h2>Users favorited by both X and Y</h2>
        <form action="part3problem5.php" method="post">
            <div class="form-group">
                <label for="user1">User X:</label>
                <select name="user1" id="user1" class="form-control" required>
                    <!-- Populate the dropdown menu with user options from the database -->
                    <?php
                    // PHP code to generate dropdown options dynamically
                    $options = get_user_id_and_username();
                    $options_user_id = $options[0];
                    $options_username = $options[1];
                    //default value of "" so that the onchange can work
                    echo '<option value="" selected disabled>Please Select</option>';
                    foreach ($options_user_id as $index => $option) {
                        echo "<option value='" . $options_username[$index] . "'>" . $option . " " . $options_username[$index] .  "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="user2">User Y:</label>
                <select name="user2" id="user2" class="form-control" required>
                    <!-- Populate the dropdown menu with user options from the database -->]
                    <?php
                    // PHP code to generate dropdown options dynamically
                    $options = get_user_id_and_username();
                    $options_user_id = $options[0];
                    $options_username = $options[1];
                    //default value of "" so that the onchange can work
                    echo '<option value="" selected disabled>Please Select</option>';
                    foreach ($options_user_id as $index => $option) {
                        echo "<option value='" . $options_username[$index] . "'>" . $option . " " . $options_username[$index] .  "</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="submit_favorite_users" class="btn btn-primary">Search</button>
        </form>
        <?php
        if (isset($_POST['submit_favorite_users'])) {
            $user1 = $_POST['user1'];
            echo $user1;
            $user2 = $_POST['user2'];
            echo $user2;
            get_users_favorited_by_both($user1, $user2);
        }
        ?>
    </div>


</body>

</html>