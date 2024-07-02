<?php
include '../auth_check.php';
// Call the function from auth_check.php will redirect to landing if not authenticated
ensureAuthenticated();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Software on Equipment</title>
    <link rel="stylesheet" href="../Styles/stylesEquipSoft.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
</head>
<body>
<a href="../main.php" class="home-link">Home</a>

<div class="SearchBar">
    <form class='SearchBar' method="post">
        <label for="search"></label>
        <input type="text" id="search" name="search" placeholder="Enter search term...">
        <select name="searchType">
            <option value="serialNum">By Serial Number</option>
            <option value="programName">By Program Name</option>
        </select>
        <select name="assignedBuildNum">
            <option value="">Select Building</option>
            <?php
            include '../config.php';
            try {
                $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $building_query = $con->query("SELECT * FROM Buildings");
                $buildings = $building_query->fetchAll(PDO::FETCH_ASSOC);
                foreach ($buildings as $building) {
                    echo "<option value='{$building['AssignedBuildNum']}'>{$building['BuildingName']}</option>";
                }
            } catch (PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
            ?>
        </select>
        <input type="text" id="roomNum" name="roomNum" placeholder="Room Number">
        <input type="submit" name="submit" value="Search">
    </form>
</div>


<?php
try {
    $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch software list for select dropdown
    $software_list = $con->query("SELECT * FROM Software");
    $software_list = $software_list->fetchAll(PDO::FETCH_OBJ);

    // Handle the form submissions for managing software
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['manageSoftware'])) {
        $softwareIds = $_POST['softwareToManage'];
        $serialNums = $_POST['serialNums'];
        $errors = [];
        $successes = [];
        $action = $_POST['action'];

        foreach ($softwareIds as $softwareId) {
            foreach ($serialNums as $serialNum) {
                // Check if the association already exists
                $checkExistenceQuery = $con->prepare("SELECT * FROM EquipSoft WHERE SerialNum = :SerialNum AND AutoGenNumS = :AutoGenNumS");
                $checkExistenceQuery->bindValue(':SerialNum', $serialNum);
                $checkExistenceQuery->bindValue(':AutoGenNumS', $softwareId);
                $checkExistenceQuery->execute();

                if ($action == 'add') {
                    // If the action is to add, we only need to proceed if the association doesn't exist
                    if ($checkExistenceQuery->rowCount() == 0) {
                        $sth = $con->prepare("INSERT INTO EquipSoft (SerialNum, AutoGenNumS) VALUES (:SerialNum, :AutoGenNumS)");
                        $sth->bindValue(':SerialNum', $serialNum);
                        $sth->bindValue(':AutoGenNumS', $softwareId);
                        if ($sth->execute()) {
                            $successes[] = "Software ID $softwareId successfully added to equipment $serialNum.";
                        } else {
                            $errors[] = "Error during $action action for software ID $softwareId on equipment $serialNum.";
                        }
                    } else {
                        $successes[] = "Software ID $softwareId is already associated with equipment $serialNum.";
                    }
                } elseif ($action == 'remove') {
                    // If the action is to remove, we need to check if the association exists
                    if ($checkExistenceQuery->rowCount() > 0) {
                        $sth = $con->prepare("DELETE FROM EquipSoft WHERE SerialNum = :SerialNum AND AutoGenNumS = :AutoGenNumS");
                        $sth->bindValue(':SerialNum', $serialNum);
                        $sth->bindValue(':AutoGenNumS', $softwareId);
                        if ($sth->execute()) {
                            $successes[] = "Software ID $softwareId successfully removed from equipment $serialNum.";
                        } else {
                            $errors[] = "Error during $action action for software ID $softwareId on equipment $serialNum.";
                        }
                    } else {
                        $errors[] = "Software ID $softwareId is not associated with equipment $serialNum.";
                    }
                }
            }
        }

        if (!empty($successes)) {
            echo "<p>Successful actions:</p><ul>";
            foreach ($successes as $success) {
                echo "<li>$success</li>";
            }
            echo "</ul>";
        }

        if (!empty($errors)) {
            echo "<p>Some actions could not be completed:</p><ul>";
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
        }
    }

    // Process search and display results
    if (isset($_POST['submit']) && isset($_POST['search'])) {
        $search = $_POST['search'];
        $searchType = $_POST['searchType'];
        $buildingNum = $_POST['assignedBuildNum'];
        $roomNum = $_POST['roomNum'];

        $sth = $con->prepare("SELECT Equipment.SerialNum, Buildings.BuildingName, Equipment.RoomNum, GROUP_CONCAT(DISTINCT Software.ProgramName ORDER BY Software.ProgramName ASC SEPARATOR ', ') AS SoftwareNames
                              FROM Equipment
                              LEFT JOIN EquipSoft ON Equipment.SerialNum = EquipSoft.SerialNum
                              LEFT JOIN Software ON EquipSoft.AutoGenNumS = Software.AutoGenNumS
                              LEFT JOIN Buildings ON Equipment.AssignedBuildNum = Buildings.AssignedBuildNum
                              WHERE (Equipment.SerialNum LIKE :search OR Software.ProgramName LIKE :search)
                              AND (Equipment.AssignedBuildNum = :buildingNum OR :buildingNum = '' )
                              AND (Equipment.RoomNum LIKE :roomNum)
                              GROUP BY Equipment.SerialNum");
        $sth->bindValue(':search', "%$search%");
        $sth->bindValue(':buildingNum', $buildingNum);
        $sth->bindValue(':roomNum', "%$roomNum%");
        $sth->execute();
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<?php if (!empty($results)): ?>
    <button class="Manage Software" type="button" onclick="toggleForm()">Manage Software</button>
    <table border="1">
        <thead>
        <tr>
            <th><input type="checkbox" id="selectAll"></th>
            <th>Serial Number</th>
            <th>Building Name</th>
            <th>Room Number</th>
            <th>Installed Software</th>
        </tr>
        </thead>
        <form method="post" id="softwareManagementForm">
            <?php foreach ($results as $row): ?>
                <tr>
                    <td><input type="checkbox" class="selectItem" name="serialNums[]" value="<?= htmlspecialchars($row['SerialNum']) ?>"></td>
                    <td><?php echo isset($row['SerialNum']) ? htmlspecialchars($row['SerialNum']) : ''; ?></td>
                    <td><?php echo isset($row['BuildingName']) ? htmlspecialchars($row['BuildingName']) : ''; ?></td>
                    <td><?php echo isset($row['RoomNum']) ? htmlspecialchars($row['RoomNum']) : ''; ?></td>
                    <td><?php echo isset($row['SoftwareNames']) ? htmlspecialchars($row['SoftwareNames']) : ''; ?></td>

                </tr>
            <?php endforeach; ?>
            <tr id="managementForm" style="display:none;"><td colspan="5">
                    <label>Select Software(s):<br>
                        <select id="softwareSelect" name="softwareToManage[]" multiple="multiple">
                            <?php foreach ($software_list as $software): ?>
                                <option value="<?= htmlspecialchars($software->AutoGenNumS) ?>"><?= htmlspecialchars($software->ProgramName) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label><br>
                    <label>Choose Action:<br>
                        <select name="action">
                            <option value="add">Add to Equipment</option>
                            <option value="remove">Remove from Equipment</option>
                        </select>
                    </label><br>
                    <input class ='execute-button' type="submit" name="manageSoftware" value="Execute Action">
                    <input class="cancel-button" type="button" value="Cancel" onclick="cancelAction()">
                </td></tr>
        </form>
    </table>
<?php elseif (isset($_POST['submit'])): ?>
    <p>No results found.</p>
<?php endif; ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#softwareSelect').select2();

        // Select/deselect all checkboxes
        $('#selectAll').change(function() {
            $('.selectItem').prop('checked', $(this).prop('checked'));
        });

        $('.selectItem').change(function() {
            if (!$(this).prop('checked')) {
                $('#selectAll').prop('checked', false);
            }
        });
    });

    function toggleForm() {
        var x = document.getElementById("managementForm");
        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
    }

    function cancelAction() {
        var x = document.getElementById("managementForm");
        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
    }
    // Smoothly toggle the visibility of the management form
    function toggleForm() {
        var form = document.getElementById("managementForm");
        if (form.style.display === "none") {
            fadeIn(form);
        } else {
            fadeOut(form);
        }
    }

    // Fade in effect for the form
    function fadeIn(element) {
        var opacity = 0;
        element.style.display = "block";
        var fadeInInterval = setInterval(function() {
            if (opacity >= 1) {
                clearInterval(fadeInInterval);
            } else {
                opacity += 0.1;
                element.style.opacity = opacity;
            }
        }, 50);
    }


</script>
</body>
</html>
