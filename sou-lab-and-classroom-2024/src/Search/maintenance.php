<?php
include '../auth_check.php';
// Call the function from auth_check.php will redirect to landing if not authenticated
ensureAuthenticated();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Maintenance</title>
    <link rel="stylesheet" href="../Styles/stylesMaintenance.css">
    <style>
        .confirm-delete {
            display: none;
        }
    </style>
    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this entry?")) {
                document.getElementById('deleteForm_' + id).submit();
            }
        }

        
    </script>
    <!--    library icon for delete -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


</head>
<body>
<a href="../main.php" class="home-link">Home</a>

<!-- Search Form -->
<div class="dropdown">
    <button class="dropbtn">Sort By</button>
    <div class="dropdown-content">
        <a href="#" onclick="submitSortForm('latest')">Latest</a>
        <a href="#" onclick="submitSortForm('oldest')">Oldest</a>
        <a href="#" onclick="submitSortForm('ascSerial')">Ascending Serial Num</a>
        <a href="#" onclick="submitSortForm('descSerial')">Descending Serial Num</a>
    </div>
</div>

<form method="get" style="display:none;" id="sortForm">
    <input type="hidden" name="sortBy" id="sortBy">
</form>

<!--script for sorting the table-->
<script>
function submitSortForm(sortByValue) {
    document.getElementById('sortBy').value = sortByValue;
    document.getElementById('sortForm').submit();
}
</script>

<!-- Maintenance Records -->
<?php
include '../config.php';

try {
    $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $str = isset($_GET['serial']) ? $_GET['serial'] : '';

    if ($str != '') {
        $sth = $con->prepare("SELECT * FROM Maintenance WHERE SerialNum LIKE :str");
        $sth->bindValue(':str', "%" . $str . "%");
        $sth->execute();
        $results = $sth->fetchAll(PDO::FETCH_OBJ);

        if ($results) {
            // Right before the table and rows are defined:
            echo "<table border='1'>";
            echo "<tr>
                <th class='serial-number'>Serial Number</th>
                <th class='notes'>Notes</th>
                <th class='date'>Date (YYYY-MM-DD)</th>
                <th style='padding: 20px;' class='action'>Action</th>
        </tr>";
        foreach ($results as $row) {
        echo "<tr class='test'>
            <td class='serial-number'><a href='maintenance.php?serial=" . htmlspecialchars($row->SerialNum) . "'>" . htmlspecialchars($row->SerialNum) . "</a></td>
            <td class='notes'>" . htmlspecialchars($row->Notes) . "</td>
            <td class='date'>" . htmlspecialchars($row->Date) . "</td>
            <td class='action' style='width:25%'><button onclick=\"confirmDelete(" . $row->AutoGenNumM . ")\" class='Delete'><i class='fas fa-trash'></i></button></td>
          </tr>";
        echo "<form id='deleteForm_" . $row->AutoGenNumM . "' method='post'><input type='hidden' name='deleteId' value='" . $row->AutoGenNumM . "'></form>";
}
        echo "</table>";

            $showAddButton = true;
        } else {
            // Offer to add the serial number to the database
            echo "<p>No notes found for serial number '$str'.</p>";
            echo "<p>Would you like to add a new note for this serial number?</p>";
            echo "<form method='post'>";
            echo "<input type='hidden' name='serialNum' value='$str'>";
            echo "<label for='note'>Note:</label>";
            echo "<textarea id='note' name='note' required></textarea><br>";
            echo "<input type='submit' name='addNoteSubmit' value='Add Note'>";
            echo "</form>";
            $showAddButton = false;
        }
    } else {
        // Display all maintenance records
        $sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'latest';

        $sql = "SELECT * FROM Maintenance";

        switch ($sortBy) {
            case 'oldest':
                $sql .= " ORDER BY Date ASC";
                break;
            case 'ascSerial':
                $sql .= " ORDER BY SerialNum ASC";
                break;
            case 'descSerial':
                $sql .= " ORDER BY SerialNum DESC";
                break;
            case 'latest':
            default:
                $sql .= " ORDER BY Date DESC";
                break;
        }

        $sth = $con->prepare($sql);
        $sth->execute();
        $results = $sth->fetchAll(PDO::FETCH_OBJ);

        if ($results) {
            echo "<table border='1'>";
            echo "<tr><th>Serial Number</th><th>Notes</th><th>Date (YYYY-MM-DD)</th><th>Action</th></tr>";
            foreach ($results as $row) {
                echo "<tr><td><a href='maintenance.php?serial=" . htmlspecialchars($row->SerialNum) . "'>" . htmlspecialchars($row->SerialNum) . "</a></td>" .
                    "<td class='maintenanc-notes'>" . htmlspecialchars($row->Notes) . "</td>" .
                    "<td>" . htmlspecialchars($row->Date) . "</td>" .
                    "<td><button onclick=\"confirmDelete(" . $row->AutoGenNumM . ")\" class='Delete'><i class='fas fa-trash'></i> </button></td></tr>";
                echo "<form id='deleteForm_$row->AutoGenNumM' method='post'><input type='hidden' name='deleteId' value='$row->AutoGenNumM'></form>";
            }
            echo "</table>";
        } else {
            echo "No maintenance records found.";
        }
        $showAddButton = false;
    }

    // Add new note
    if (isset($_POST["addNoteSubmit"])) {
        $serialNum = $_POST["serialNum"];
        $note = $_POST["note"];
        $date = date("Y-m-d");

        // Insert into Maintenance table
        $stmt = $con->prepare("INSERT INTO Maintenance (SerialNum, Notes, Date) VALUES (:serialNum, :note, :date)");
        $stmt->bindParam(':serialNum', $serialNum);
        $stmt->bindParam(':note', $note);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        echo "Note added successfully!";

        // Reload the page with the serial number
        echo "<script>window.location.href = window.location.href.split('?')[0] + '?serial=$serialNum';</script>";
    }

    // Delete entry
    if (isset($_POST["deleteId"])) {
        $deleteId = $_POST["deleteId"];

        // Delete from Maintenance table
        $stmt = $con->prepare("DELETE FROM Maintenance WHERE AutoGenNumM = :deleteId");
        $stmt->bindParam(':deleteId', $deleteId);
        $stmt->execute();

        // Redirect to the page without any query parameters
        echo "<script>window.location.href = window.location.href.split('?')[0];</script>";
    }
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!-- Add New Entry Button -->
<?php if ($showAddButton): ?>
    <button onclick="document.getElementById('addEntryModal').style.display='block'">Add New Entry</button>
<?php endif; ?>

<!-- Add Entry Modal -->
<div id="addEntryModal" style="display:none;">
    <form method="post">
        <input type="hidden" name="serialNum" value="<?php echo $str; ?>">
        <label for="note">Note:</label>
        <textarea id="note" name="note" required></textarea><br>

        <input class="Add Note" type="submit" name="addNoteSubmit" value="Add Note">
        <button class="Cancel" type="button" onclick="document.getElementById('addEntryModal').style.display='none'">Cancel</button>
    </form>
</div>

</body>
</html>
