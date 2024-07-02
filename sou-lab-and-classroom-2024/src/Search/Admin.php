<?php
include '../config.php';
try {
    $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // No need to check for SuperAdmin table since we are using AdminAccess table
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Management</title>
    <link rel="stylesheet" href="../Styles/stylesAdmin.css">
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this user?");
        }

        function toggleAddForm() {
            var addForm = document.getElementById("addUserForm");
            if (addForm.style.display === "none" || addForm.style.display === "") {
                addForm.style.display = "block";
            } else {
                addForm.style.display = "none";
            }
        }

        function toggleEditForm(userNum, userName, isSuperAdmin) {
            document.getElementById("editUserForm").style.display = "block";
            document.getElementById("editUserNum").value = userNum;
            document.getElementById("editUserName").value = userName;
            document.getElementById("editSuperAdmin").checked = isSuperAdmin === 'Yes';
        }

        function cancel(formId) {
            document.getElementById(formId).style.display = "none";
        }
    </script>
</head>
<body>
<a href="../main.php" class="home-link">Home</a>

<div class="AddUserButton">
    <!-- Add User Button -->
    <button onclick="toggleAddForm()">Add User</button>
</div>

<div id="addUserForm" style="display: none;">
    <h2>Add New User</h2>
    <form method="post">
        Username: <input type="text" name="UserName" required>
        SuperAdmin: <input type="checkbox" name="SuperAdmin">
        <input type="submit" name="insert" value="Confirm New User">
        <button type="button" class="cancel" onclick="cancel('addUserForm')">Cancel</button>
        
    </form>
</div>

<!-- Edit User Form (Initially Hidden) -->
<div id="editUserForm" style="display: none;">
    <h2>Edit User</h2>
    <form method="post">
        <input type="hidden" name="UserNum" id="editUserNum">
        Username: <input type="text" name="UserName" id="editUserName" required>
        SuperAdmin: <input type="checkbox" name="SuperAdmin" id="editSuperAdmin">
        <input type="submit" name="edit" value="Confirm Edit">
        <button type="button" class="cancel" onclick="cancel('editUserForm')">Cancel</button>
    </form>
</div>

<!-- Search Users -->
<div class="SearchBar">
    <form method="post">
        <input type="text" name="searchUserName" placeholder="Search by UserName">
        <input type="submit" name="search" value="Search Users">
    </form>
</div>

<!-- Display Users -->
<?php
try {
    include '../config.php';
    $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['search'])) {
        $searchUserName = isset($_POST['searchUserName']) ? $_POST['searchUserName'] : '';
        
        // Fetch users from AdminAccess table with optional search filter
        $sth = $con->prepare("SELECT UserNum, UserName, SuperAdmin FROM AdminAccess WHERE UserName LIKE :searchUserName");
        $sth->bindValue(':searchUserName', '%' . $searchUserName . '%', PDO::PARAM_STR);
        $sth->execute();
        $usersResults = $sth->fetchAll(PDO::FETCH_OBJ);

        // Display users
        echo "<h2>Admin Access Users</h2>";
        if ($usersResults) {
            echo "<table border='1'>";
            echo "<tr><th>UserNum</th><th>UserName</th><th>SuperAdmin</th><th>Edit</th><th>Delete</th></tr>";
            foreach ($usersResults as $row) {
                echo "<tr><td>" . htmlspecialchars($row->UserNum) . "</td>";
                echo "<td>" . htmlspecialchars($row->UserName) . "</td>";
                echo "<td>" . ($row->SuperAdmin ? 'Yes' : 'No') . "</td>";
                echo "<td><button onclick=\"toggleEditForm(" . htmlspecialchars($row->UserNum) . ", '" . htmlspecialchars($row->UserName) . "', '" . ($row->SuperAdmin ? 'Yes' : 'No') . "')\">Edit</button></td>";
                echo "<td><a href='?deleteAdminAccess=" . htmlspecialchars($row->UserNum) . "' onclick='return confirmDelete()'>Delete</a></td></tr>";
            }
            echo "</table>";
        } else {
            echo "No users found.";
        }
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!-- Insert New User -->
<?php
if (isset($_POST['insert'])) {
    try {
        $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $userName = $_POST['UserName'];
        $isSuperAdmin = isset($_POST['SuperAdmin']) ? 1 : 0;

        // Validate username
        if (!filter_var($userName, FILTER_VALIDATE_EMAIL) || !preg_match('/@sou\.edu$/', $userName)) {
            echo "Invalid username. Must be an @sou.edu email.";
        } else {
            // Check for duplicate username
            $sth = $con->prepare("SELECT COUNT(*) FROM AdminAccess WHERE UserName = :userName");
            $sth->bindValue(':userName', $userName, PDO::PARAM_STR);
            $sth->execute();
            $count = $sth->fetchColumn();

            if ($count > 0) {
                echo "Username already exists in AdminAccess.";
            } else {
                $sth = $con->prepare("INSERT INTO AdminAccess (UserName, SuperAdmin) VALUES (:userName, :superAdmin)");
                $sth->bindValue(':userName', $userName, PDO::PARAM_STR);
                $sth->bindValue(':superAdmin', $isSuperAdmin, PDO::PARAM_BOOL);

                if ($sth->execute()) {
                    exit;
                } else {
                    echo "<p>Insert failed.</p>";
                }
            }
        }
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}
?>

<!-- Edit User -->
<?php
if (isset($_POST['edit'])) {
    try {
        $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $userNum = $_POST['UserNum'];
        $userName = $_POST['UserName'];
        $isSuperAdmin = isset($_POST['SuperAdmin']) ? 1 : 0;

        // Validate username
        if (!filter_var($userName, FILTER_VALIDATE_EMAIL) || !preg_match('/@sou\.edu$/', $userName)) {
            echo "Invalid username. Must be an @sou.edu email.";
        } else {
            $sth = $con->prepare("UPDATE AdminAccess SET UserName = :userName, SuperAdmin = :superAdmin WHERE UserNum = :userNum");
            $sth->bindValue(':userName', $userName, PDO::PARAM_STR);
            $sth->bindValue(':superAdmin', $isSuperAdmin, PDO::PARAM_BOOL);
            $sth->bindValue(':userNum', $userNum, PDO::PARAM_INT);

            if ($sth->execute()) {
                exit;
            } else {
                echo "<p>Update failed.</p>";
            }
        }
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}
?>

<!-- Delete User -->
<?php
if (isset($_GET['deleteAdminAccess'])) {
    $idToDelete = $_GET['deleteAdminAccess'];
    try {
        $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sth = $con->prepare("DELETE FROM AdminAccess WHERE UserNum = :id");
        $sth->bindValue(':id', $idToDelete, PDO::PARAM_INT);
        if ($sth->execute()) {
            exit;
        } else {
            echo "<p>Delete failed.</p>";
        }
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}
?>

</body>
</html>