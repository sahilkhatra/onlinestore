<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";

//start of the implementation for exact categories, for example
//the following code could possible return the category of 10 and 1 as the same
//category
// $search_results = [];
// if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty(trim($_POST["category"]))) {
//     $category = trim($_POST["category"]);
//     $sql = "SELECT user_id, item_id, category FROM categories WHERE category LIKE $category";
//     $search_result = $link->query($sql);
//     if (!$search_result) {
//         die("Query fialed: " . $link->error);
//     }
// }

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty(trim($_POST["category"]))) {
    $category = trim($_POST["category"]);
    $sql = "SELECT id, title, description, price, categories FROM items WHERE categories LIKE ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $param_category);
        $param_category = '%' . $category . '%';
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $search_results[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Search</title>
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
        //Implement a way to check 
        function closeWindow() {
            var now = Date.now();
            newWindow = window.open('welcome.php', 'Welcome Page' + now, 'width=500,height=500');
            window.close();
        }

        function review(value) {
            var val = value;
            window.location.href = "write_review.php?value=" + encodeURIComponent(val);
            document.onmousedown = focusPopup;
        }

        function focusPopup() {
            if (!newWindow.closed) {
                newWindow.close();
            }
        }
    </script>
</head>

<body>
    <div class="wrapper">
        <h2>Search</h2>
        <p>Search for items by category:</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" class="form-control" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Search">
            </div>
        </form>

        <?php if (!empty($search_results)) : ?>
            <h3>Search Results:</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Categories</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($search_results as $result) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result["title"]); ?></td>
                            <td><?php echo htmlspecialchars($result["description"]); ?></td>
                            <td><?php echo htmlspecialchars($result["price"]); ?></td>
                            <td><?php echo htmlspecialchars($result["categories"]); ?></td>

                        </tr>
                        <button id="btn btn-primary" value="<?php echo $result["id"]; ?>" onclick="review(this.value)">Review <?php echo "" . $result["title"] ?> </button>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <button class="btn btn-primary" onclick="closeWindow()">Return To Main Menu</button>

</body>

</html>