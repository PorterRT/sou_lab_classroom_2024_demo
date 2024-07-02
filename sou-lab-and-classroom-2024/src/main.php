<?php
include 'auth_check.php';
// Call the function from auth_check.php will redirect to landing if not authenticated
ensureAuthenticated();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main</title>
    <link rel="stylesheet" href="/Styles/styles.css">

</head>
<body>

<div class="header">
    <a href="https://inside.sou.edu/"> <!-- HOME page of sou -->
        <img src="/Styles/SOU.png" alt="SOU Logo" class="logo" />
    </a>
</div>
<div class="logoutButton">
        <a href="logout.php">Logout</a>
    </div>

<div class="nav">
    <a href="Search/searchEquipment.php">Equipment</a>
    <a href="Search/searchSoftware.php">Software</a>
    <a href="Search/searchBuilding.php">Buildings</a>
    <a href="Search/maintenance.php"> Maintenance</a>
    <a href="Search/EquipSoft.php"> Installed Software</a>
    <a href="CSV/CSV.php"> Bulk Add</a>
    <a href="Guest/Landing.html"> Guest Pages</a>
    <a href="Search/Admin.php"> Admin Management</a>
    <a href="Search/Manual.php"> Manual </a>

</div>

<div class="SearchBar">
    <form class="search-form" method="post" action="">
        <select class='model-dropdown' name="searchType">
            <option value="software">Software</option>
            <option value="model">Model</option>
        </select>
        <input type="text" placeholder="Search..." name="search" required>
        <select class='dropdown' name="building">
            <option value="">Optional Building Filter</option>
            <?php
            include 'config.php'; // Include your database connection code here
            try {
                $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $buildingQuery = $con->query("SELECT BuildingName FROM Buildings");
                $buildings = $buildingQuery->fetchAll(PDO::FETCH_COLUMN);
                foreach ($buildings as $building) {
                    echo "<option value=\"$building\">$building</option>";
                }
            } catch (PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
            ?>
        </select>
        <button type="submit" name="submitSearch">🔍</button>
    </form>
</div>

<?php
include 'config.php'; // Make sure to use your actual config file



$results = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitSearch'])) {
    $searchTerm = $_POST['search'];
    $buildingTerm = isset($_POST['building']) ? $_POST['building'] : '';
    $searchType = $_POST['searchType'];

    try {
        $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "SELECT DISTINCT Equipment.Model, ";
        if ($searchType === 'model') {
            $query .= "'' AS ProgramName, ";
        } else {
            $query .= "Software.ProgramName, ";
        }
        $query .= "Buildings.BuildingName, Buildings.SOUMapLinkDir, Equipment.RoomNum
                  FROM Equipment 
                  LEFT JOIN EquipSoft ON Equipment.SerialNum = EquipSoft.SerialNum
                  LEFT JOIN Software ON EquipSoft.AutoGenNumS = Software.AutoGenNumS 
                  LEFT JOIN Buildings ON Equipment.AssignedBuildNum = Buildings.AssignedBuildNum 
                  WHERE (Equipment.Model LIKE :searchTerm OR Software.ProgramName LIKE :searchTerm)
                  AND Buildings.BuildingName LIKE :buildingTerm";

        $stmt = $con->prepare($query);
        $stmt->bindValue(':searchTerm', "%$searchTerm%");
        $stmt->bindValue(':buildingTerm', "%$buildingTerm%");
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}
?>

<?php if (!empty($results)): ?>
    <div class="search-results">
        <table>
            <thead>
            <tr>
                <?php if ($searchType === 'software'): ?>
                    <th>Searched Software</th>
                <?php endif; ?>
                <th>Model</th>
                <th>Building Name  <div class="loader"></div></th>
                <th>Room Number</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($results as $row): ?>
          <tr>
          <?php if ($searchType === 'software'): ?>
        <td><?= htmlspecialchars($row['ProgramName'] ?? '') ?></td>
        <?php endif; ?>
        <td><?= htmlspecialchars($row['Model'] ?? '') ?></td>
        <td><a href="<?= htmlspecialchars($row['SOUMapLinkDir'] ?? '') ?>" target="_blank"><?= htmlspecialchars($row['BuildingName'] ?? '') ?></a></td>
        <td><?= htmlspecialchars($row['RoomNum'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php elseif (isset($_POST['submitSearch'])): ?>
    <p>No results found for <?= htmlspecialchars($_POST['search']) ?>.</p>
<?php endif; ?>

<div class="footer">
    <p>© 2015 - 2024 Southern Oregon University</p>
</div>

</body>
</html>
