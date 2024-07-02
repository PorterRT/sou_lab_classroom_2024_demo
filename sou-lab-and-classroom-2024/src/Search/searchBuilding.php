<?php
include '../auth_check.php';
// Call the function from auth_check.php will redirect to landing if not authenticated
ensureAuthenticated();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Building Search</title>
    <link rel="stylesheet" href="../Styles/stylesBuildings.css">
</head>
<body>
<a href="../main.php" class="home-link">Home</a>

<!-- Search Form -->
<div class="SearchBar">
    <form method="post">
        <label for="search">Search:</label>
        <input type="text" id="search" name="search" placeholder="Search Building">
        <input type="submit" name="submit" value="Search">
    </form>
</div>

<?php
include '../config.php';

try {
    $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST["submit"])) {
        $str = $_POST["search"];
        $sth = $con->prepare("SELECT * FROM Buildings WHERE BuildingName LIKE :str");
        $sth->bindValue(':str', "%$str%");
        $sth->execute();
        $results = $sth->fetchAll(PDO::FETCH_OBJ);

        if ($results) {
            echo "<table border='1'>";
            echo "<tr><th>Building Num</th><th>Building Name</th><th>SOUmapLinkDir</th><th>Edit</th></tr>";
            foreach ($results as $row) {
                echo "<tr><td>" . htmlspecialchars($row->AssignedBuildNum) .
                    "</td><td>" . htmlspecialchars($row->BuildingName) .
                    "</td><td>" . htmlspecialchars($row->SOUmapLinkDir) .
                    "</td><td><a href='?edit=" . htmlspecialchars($row->AssignedBuildNum) . "'>Edit</a>";
            }
            echo "</table>";
        } else {
            echo "No results found.";
        }
    }

    // Edit operation
    if (isset($_GET['edit'])) {
        $idToEdit = $_GET['edit'];
        $sth = $con->prepare("SELECT * FROM Buildings WHERE AssignedBuildNum = :id");
        $sth->bindValue(':id', $idToEdit);
        $sth->execute();
        $row = $sth->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo "<div class='edit-update-form'>";
            echo "<form method='post' action=''>";
            echo "Building Name: <input type='text' name='name' value='" . htmlspecialchars($row['BuildingName']) . "'><br>";
            echo "SOUmapLinkDir: <input type='text' name='soumap' value='" . htmlspecialchars($row['SOUmapLinkDir']) . "'><br>";
            echo "<input type='hidden' name='id' value='" . htmlspecialchars($row['AssignedBuildNum']) . "'>";
            echo "<input type='submit' name='update' value='Update'>";
            echo "</form>";
        }
    }

    // Update operation
    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $soumap = $_POST['soumap'];

        $sth = $con->prepare("UPDATE Buildings SET BuildingName = :name, SOUmapLinkDir = :soumap WHERE AssignedBuildNum = :id");

        if ($sth->execute([':name' => $name, ':soumap' => $soumap, ':id' => $id])) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo "<p>Update failed.</p>";
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

</body>
</html>
