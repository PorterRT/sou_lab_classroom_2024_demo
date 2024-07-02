<?php
include '../auth_check.php';
// Call the function from auth_check.php will redirect to landing if not authenticated
ensureAuthenticated();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab & Classroom Management Training Manual</title>
    <link rel="stylesheet" href="../Styles/Manual.css">
</head>
<body>
<a href="../main.php" class="home-link">Home</a>

<!-- Header Section -->
<div class="header">
    <img src="../Styles/SOU.png" alt="Southern Oregon University Logo" class="logo">
</div>

<!-- Navigation Bar -->
<div class="nav">
    <a href="#guest-landing">Guest Landing</a>
    <a href="#guest-software-search">Guest Software Search</a>
    <a href="#guest-building-search">Guest Building Search</a>
    <a href="#equipment">Equipment Management</a>
    <a href="#software">Software Management</a>
    <a href="#buildings">Building Management</a>
    <a href="#maintenance">Maintenance Records</a>
    <a href="#installed-software">Installed Software</a>
    <a href="#bulk-add">Bulk Add Equipment</a>
    <a href="#admin-management">Admin Management</a>
</div>

<!-- Guest Landing Page Section -->
<div id="guest-landing" class="section">
    <h2>Guest Landing Page</h2>
    <p>This is the main landing page for guest users.</p>
    <ul>
        <li><a href="#guest-software-search">Search by Software</a>: Locate specific software, computer model, building name, map link, and room number.</li>
        <li><a href="#guest-building-search">Search by Building</a>: Locate all software available in the building along with room numbers.</li>
    </ul>
    <img src="../ManualPictures/GuestLanding.PNG" alt="Guest Landing Page">
</div>

<!-- Guest Software Search Section -->
<div id="guest-software-search" class="section">
    <h2>Guest Software Search</h2>
    <p>Allows guests to search for specific software locations.</p>
    <ul>
        <li>
            <b>Software Dropdown:</b> Searches for software locations, software name is required.
            <img src="../ManualPictures/GuestSoft.PNG" alt="Guest Software Search" class="inline-image">
        </li>
        <li>
            <b>Model Dropdown:</b> Omits software from search results and only returns model location.
        </li>
        <li>
            <b>Building Filter:</b> Limits building results to the selected building; this is an optional field.
        </li>
    </ul>
</div>

<!-- Guest Building Search Section -->
<div id="guest-building-search" class="section">
    <h2>Guest Building Search</h2>
    <p>Allows guests to search for software by building.</p>
    <ul>
        <li>
            <b>Building Dropdown:</b> Selects a specific building to return all available software in the specified building along with room locations.
            <img src="../ManualPictures/GustBuild.PNG" alt="Guest Building Search" class="inline-image">
        </li>
        <li>
            <b>Directions:</b> Displays a building name as a link to the SOU map showing the building location.
        </li>
    </ul>
</div>

<!-- Equipment Management Section -->
<div id="equipment" class="section">
    <h2>Equipment Management</h2>
    <p>Admin users can add, edit, or delete equipment details here.</p>
    <ul>
        <li>
            <b>Search Bar:</b> Utilizes the “Search By” Dropdown to search by equipment Name, Serial Number, Type, or Model.
            <img src="../ManualPictures/EquipmentSearch.PNG" alt="Equipment Search" class="inline-image">
        </li>
        <li>
            <b>Select Building:</b> Dropdown menu will narrow search results to only results from the selected building.
        </li>
        <li>
            <b>Room Number:</b> Will filter results to show results only from the selected room number.
        </li>
        <li>
            <b>Add Equipment:</b> Opens the add equipment form and prompts the user to enter required fields for new equipment.
        </li>
        <li>
            <b>Download CSV:</b> Will download a .csv file that contains the results of the last search including serial number, Name, Type, Model, Lease Year, and Room Number.
        </li>
        <li>
            <b>Home:</b> The home button will return the user to the main page.
        </li>
    </ul>
    <img src="../ManualPictures/EquipmentSelectboxes.PNG" alt="Equipment Selection" class="inline-image">
    <img src="../ManualPictures/EquipmentEditDeleteButtons.PNG" alt="Edit/Delete Equipment" class="inline-image">
</div>

<!-- Software Management Section -->
<div id="software" class="section">
    <h2>Software Management</h2>
    <p>Manage software installations and attributes.</p>
    <ul>
        <li>
            <b>Home:</b> The home button will return the user to the main page.
        </li>
        <li>
            <b>Search Bar:</b> This field is optional, if populated with a software name the search results will be filtered to only display that software.
            <img src="../ManualPictures/SoftwareSearchAndCategory.PNG" alt="Search by Software" class="inline-image">
        </li>
        <li>
            <b>Search by Category:</b> Will filter the search results to display only software from the selected category.
        </li>
        <li>
            <b>Search Button:</b> Will apply the selected options to the filter and then perform the search.
        </li>
        <li>
            <b>Add New Software Section:</b> Used to add new software and categories to the database.
            <ul>
                <li>
                    <b>Software Name Bar:</b> Used to enter the name of a new software to be added.
                    <img src="../ManualPictures/SoftwareAddSoftwareButtonMenu.PNG" alt="Add New Software" class="inline-image">
                </li>
                <li>
                    <b>Category Dropdown Menu:</b> Can be used to add software to an existing category or to create a new category that includes the specified software listed under software name.
                    <img src="../ManualPictures/SoftwareAddSoftwareButtonMenu.PNG" alt="Category Dropdown" class="inline-image">
                </li>
                <li>
                    <b>Select Boxes:</b> Check the boxes next to the program that you would like to remove from the database.
                    <img src="../ManualPictures/SoftwareSelectboxes.PNG" alt="Select Boxes" class="inline-image">
                </li>
                <li>
                    <b>Delete Selected Button:</b> Clicking this button will remove any program that has been selected with the check box.
                    <img src="../ManualPictures/SoftwareDeleteSelectedButton.PNG" alt="Delete Selected" class="inline-image">
                </li>
                <li>
                    <b>Edit:</b> Selecting the edit link will bring the user to the edit software screen allowing the user to edit the existing software name and category. When finished editing, select Upload to apply changes.
                    <img src="../ManualPictures/SoftwareEditButtons.PNG" alt="Edit Software" class="inline-image">
                </li>
            </ul>
        </li>
    </ul>
</div>

<!-- Building Management Section -->
<div id="buildings" class="section">
    <h2>Building Management</h2>
    <p>View and edit building-related information.</p>
    <ul>
        <li>
            <b>Home:</b> The home button will return the user to the main page.
        </li>
        <li>
            <b>Search Bar:</b> Optional field, if populated the search results will be filtered to only show results.
            <img src="../ManualPictures/BuildingSearchButton.PNG" alt="Search Building" class="inline-image">
        </li>
        <li>
            <b>Search Button:</b> Will apply the selected options if any to the filter and then perform the search. If the search bar is left empty, will return all buildings.
        </li>
        <li>
            <b>Edit:</b> Selecting this link will allow the user to edit the associated building name and building location.
            <img src="../ManualPictures/BuildingEditButton.PNG" alt="Edit Building" class="inline-image">
        </li>
    </ul>
</div>

<!-- Maintenance Records Section -->
<div id="maintenance" class="section">
    <h2>Maintenance Records</h2>
    <p>Track and manage maintenance records.</p>
    <ul>
        <li>
            <b>Home:</b> The home button will return the user to the main page.
        </li>
        <li>
            <b>Sort By Button:</b> Allows the user to sort maintenance records by latest, oldest, ascending or descending serial number.
            <img src="../ManualPictures/MaintanceSortByButton.PNG" alt="Sort By" class="inline-image">
        </li>
        <li>
            <b>Serial Number Link:</b> Selecting this link will redirect the user to the selected equipment’s maintenance notes.
            <img src="../ManualPictures/MaintainceSerialButtons.PNG" alt="Serial Number Links" class="inline-image">
        </li>
        <li>
            <b>Delete (Trashcan Icon):</b> This will remove the related maintenance note from the database.
        </li>
    </ul>
    <img src="../ManualPictures/MaintainceAddentry.PNG" alt="Add New Entry" class="inline-image">
</div>

<!-- Installed Software Section -->
<div id="installed-software" class="section">
    <h2>Installed Software</h2>
    <p>Manage installed software on equipment.</p>
    <ul>
        <li>
            <b>Enter Search Term Bar:</b> Optional, filters search results to display only the software or serial number searched for.
            <img src="../ManualPictures/InstalledSoftwareSearchBar.PNG" alt="Search Bar" class="inline-image">
        </li>
        <li>
            <b>Serial and Program Dropdown:</b> Allows the user to prioritize between serial number-based search or program name.
        </li>
        <li>
            <b>Select Building:</b> Optional, allows users to filter search results to only display the selected building.
        </li>
        <li>
            <b>Room Number:</b> Optional, applies a filter to only display results from a specific room number.
        </li>
        <li>
            <b>Search Button:</b> Will apply the selected options if any to the filter and then perform the search. If the search terms field is left empty, will return all results.
        </li>
        <li>
            <b>Check Box:</b> Allows the user to select multiple pieces of equipment to apply software changes to.
        </li>
        <li>
            <b>Manage Software Button:</b> Opens a pop-up that allows the user to decide what software is attributed to the selected machines.
        </li>
    </ul>
    <h3>Manage Software Popup Menu</h3>
    <ul>
        <li>
            <b>Select Software Bar:</b> Allows the user to scroll through a list of software and add/remove multiple software to the checkbox-selected machines.
            <img src="../ManualPictures/InstalledSoftwareManageSoftwareButton.PNG" alt="Manage Software" class="inline-image">
        </li>
        <li>
            <b>Choose Action Dropdown:</b> Allows the user to decide between adding software to equipment or removing equipment.
        </li>
        <li>
            <b>Execute Action Button:</b> Applies user selection to the database.
        </li>
        <li>
            <b>Cancel Button:</b> Exits the managed software pop-up and discards user changes.
        </li>
    </ul>
</div>

<!-- Bulk Add Equipment Section -->
<div id="bulk-add" class="section">
    <h2>Bulk Add Equipment</h2>
    <p>Upload a CSV file to add multiple pieces of equipment simultaneously.</p>
    <ul>
        <li>
            <b>Choose File Button:</b> Allows the user to upload a .csv file containing data formatted for bulk add.
            <img src="../ManualPictures/BullkAddCSVPage.PNG" alt="Choose File" class="inline-image">
        </li>
        <li>
            <b>Upload Button:</b> Completes the upload process and adds equipment to the database.
        </li>
        <li>
            <b>Download Template:</b> Downloads a correctly formatted template that guides the user on how to fill out the bulk add .csv.
        </li>
    </ul>
</div>

<!-- Admin Management Section -->
<div id="admin-management" class="section">
    <h2>Admin Management</h2>
    <p>Administer user accounts and permissions.</p>
    <ul>
        <li>
            <b>Home Button:</b> The home button will return the user to the main page.
        </li>
        <li>
            <b>Search by Username Bar:</b> Optional, this field will filter the search results and display only user names that match the entered value.
            <img src="../ManualPictures/AdminSearchAndAddUserButton.PNG" alt="Search by Username" class="inline-image">
        </li>
        <li>
            <b>Search Users Button:</b> When selected this button will complete the search and return results.
        </li>
        <li>
            <b>Delete Link:</b> Selecting this link will delete the associated user from the admin list removing all admin privileges in the process.
            <img src="../ManualPictures/AdminDeleteUserButton.PNG" alt="Delete User" class="inline-image">
        </li>
        <li>
            <b>Add User Button:</b> Opens a popup prompt to add users to add a new admin.
            <ul>
                <li>
                    <b>Add New User Username Field:</b> The user shall enter the new admin .sou email account to be given admin privileges.
                </li>
                <li>
                    <b>Super Admin Checkbox:</b> Will display when adding a new admin. This box allows the user to grant SuperAdmin privileges of the new admin.
                </li>
                <li>
                    <b>Confirm New User Button:</b> Completes the admin creation and applies changes to the database.
                </li>
                <li>
                    <b>Cancel Button:</b> This will cancel the creation of the new admin.
                </li>
            </ul>
        </li>
        <li>
            <b>Edit User Button:</b> Opens a popup prompt to edit an existing admin.
            <ul>
                <li>
                    <b>Home Button:</b> The home button will return the user to the main page.
                </li>
                <li>
                    <b>User Name Field:</b> This field allows the user to change the admin’s email address / username.
                    <img src="../ManualPictures/AdminEditUserButton.PNG" alt="Edit User" class="inline-image">
                </li>
                <li>
                    <b>Super Admin Checkbox:</b> Will grant SuperAdmin rights to the admin being edited if checked.
                </li>
                <li>
                    <b>Confirm Edit Button:</b> Will apply changes made to the admin to the database.
                </li>
                <li>
                    <b>Cancel Button:</b> Will abort admin editing and return the user to the admin page.
                </li>
            </ul>
        </li>
    </ul>
</div>

<!-- Footer -->
<div class="footer">
    <p>© 2015 - 2024 Southern Oregon University</p>
</div>

</body>
</html>
