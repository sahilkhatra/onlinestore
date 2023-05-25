<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>

    <title>Database</title>
    <link href="styles.css" rel="stylesheet">
    <script defer src="script.js">
    </script>

    <script type="text/javascript">
        function closeWindow() {
            var now = Date.now();
            newWindow = window.open('welcome.php', 'Welcome Page' + now, 'width=500,height=500');
            window.close();
        }
    </script>

</head>

<body>


    <?php

    echo "<table style='border: solid 1px black;'>";
    echo "<tr><th>Title</th><th>Description</th><th>Date</th><th>Price</th><th>Categories</th></tr>";

    class TableRows extends RecursiveIteratorIterator
    {

        function __construct($it)
        {
            parent::__construct($it, self::LEAVES_ONLY);
        }

        function current()
        {
            return "<td style='width: 150px; border: 1px solid black;'>" . parent::current() . "</td>";
        }

        function beginChildren()
        {
            echo "<tr>";
        }

        function endChildren()
        {
            echo "</tr>" . "\n";
        }
    }

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "project440";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare("SELECT title, description, date, price, categories FROM items");
        $stmt->execute();

        // set the resulting array to associative
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);

        foreach (new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k => $v) {
            echo $v;
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;
    echo "</table>";

    ?>
    <button class="btn btn-primary" onclick="closeWindow()">Return To Main Menu</button>



</body>

</html>