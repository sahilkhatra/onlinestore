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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Insert New Item</title>
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
    <script>
        // Implement a way to limit 3 entries per day
        //descritpion
        //cateroies, implement a way to seperate by commas
        //price
        function clear() {
            document.getElementById("title").value = "";
            document.getElementById("description").value = "";
            document.getElementById("categories").value = "";
            document.getElementById("price").value = "";

        }

        function closeWindow() {
            var now = Date.now();
            newWindow = window.open('welcome.php', 'Welcome Page' + now, 'width=500,height=500');
            window.close();
        }
    </script>
</head>

<body>
    <div class="wrapper">
        <h2>Insert New Items</h2>
        <p>Please fill out this form to insert a new item into the database:</p>
        <form action="form_handler.php" method="post" class="formContainer">
            <div class="form-group">
                <label for="title">
                    <strong>Title</strong>
                    <input type="text" id="title" placeholder="Your Title" name="title" required class="form-control">

                </label>
            </div>
            <div class="form-group">
                <label for="description">
                    <strong>Description</strong>
                </label>
                <input type="text" id="description" placeholder="Enter Description" name="description" required class="form-control">

            </div>
            <div class="form-group">
                <label for="catergories">
                    <strong>Categories (Seperated by Commas)</strong>
                </label>
                <input type="text" id="categories" placeholder="Enter Categories" name="categories" required class="form-control">

            </div>
            <div class="form-group">
                <label for="price">
                    <strong>Price</strong>
                </label>
                <input type="number" id="price" placeholder="Enter Price" name="price" required step="0.01" min="0" class="form-control">

            </div>
            <div class="form-group">
                <button type="submit" onclick="clear()" class="btn btn-primary">Submit Item</button>

            </div>
        </form>

    </div>
    <button class="btn btn-primary" onclick="closeWindow()">Return To Main Menu</button>

</body>

</html>