<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Building Software and Equipment Finder</title>
    <link rel="stylesheet" href="../Styles/stylesGuestBuildingSearch.css">
</head>
<body>
    <div class="header">
        <a href="https://inside.sou.edu/">
            <img src="../Styles/SOU.png" alt="SOU Logo" class="logo" />
        </a>
    </div>
    <div class="nav">
        <a href="GuestSoftwareSearch.php"> Search by Software</a>
        <a href="GuestBuildingSearch.php"> Search by Building</a>
    </div>
    <div class="logoutButton">
        <a href="../logout.php">Logout</a>
    </div>

    <div class="SearchBar">
        <form class="search-form" method="post" action="">
            <select class='model-dropdown' name="building" required>
                <option value="">Select Building</option>
                <?php
                include '../config.php';
                try {
                    $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $buildingQuery = $con->query("SELECT BuildingName FROM Buildings");
                    $buildings = $buildingQuery->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($buildings as $building) {
                        echo "<option value=\"" . htmlspecialchars($building) . "\">" . htmlspecialchars($building) . "</option>";
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
    include '../config.php';
    $results = [];
    $buildingMapLink = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitSearch'])) {
        $buildingTerm = $_POST['building'];

        try {
            $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Retrieve distinct software and room numbers for the selected building
            $softwareQuery = "SELECT s.ProgramName, e.RoomNum
                              FROM Software s
                              INNER JOIN EquipSoft es ON s.AutoGenNumS = es.AutoGenNumS
                              INNER JOIN Equipment e ON es.SerialNum = e.SerialNum
                              WHERE e.AssignedBuildNum = (
                                  SELECT AssignedBuildNum
                                  FROM Buildings
                                  WHERE BuildingName = :buildingTerm
                              )";
            $softwareStmt = $con->prepare($softwareQuery);
            $softwareStmt->bindValue(':buildingTerm', $buildingTerm, PDO::PARAM_STR);
            $softwareStmt->execute();
            $softwareResults = $softwareStmt->fetchAll(PDO::FETCH_ASSOC);

            // Group the software results by ProgramName and collect the room numbers
            $software = [];
            foreach ($softwareResults as $row) {
                $programName = $row['ProgramName'];
                $roomNumber = $row['RoomNum'];
                
                if (!isset($software[$programName])) {
                    $software[$programName] = [];
                }
                
                if (!in_array($roomNumber, $software[$programName])) {
                    $software[$programName][] = $roomNumber;
                }
            }

            // Retrieve distinct equipment models for the selected building
            $equipmentQuery = "SELECT DISTINCT e.Model
                               FROM Equipment e
                               WHERE e.AssignedBuildNum = (
                                   SELECT AssignedBuildNum
                                   FROM Buildings
                                   WHERE BuildingName = :buildingTerm
                               )";
            $equipmentStmt = $con->prepare($equipmentQuery);
            $equipmentStmt->bindValue(':buildingTerm', $buildingTerm, PDO::PARAM_STR);
            $equipmentStmt->execute();
            $equipment = $equipmentStmt->fetchAll(PDO::FETCH_COLUMN);

            // Retrieve the SOUMapLinkDir for the selected building
            $mapLinkQuery = "SELECT SOUMapLinkDir
                             FROM Buildings
                             WHERE BuildingName = :buildingTerm";
            $mapLinkStmt = $con->prepare($mapLinkQuery);
            $mapLinkStmt->bindValue(':buildingTerm', $buildingTerm, PDO::PARAM_STR);
            $mapLinkStmt->execute();
            $buildingMapLink = $mapLinkStmt->fetchColumn();

            $results = [
                'software' => $software,
                'equipment' => $equipment
            ];
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage() . "<br>";
            echo "Error Info: " . print_r($e->errorInfo, true) . "<br>";
        }
    }
    ?>
    <?php if (!empty($results)): ?>
    <div class="search-results">
        <?php if (!empty($buildingMapLink)): ?>
            <p>Directions: <a href="<?= htmlspecialchars($buildingMapLink) ?>" target="_blank"><?= htmlspecialchars($buildingTerm) ?></a></p>
        <?php endif; ?>

        <div class="results-container">
            <div class="software">
                <h3>Software:</h3>
                <ul class="software-list">
                    <?php foreach ($results['software'] as $programName => $rooms): ?>
                        <li>
                            <?= htmlspecialchars($programName) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="rooms">
                <h3>Room Numbers:</h3>
                <ul class="room-list">
                    <?php foreach ($results['software'] as $rooms): ?>
                        <li>
                            <?php echo implode(", ", array_map('htmlspecialchars', $rooms)); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php elseif (isset($_POST['submitSearch'])): ?>
        <div class="no-results">
            <p>No results found for <?= htmlspecialchars($buildingTerm) ?>.</p>
        </div>
    <?php endif; ?>
    <div class="footer">
        <p>© 2015 - 2024 Southern Oregon University</p>
    </div>
</body>
</html>