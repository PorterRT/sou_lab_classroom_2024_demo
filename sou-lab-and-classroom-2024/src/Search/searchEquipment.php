<?php
include '../auth_check.php';
// Call the function from auth_check.php will redirect to landing if not authenticated
ensureAuthenticated();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Equipment Search</title>
    <link rel="stylesheet" href="../Styles/stylesEquipment.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Link to main page -->
    <a href="../main.php" class="home-link">Home</a>

    <script>
        // Individual deletion
        function confirmDelete(serialNum) {
            if (confirm("Are you sure you want to delete this equipment and all its associated maintenance notes " +
                "and all software associated with this equipment?")) {
                window.location.href = '?delete=' + serialNum;
            }
        }

        // Bulk deletion
        function confirmBulkDeletion() {
            // Check how many checkboxes are selected
            var selectedItems = document.querySelectorAll('input[name="selectedEquipment[]"]:checked').length;
            if (selectedItems > 0) {
                // If at least one checkbox is selected, ask for confirmation
                return confirm("Are you sure you want to delete the selected equipment and all its associated maintenance notes?");
            } else {
                // If no items are selected, alert the user and prevent form submission
                alert("Please select at least one item to delete.");
                return false;
            }
        }
        function showTypeInput() {
            var select = document.getElementById("TypeSelect");
            var selectedOption = select.options[select.selectedIndex].value;
            var newTypeInput = document.getElementById("newTypeInput");
            var addSubmitButton = document.querySelector("[name='addSubmit']");

            if (selectedOption === "Add New") {
                newTypeInput.style.display = "block";
                addSubmitButton.disabled = true; // Disable the submit button by default when "Add New" is selected
                newTypeInput.oninput = function() {
                    // Check if the new type input is not empty
                    addSubmitButton.disabled = newTypeInput.value.trim() === "";
                };
            } else {
                newTypeInput.style.display = "none";
                addSubmitButton.disabled = false; // Enable the submit button when "Add New" is not selected
                newTypeInput.value = ''; // Clear any text in the new type input field when it's hidden
            }
        }

            function toggleSelectAll(source) {
            checkboxes = document.querySelectorAll('input[name="selectedEquipment[]"]');
            for (var i = 0, n = checkboxes.length; i < n; i++) {
            checkboxes[i].checked = source.checked;
        }
        }


    </script>



</head>
<body>

<!-- Search Form -->
<div class="SearchBar">
    <form method="post">
        <label for="search">Search:</label>
        <input type="text" id="search" name="search" placeholder="Search">

        <label for="searchBy">Search By:</label>
        <select name="searchBy" id="searchBy">
            <option value="Name">Name</option>
            <option value="SerialNum">Serial Number</option>
            <option value="Type">Type</option>
            <option value="Model">Model</option>
        </select>

        <label for="building">Building Name:</label>
        <select name="building" id="building">
            <option value="">Select Building</option>
            <?php
            include '../config.php';
            try {
                $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $con->prepare("SELECT AssignedBuildNum, BuildingName FROM Buildings");
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row['AssignedBuildNum']}'>{$row['BuildingName']}</option>";
                }
            } catch (PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
            ?>
        </select>

        <label for="RoomNum">Room Number:</label>
        <input type="text" id="RoomNum" name="RoomNum" placeholder="Room Number">

        <input type="submit" name="submit" value="Search">
    </form>
</div>



<!-- Add Button -->
<button class='Add-Equipment' onclick="document.getElementById('addModal').style.display='block'">Add Equipment</button>
<button class='Download-CSV' onclick="downloadCSV()">Download CSV</button>

<!-- Add Equipment Modal -->
<div id="addModal" style="display:none;">
    <form method="post">
        <label for="serialNum">Serial Number:</label>
        <input type="text" id="serialNum" name="serialNum" required>

        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="Model">Model:</label>
        <input type="text" id="Model" name="Model" required> <!-- Add Model field -->

        <label for="LeaseYear">Lease Year:</label>
        <input type="text" id="LeaseYear" name="LeaseYear" required>

        <label for="RoomNum">Room Num:</label>
        <input type="text" name="RoomNum" id="RoomNum" required>
        <br>
        <label for="AssignedBuildNum">Building Name:</label>
        <select name="AssignedBuildNum" id="AssignedBuildNum" required>
            <?php
            include 'config.php';
            try {
                $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stmt = $con->query("SELECT AssignedBuildNum, BuildingName FROM Buildings");
                $buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($buildings as $building) {
                    echo "<option value=\"" . $building['AssignedBuildNum'] . "\">" . $building['BuildingName'] . "</option>";
                }
            } catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
            ?>
        </select>
        <label for="Type">Type:</label>
        <select id="TypeSelect" name="Type" onchange="showTypeInput()">
            <?php
            // Assuming you have a table or method to fetch existing types
            include 'config.php';
            $stmt = $con->query("SELECT DISTINCT Type FROM Equipment"); // Adjust table and column names as necessary
            $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($types as $type) {
                echo "<option value=\"" . htmlspecialchars($type['Type']) . "\">" . htmlspecialchars($type['Type']) . "</option>";
            }
            ?>
            <option value="Add New">Add New</option>
        </select>

        <input type="text" id="newTypeInput" name="NewType" style="display: none;" placeholder="Enter new type">


        <input type="submit" name="addSubmit" value="Add Equipment">
        <button type="button" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
    </form>
</div>

<!-- JavaScript for Dynamic Room Number Input Field -->
<script>
    // Event listener for dropdown menu change
    document.getElementById('searchBy').addEventListener('change', function() {
        var RoomNum = document.getElementById('RoomNum');
        // If "Building Name" is selected, show the room number input field; otherwise, hide it
        RoomNum.style.display = this.value === 'BuildingName' ? 'inline-block' : 'none';
    });
</script>

<script>
    function showTypeInput() {
        var select = document.getElementById("TypeSelect");
        var selectedOption = select.options[select.selectedIndex].value;
        var newTypeInput = document.getElementById("newTypeInput");
        if (selectedOption === "Add New") {
            newTypeInput.style.display = "block";
        } else {
            newTypeInput.style.display = "none";
        }
    }
</script>



<!-- PHP Code for Searching and Adding Equipment -->
<?php
include '../config.php';
try {
    $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST["submit"])) {
        $str = $_POST["search"];
        $searchBy = $_POST["searchBy"];
        $building = $_POST["building"];

        // Map form inputs to database columns securely
        $columnMap = [
            'Name' => 'Equipment.Name',
            'SerialNum' => 'Equipment.SerialNum',
            'Type' => 'Equipment.Type',
            'Model' => 'Equipment.Model',
            'BuildingName' => 'Buildings.BuildingName',
            'RoomNum' => 'Equipment.RoomNum'
        ];

        // Validate the search column to prevent SQL injection
        if (!array_key_exists($searchBy, $columnMap)) {
            throw new Exception("Invalid search criteria.");
        }
        // Construct the base SQL query
        $query = "SELECT Equipment.*, Buildings.BuildingName 
                  FROM Equipment 
                  JOIN Buildings ON Equipment.AssignedBuildNum = Buildings.AssignedBuildNum 
                  WHERE 1=1";

        // Append conditions based on user input
        if (!empty($str)) {
            $query .= " AND " . $columnMap[$searchBy] . " LIKE :search";
        }
        if (!empty($building)) {
            $query .= " AND Buildings.AssignedBuildNum = :building";
        }

        $sth = $con->prepare($query);

        // Bind parameters
        if (!empty($str)) {
            $sth->bindValue(':search', "%$str%");
        }
        if (!empty($building)) {
            $sth->bindValue(':building', $building);
        }

        $sth->execute();
        $results = $sth->fetchAll(PDO::FETCH_OBJ);

        // Output results or process them
        if ($results) {
            echo "<form method='post' action=''>";
            echo "<input type='submit' name='deleteSelected' value='Delete Selected Equipment' onclick='return confirmBulkDeletion();' style='margin-bottom: 10px;'>";
            // Add inputs for batch editing
            echo "<span class='new-building-label'>New Building:</span> <select name='newAssignedBuildNum'>";
            echo "<option value=''>Select Building</option>";
            $stmt = $con->query("SELECT AssignedBuildNum, BuildingName FROM Buildings");
            while ($building = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$building['AssignedBuildNum']}'>{$building['BuildingName']}</option>";
            }
            echo "</select>";
            echo " <span class='new-building-label'> New Room Number:</span> <input type='text' name='newRoomNum' placeholder='Room Number'>";
            echo "<input type='submit' name='editSelected' value='Edit Selected' style='margin-top: 10px;'>";

            echo "<table border='1'>";
            echo "<tr>
            <th><input type='checkbox' id='selectAll' onclick='toggleSelectAll(this)'></th>
            <th>Serial Number</th>
            <th>Name</th>
            <th>Type</th>
            <th>Model</th>
            <th>Lease Year</th>
            <th>Building Name</th>
            <th>Room Number</th>
            <th>Edit</th>
            <th>Delete</th>
          </tr>";

            foreach ($results as $row) {
                echo "<tr>";
                echo "<td><input type='checkbox' name='selectedEquipment[]' value='" . htmlspecialchars($row->SerialNum) . "'></td>";
                echo "<td><a href='maintenance.php?serial=" . urlencode($row->SerialNum) . "'>" . htmlspecialchars($row->SerialNum) . "</a></td>";
                echo "<td>" . htmlspecialchars($row->Name) . "</td>";
                echo "<td>" . htmlspecialchars($row->Type) . "</td>";
                echo "<td>" . htmlspecialchars($row->Model) . "</td>";
                echo "<td>" . htmlspecialchars($row->LeaseYear) . "</td>";
                echo "<td>" . htmlspecialchars($row->BuildingName) . "</td>";
                echo "<td>" . htmlspecialchars($row->RoomNum) . "</td>";
                echo "<td><a href='?edit=" . urlencode($row->SerialNum) . "' class='edit-icon'><i class='fas fa-edit'></i></a></td>";
                echo "<td><a href='javascript:void(0);' onclick='confirmDelete(\"" . htmlspecialchars($row->SerialNum) . "\");' class='delete-icon'><i class='fas fa-trash'></i></a></td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</form>";
        } else {
            echo "No results found.";
        }

    }

    if (isset($_POST['editSelected']) && !empty($_POST['selectedEquipment'])) {
        $selectedEquipment = $_POST['selectedEquipment'];
        $newAssignedBuildNum = $_POST['newAssignedBuildNum'];
        $newRoomNum = $_POST['newRoomNum'];

        if (!empty($newAssignedBuildNum) || !empty($newRoomNum)) {
            $con->beginTransaction();
            try {
                foreach ($selectedEquipment as $serialNum) {
                    $query = "UPDATE Equipment SET ";
                    $params = [];
                    if (!empty($newAssignedBuildNum)) {
                        $query .= "AssignedBuildNum = :assignedBuildNum, ";
                        $params[':assignedBuildNum'] = $newAssignedBuildNum;
                    }
                    if (!empty($newRoomNum)) {
                        $query .= "RoomNum = :roomNum, ";
                        $params[':roomNum'] = $newRoomNum;
                    }
                    $query = rtrim($query, ', ');
                    $query .= " WHERE SerialNum = :serialNum";
                    $params[':serialNum'] = $serialNum;

                    $stmt = $con->prepare($query);
                    $stmt->execute($params);
                }
                $con->commit();
                echo "<script>alert('Selected equipment updated successfully.');</script>";
            } catch (PDOException $e) {
                $con->rollBack();
                echo "<script>alert('Error during batch update: " . $e->getMessage() . "');</script>";
            }
        } else {
            echo "<script>alert('Please fill out all fields for batch editing.');</script>";
        }
    }


    // Deleting more than one checkbox
    if (isset($_POST["deleteSelected"]) && !empty($_POST['selectedEquipment'])) {
        $con->beginTransaction();
        try {
            foreach ($_POST["selectedEquipment"] as $serialNumToDelete) {
                // Delete related records in EquipSoft table first
                $sth = $con->prepare("DELETE FROM EquipSoft WHERE SerialNum = :serialNum");
                $sth->bindValue(':serialNum', $serialNumToDelete);
                $sth->execute();

                // Then, delete the maintenance notes for this equipment
                $sth = $con->prepare("DELETE FROM Maintenance WHERE SerialNum = :serialNum");
                $sth->bindValue(':serialNum', $serialNumToDelete);
                $sth->execute();

                // Now delete the equipment
                $sth = $con->prepare("DELETE FROM Equipment WHERE SerialNum = :serialNum");
                $sth->bindValue(':serialNum', $serialNumToDelete);
                $sth->execute();
            }
            $con->commit();
            echo "<p>Selected equipment and associated maintenance notes deleted successfully.</p>";
        } catch(PDOException $e) {
            $con->rollBack();
            echo "Error during batch deletion: " . $e->getMessage();
        }
    } elseif (isset($_GET['delete'])) {
        $serialNumToDelete = $_GET['delete'];

        // Start a transaction
        $con->beginTransaction();

        try {
            // First, delete the maintenance notes for this equipment
            $sth = $con->prepare("DELETE FROM Maintenance WHERE SerialNum = :serialNum");
            $sth->bindValue(':serialNum', $serialNumToDelete);
            $sth->execute();

            // Then, delete the equipment itself
            $sth = $con->prepare("DELETE FROM Equipment WHERE SerialNum = :serialNum");
            $sth->bindValue(':serialNum', $serialNumToDelete);
            $sth->execute();

            // If everything went well, commit the transaction
            $con->commit();
            echo "<p>Equipment and associated maintenance notes with Serial Number $serialNumToDelete deleted successfully.</p>";

        } catch(PDOException $e) {
            // If an error occurs, roll back the transaction
            $con->rollBack();
            echo "Error during deletion: " . $e->getMessage();
        }
    }




    if (isset($_POST["addSubmit"])) {
        $serialNum = $_POST["serialNum"];
        $name = $_POST["name"];
        $type = $_POST["Type"];
        $model = $_POST["Model"];
        $leaseYear = $_POST["LeaseYear"];
        $roomNum = $_POST["RoomNum"];
        $assignedBuildNum = $_POST["AssignedBuildNum"];

        // Validate input types
        if (!is_string($serialNum)) {
            echo "Check Serial Number Something went wrong.";
        } elseif (!is_string($name) || !is_string($type) || !is_string($model) || !is_string($leaseYear) || !is_numeric($roomNum)) {
            echo "Please ensure all text fields are properly filled out.";
        } elseif  (!is_numeric($assignedBuildNum)) {
            echo "Room Number and Assigned Building Number must be numeric values.";
        } elseif ($type === "Add New") {
                $newType = $_POST['NewType'] ?? ''; // Use the null coalescing operator to ensure $newType has a value
                if (empty($newType)) {
                    echo "Error: Please enter a type for 'New Type'.";
                } else {
                    $type = $newType;
                    // Optionally insert new type into a Types table here
                    // $stmt = $con->prepare("INSERT INTO EquipmentTypes (Type) VALUES (:type)");
                    // $stmt->bindParam(':type', $type);
                    // $stmt->execute();
                }
            }
        else{


            // Check if serial number already exists
            $stmt = $con->prepare("SELECT SerialNum FROM Equipment WHERE SerialNum = :serialNum");
            $stmt->bindValue(':serialNum', $serialNum);
            $stmt->execute();
            $existingSerialNum = $stmt->fetchColumn();

            if ($existingSerialNum) {
                echo "Serial Number already added. Try update instead!";
            } else {
                // Insert new equipment
                $stmt = $con->prepare("INSERT INTO Equipment (SerialNum, Name, Type, Model, LeaseYear, RoomNum, AssignedBuildNum) VALUES (:serialNum, :name, :type, :model, :leaseYear, :roomNum, :assignedBuildNum)");
                $stmt->bindParam(':serialNum', $serialNum);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':type', $type);
                $stmt->bindParam(':model', $model);
                $stmt->bindParam(':leaseYear', $leaseYear);
                $stmt->bindParam(':roomNum', $roomNum);
                $stmt->bindParam(':assignedBuildNum', $assignedBuildNum);
                $stmt->execute();
                echo "Equipment added successfully!";

                // Redirect after adding equipment to avoid form resubmission
               // header("Location: searchEquipment.php");
                exit();
            }
        }
    }

// Delete operation
    if (isset($_GET['delete']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $serialNumToDelete = $_GET['delete'];

        // Start a transaction
        $con->beginTransaction();

        try {
            // First, delete related records in EquipSoft table
            $sth = $con->prepare("DELETE FROM EquipSoft WHERE SerialNum = :serialNum");
            $sth->bindValue(':serialNum', $serialNumToDelete);
            $sth->execute();

            // Then, delete the maintenance notes for this equipment
            $sth = $con->prepare("DELETE FROM Maintenance WHERE SerialNum = :serialNum");
            $sth->bindValue(':serialNum', $serialNumToDelete);
            $sth->execute();

            // Now delete the equipment
            $sth = $con->prepare("DELETE FROM Equipment WHERE SerialNum = :serialNum");
            $sth->bindValue(':serialNum', $serialNumToDelete);
            $sth->execute();

            // If everything went well, commit the transaction
            $con->commit();

            // Redirect without 'delete' parameter to avoid resubmission
            //header("Location: searchEquipment.php");
            exit();
        } catch(PDOException $e) {
            // If an error occurs, roll back the transaction
            $con->rollBack();
            echo "Error during deletion: " . $e->getMessage();
        }
    }




    // Edit operation
    if (isset($_GET['edit'])) {
        $serialNumToEdit = $_GET['edit'];
        $sth = $con->prepare("SELECT * FROM Equipment WHERE SerialNum = :serialNum");
        $sth->bindValue(':serialNum', $serialNumToEdit);
        $sth->execute();
        $row = $sth->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo "<form method='post' action=''>";
            echo "Serial Number: <input type='text' name='serialNumEdit' value='" . htmlspecialchars($row['SerialNum']) . "' readonly><br>";
            echo "Name: <input type='text' name='nameEdit' value='" . htmlspecialchars($row['Name']) . "'><br>";
            echo "Type: <select name='typeEdit'>";

            // Fetching existing types
            $stmt = $con->query("SELECT DISTINCT Type FROM Equipment");
            $types = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($types as $type) {
                echo "<option value='" . htmlspecialchars($type) . "'";
                if ($type == $row['Type']) {
                    echo " selected";
                }
                echo ">" . htmlspecialchars($type) . "</option>";
            }
            echo "</select><br>";

            // Input field for new type

            echo "Model: <input type='text' name='modelEdit' value='" . htmlspecialchars($row['Model']) . "'><br>";
            echo "Lease Year: <input type='text' name='leaseYearEdit' value='" . htmlspecialchars($row['LeaseYear']) . "'><br>";
            echo "Room Num: <input type='text' name='roomNumEdit' value='" . htmlspecialchars($row['RoomNum']) . "'><br>";
            echo "Building Name: <select name='assignedBuildNumEdit'>";

            // Fetching building names
            $stmt = $con->query("SELECT AssignedBuildNum, BuildingName FROM Buildings");
            $buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($buildings as $building) {
                if ($building['AssignedBuildNum'] == $row['AssignedBuildNum']) {
                    echo "<option value=\"" . $building['AssignedBuildNum'] . "\" selected>" . $building['BuildingName'] . "</option>";
                } else {
                    echo "<option value=\"" . $building['AssignedBuildNum'] . "\">" . $building['BuildingName'] . "</option>";
                }
            }
            echo "</select><br>";
            echo "<input type='submit' name='updateSubmit' value='Update'>";
            echo "</form>";
        }
    }



    // Update operation
    if (isset($_POST['updateSubmit'])) {
        $serialNum = $_POST['serialNumEdit'];
        $name = $_POST['nameEdit'];
        $type = $_POST['typeEdit'];
        $model = $_POST['modelEdit']; // Add Model field
        $leaseYear = $_POST['leaseYearEdit'];
        $roomNum = $_POST['roomNumEdit'];
        $assignedBuildNum = $_POST['assignedBuildNumEdit'];

        // Validate input types
        if (!is_string($name)) {
            echo "Name must be a string.";
        } elseif (!is_string($type)) {
            echo "Type must be a string.";
        } elseif (!is_string($model)) { // Validate Model type
            echo "Model must be a string.";
        } elseif (!is_string($leaseYear)) {
            echo "Lease Year must be a String.";
        } elseif (!is_string($roomNum)) {
            echo "Room Number must be a 3 character value or less.";
        } elseif (!is_numeric($assignedBuildNum)) {
            echo "Assigned Building Number must be a numeric value.";
        } else {
            // Update equipment
            $stmt = $con->prepare("UPDATE Equipment SET Name = :name, Type = :type, Model = :model, LeaseYear = :leaseYear, RoomNum = :roomNum, AssignedBuildNum = :assignedBuildNum WHERE SerialNum = :serialNum");
            $stmt->bindParam(':serialNum', $serialNum);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':model', $model); // Bind Model parameter
            $stmt->bindParam(':leaseYear', $leaseYear);
            $stmt->bindParam(':roomNum', $roomNum);
            $stmt->bindParam(':assignedBuildNum', $assignedBuildNum);
            $stmt->execute();
            echo "Equipment updated successfully!";
        }
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
<script>
    function downloadCSV() {
        // Prompt the user for a file name and exit if none is provided
        var filename = prompt("Please enter a name for your CSV file:", "search_results.csv");
        if (filename === null || filename.trim() === "") {
            alert("File download cancelled.");
            return;  // Exit the function if no filename is provided
        }

        // Select the table containing the search results
        var table = document.querySelector('table');
        var csvContent = [];
        var columnIndexMap = [];

        // Define the columns from the table to be included in the CSV
        var columnsToInclude = ["Serial Number", "Name", "Type", "Model", "Lease Year", "Room Number", "Building Name"];

        // Process the first row to identify the relevant column indices
        var headers = table.querySelectorAll('tr')[0].querySelectorAll('th');
        headers.forEach((header, index) => {
            if (columnsToInclude.includes(header.innerText.trim())) {
                columnIndexMap.push(index);
            }
        });

        // Iterate over all rows to build the CSV content
        var rows = table.querySelectorAll('tr');
        rows.forEach(row => {
            var csvRow = [];
            columnIndexMap.forEach(index => {
                var text = row.querySelectorAll('td, th')[index].innerText.trim().replace(/"/g, '""'); // Trim whitespace, escape existing quotes
                csvRow.push('"' + text + '"'); // Wrap text in double quotes to handle special characters
            });
            csvContent.push(csvRow.join(','));
        });

        // Convert array of CSV lines into a single string
        var csvContentStr = csvContent.join('\n');

        // Create a Blob containing the CSV data
        var blob = new Blob([csvContentStr], { type: 'text/csv;charset=utf-8;' });

        // Use a temporary anchor element to trigger the download
        var link = document.createElement('a');
        if (link.download !== undefined) { // Feature detection
            var url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
</script>


</body>
</html>