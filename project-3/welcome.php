<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>
<script type="text/javascript">
    var newWindow;

    function openDatabase() {
        var now = Date.now();
        newWindow = window.open('database_info.php', 'Insert Item Form' + now, 'width=500,height=500');
        window.close();
    }

    function openInsertItem() {
        var now = Date.now();

        newWindow = window.open('insert_item.php', 'Insert Item Form' + now, 'width=500,height=500');
        window.close();
    }

    function openReview() {
        var now = Date.now();

        newWindow = window.open('write_review.php', 'Write a Review' + now, 'width=500,height=500');
        window.close();

    }

    function openSearch() {
        var now = Date.now();

        newWindow = window.open('search.php', 'Search Product By Category' + now, 'width=500,height=500');
        window.close();
    }

    function openPart3() {
        var now = Date.now();

        newWindow = window.open('part3.php', 'Part 3 of Project' + now, 'width=2000,height=2000');
        window.close();
    }

    function openFavorites() {
        var now = Date.now();

        newWindow = window.open('favorites.php', 'Select Favorites' + now, 'width=500,height=500');
        window.close();
    }
</script>
<!DOCTYPE html>
<html>

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

        p {
            text-align: center;
        }

        tr {
            text-align: center;
        }

        div {
            padding: 25px;
        }
    </style>
</head>

<body>
    <div>
        <a href="logout.php" class="btn btn-danger ml-3">
            Sign Out of Your Account
        </a>
    </div>
    <button class="btn btn-primary" onclick="openDatabase()">Open Database Button</button>
    <button class="btn btn-primary" onclick="openInsertItem()">Insert Item Button</button>
    <button class="btn btn-primary" onclick="openSearch()">Search Button</button>
    <button class="btn btn-primary" onclick="openPart3()">Open Part 3</button>
    <button class="btn btn-primary" onclick="openFavorites()">Open Favorites</button>

</body>

</html>