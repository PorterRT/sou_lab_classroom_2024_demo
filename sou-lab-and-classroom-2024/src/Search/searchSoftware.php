<?php
include '../auth_check.php';
// Call the function from auth_check.php will redirect to landing if not authenticated
ensureAuthenticated();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Software Search</title>
    <link rel="stylesheet" href="../Styles/stylesSoftware.css">
    <script>
        function showTextInput() {
            var select = document.getElementById("categorySelect");
            var selectedOption = select.options[select.selectedIndex].value;
            if (selectedOption === "Add New") {
                document.getElementById("newCategoryInput").style.display = "block";
            } else {
                document.getElementById("newCategoryInput").style.display = "none";
            }
        }

        function showEditTextInput() {
            var select = document.getElementById("categorySelectEdit");
            var selectedOption = select.options[select.selectedIndex].value;
            if (selectedOption === "Add New") {
                document.getElementById("newCategoryInputEdit").style.display = "block";
            } else {
                document.getElementById("newCategoryInputEdit").style.display = "none";
            }
        }

        function confirmDelete() {
            return confirm("Are you sure you want to delete the selected software " +
                "it will also delete on any equipment is is on inf the database?");
        }

        function toggleAddForm() {
            var addForm = document.getElementById("addSoftwareForm");
            if (addForm.style.display === "none" || addForm.style.display === "") {
                addForm.style.display = "block";
            } else {
                addForm.style.display = "none";
            }
        }

        function selectAll() {
            var checkboxes = document.getElementsByName("softwareToDelete[]");
            var selectAllCheckbox = document.getElementById("selectAllCheckbox");
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = selectAllCheckbox.checked;
            }
        }
    </script>
</head>
<body>
<a href="../main.php" class="home-link">Home</a>

<!-- Search Form -->
<div class="SearchBar">
    <form method="post">
        <label for="search">Search:</label>
        <input type="text" id="search" name="search" placeholder="Search Program name">
        <select id="categorySelectSearch" name="searchCategory">
            <option value="">Search by Category</option>
            <?php
            include '../config.php';
            try {
                $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sth = $con->prepare("SELECT DISTINCT Category FROM Software");
                $sth->execute();
                $categories = $sth->fetchAll(PDO::FETCH_COLUMN);

                foreach ($categories as $category) {
                    if (!empty($category)) {
                        echo "<option value='" . htmlspecialchars($category) . "'>" . htmlspecialchars($category) . "</option>";
                    }
                }
            } catch (PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
            ?>
        </select>
        <input type="submit" name="submit" value="Search">
    </form>
</div>

<!-- Add Software Button -->
<button onclick="toggleAddForm()">Add Software</button>

<!-- Insert Form (Initially Hidden) -->
<div id="addSoftwareForm" style="display: none;">
    <h2>Add New Software</h2>
    <form method="post">
        Software Name: <input type="text" name="ProgramName" required>
        Category:
        <select id="categorySelect" name="Category" onchange="showTextInput()">
            <?php
            try {
                foreach ($categories as $category) {
                    if (!empty($category)) {
                        echo "<option value='" . htmlspecialchars($category) . "'>" . htmlspecialchars($category) . "</option>";
                    }
                }
                echo "<option value='Add New'>Add New</option>";
            } catch (PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
            ?>
        </select>
        <input type="text" id="newCategoryInput" name="NewCategory" style="display: none;">
        <input type="submit" name="insert" value="Add Software">
    </form>
</div>

<!-- Display Search Results -->
<?php
if (isset($_POST["submit"])) {
    try {
        $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $str = $_POST["search"];
        $searchCategory = $_POST["searchCategory"];

        $query = "SELECT * FROM Software WHERE ProgramName LIKE :str";
        $parameters = array(':str' => "%$str%");

        if (!empty($searchCategory)) {
            $query .= " AND Category = :searchCategory";
            $parameters[':searchCategory'] = $searchCategory;
        }

        $sth = $con->prepare($query);
        $sth->execute($parameters);
        $results = $sth->fetchAll(PDO::FETCH_OBJ);

        if (count($results) > 0) {
            echo "<form method='post'>";
            echo "<table border='1'>";
            echo "<tr>";
            echo "<th><input type='checkbox' id='selectAllCheckbox' onclick='selectAll()'></th>";
            echo "<th>Program name</th>";
            echo "<th>Category</th>";
            echo "<th>Edit</th>";
            echo "</tr>";

            foreach ($results as $row) {
                echo "<tr>";
                echo "<td><input type='checkbox' name='softwareToDelete[]' value='" . htmlspecialchars($row->AutoGenNumS) . "'></td>";
                echo "<td>" . htmlspecialchars($row->ProgramName) . "</td>";
                echo "<td>" . htmlspecialchars($row->Category) . "</td>";
                echo "<td><a href='?edit=" . htmlspecialchars($row->AutoGenNumS) . "'>Edit</a></td>";
                echo "</tr>";
            }

            echo "</table>";
            echo "<input type='submit' name='deleteSelected' value='Delete Selected' onclick='return confirmDelete()'>";
            echo "</form>";
        } else {
            echo "No results found.";
        }
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}
?>

<!-- Display Edit Form -->
<?php
if (isset($_GET['edit'])) {
    $idToEdit = $_GET['edit'];
    try {
        $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sth = $con->prepare("SELECT * FROM Software WHERE AutoGenNumS = :id");
        $sth->bindValue(':id', $idToEdit, PDO::PARAM_INT);
        $sth->execute();
        $row = $sth->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo "<div class='edit-update-form'>";
            echo "<form method='post'>";
            echo "Software: <input type='text' name='ProgramName' value='" . htmlspecialchars($row['ProgramName']) . "'><br>";
            echo "Category:";
            echo "<select id='categorySelectEdit' name='Category' onchange='showEditTextInput()'>";

            foreach ($categories as $category) {
                if (!empty($category)) {
                    $selected = ($row['Category'] == $category) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($category) . "' $selected>" . htmlspecialchars($category) . "</option>";
                }
            }
            echo "<option value='Add New'>Add New</option>";
            echo "</select>";
            echo "<input type='text' id='newCategoryInputEdit' name='NewCategory' style='display: none;'>";
            echo "<input type='hidden' name='AutoGenNumS' value='" . htmlspecialchars($row['AutoGenNumS']) . "'>";
            echo "<input type='submit' name='update' value='Update'>";
            echo "</form>";
            echo "</div>";
        }
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}
?>

<!-- Update Software -->
<?php
if (isset($_POST['update'])) {
    try {
        $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $ProgramName = $_POST['ProgramName'];
        $Category = $_POST['Category'];
        $NewCategory = isset($_POST['NewCategory']) ? $_POST['NewCategory'] : null;
        $AutoGenNumS = $_POST['AutoGenNumS'];

        if ($Category === "Add New") {
            $Category = $NewCategory;
        }

        $sth = $con->prepare("UPDATE Software SET ProgramName = :ProgramName, Category = :Category WHERE AutoGenNumS = :AutoGenNumS");
        $sth->bindValue(':ProgramName', $ProgramName);
        $sth->bindValue(':Category', $Category);
        $sth->bindValue(':AutoGenNumS', $AutoGenNumS);

        if ($sth->execute()) {
            //header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo "<p>Update failed.</p>";
        }
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}
?>

<!-- Insert New Software -->
<?php
if (isset($_POST['insert'])) {
    try {
        $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $ProgramName = $_POST['ProgramName'];
        $Category = $_POST['Category'];
        $NewCategory = isset($_POST['NewCategory']) ? $_POST['NewCategory'] : null;

        if ($Category === "Add New") {
            $Category = $NewCategory;
        }

        $sth = $con->prepare("INSERT INTO Software (ProgramName, Category) VALUES (:ProgramName, :Category)");
        $sth->bindValue(':ProgramName', $ProgramName);
        $sth->bindValue(':Category', $Category);

        if ($sth->execute()) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo "<p>Insert failed.</p>";
        }
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}
?>

<!-- Bulk Delete Operation -->
<?php
// Bulk Delete Operation
if (isset($_POST["deleteSelected"]) && !empty($_POST["softwareToDelete"])) {
    try {
        $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $con->beginTransaction();
        foreach ($_POST["softwareToDelete"] as $idToDelete) {
            // Delete related records from EquipSoft table
            $sth = $con->prepare("DELETE FROM EquipSoft WHERE AutoGenNumS = :id");
            $sth->bindValue(':id', $idToDelete);
            $sth->execute();

            // Delete from Software table
            $sth = $con->prepare("DELETE FROM Software WHERE AutoGenNumS = :id");
            $sth->bindValue(':id', $idToDelete);
            $sth->execute();
        }
        $con->commit();
        echo "<p>Selected software deleted successfully.</p>";
    } catch (PDOException $e) {
        $con->rollBack();
        echo "Error during bulk deletion: " . $e->getMessage();
    }
}
?>

</body>
</html>
