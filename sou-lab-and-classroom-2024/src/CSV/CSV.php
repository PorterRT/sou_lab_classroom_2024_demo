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
    <title>Upload Equipment</title>
    <link rel="stylesheet" href="../Styles/stylesSoftware.css">
    <a href="../main.php" class="home-link">Home</a>
</head>
<body>
<h1>Upload Equipment Data</h1>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
    <input type="submit" name="upload" value="Upload">
</form>

<!-- Add a link to download the template file -->
<a href="Lab&ClassCSVTemplate.csv" download>Download Template</a>

<?php
include '../config.php'; // Include database configuration

function xlsxToCsv($filename, $csvFilename) {
    $zip = new ZipArchive;
    if ($zip->open($filename) === TRUE) {
        $zip->extractTo('temp/');
        $zip->close();

        $xml = simplexml_load_file('temp/xl/sharedStrings.xml');
        $strings = [];
        foreach ($xml->si as $string) {
            $strings[] = (string)$string->t;
        }

        $xml = simplexml_load_file('temp/xl/worksheets/sheet1.xml');
        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $cell) {
                $index = (int)$cell->v;
                $cells[] = $strings[$index];
            }
            $rows[] = implode(',', $cells);
        }

        file_put_contents($csvFilename, implode("\n", $rows));

        // Clean up temporary files
        array_map('unlink', glob("temp/*.*"));
        rmdir('temp/');
    } else {
        throw new Exception('Failed to open XLSX file.');
    }
}

function validateRow($row) {
    $errors = [];
    if (empty($row[0])) $errors[] = 'Error in SerialNum: Check length and spelling for errors';
    if (empty($row[1])) $errors[] = 'Error found: Check "Name" for any possible errors';
    if (empty($row[2])) $errors[] = 'Error found: Check "Type" for any possible errors';
    if (empty($row[3])) $errors[] = 'Error found: Check "Model" for any possible errors';
    if (empty($row[4])) $errors[] = 'LeaseYear is empty';
    if (empty($row[5])) $errors[] = 'Error found with "Room Number": Check length is not more than 3 characters';
    if (empty($row[6])) $errors[] = 'Error found in "Building Name": Check spelling and match it to a building. If needed, go to the Buildings page to help';
    return $errors;
}

try {
    // Set up database connection
    $con = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['upload'])) {
        if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $fileType = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

            if ($fileType == 'csv' || $fileType == 'xlsx' || $fileType == 'xls') {
                $filename = $_FILES['file']['tmp_name'];

                if ($fileType == 'xlsx' || $fileType == 'xls') {
                    $csvFilename = 'converted.csv';
                    xlsxToCsv($filename, $csvFilename);
                    $filename = $csvFilename;
                }

                $data = array_map('str_getcsv', file($filename));
                $headers = array_shift($data); // Ignore the first row (column labels)

                // Remove the 'Errors' column if it exists
                if (end($headers) === 'Errors') {
                    array_pop($headers);
                }

                $invalidRows = [];
                $hasErrors = false;
                $duplicateSerialNumErrors = [];
                $successCount = 0;
                $errorCount = 0;

                foreach ($data as $row) {
                    // Remove the 'Errors' column data if it exists
                    if (count($row) > count($headers)) {
                        array_pop($row);
                    }

                    $validationErrors = validateRow($row);
                    if (empty($validationErrors)) {
                        $serialNum = $row[0];
                        $name = $row[1];
                        $type = $row[2];
                        $model = $row[3];
                        $leaseYear = $row[4];
                        $roomNum = $row[5];
                        $buildingName = $row[6];

                        // Check for duplicate SerialNum
                        $stmt = $con->prepare("SELECT COUNT(*) FROM Equipment WHERE SerialNum = :serialNum");
                        $stmt->bindParam(':serialNum', $serialNum);
                        $stmt->execute();
                        $count = $stmt->fetchColumn();

                        if ($count > 0) {
                            $duplicateError = 'Error in SerialNum: Serial number already exists in the database';
                            $validationErrors[] = $duplicateError;
                            $duplicateSerialNumErrors[] = $duplicateError . " for " . $name;
                        }

                        if (empty($validationErrors)) {
                            // Insert into the database with detailed error handling
                            $con->beginTransaction();
                            try {
                                // Handle building assignment
                                $stmt = $con->prepare("SELECT AssignedBuildNum FROM Buildings WHERE BuildingName LIKE :buildingName ORDER BY LENGTH(BuildingName)");
                                $stmt->bindValue(':buildingName', '%' . $buildingName . '%');
                                $stmt->execute();
                                $assignedBuildNum = $stmt->fetchColumn();

                                if ($assignedBuildNum === false) {
                                    throw new Exception('Error found in "Building Name": Check spelling and match it to a building. If needed, go to the Buildings page to help');
                                }

                                // Insert equipment data
                                $stmt = $con->prepare("INSERT INTO Equipment (SerialNum, Name, Type, Model, LeaseYear, RoomNum, AssignedBuildNum) VALUES (:serialNum, :name, :type, :model, :leaseYear, :roomNum, :assignedBuildNum)");
                                $stmt->bindParam(':serialNum', $serialNum);
                                $stmt->bindParam(':name', $name);
                                $stmt->bindParam(':type', $type);
                                $stmt->bindParam(':model', $model);
                                $stmt->bindParam(':leaseYear', $leaseYear);
                                $stmt->bindParam(':roomNum', $roomNum);
                                $stmt->bindParam(':assignedBuildNum', $assignedBuildNum);
                                $stmt->execute();

                                $con->commit();
                                $successCount++;
                            } catch (Exception $e) {
                                $con->rollBack();
                                $validationErrors[] = $e->getMessage();
                                $invalidRows[] = array_merge($row, ['Errors' => implode('; ', $validationErrors)]);
                                $hasErrors = true;
                                $errorCount++;
                            }
                        } else {
                            $invalidRows[] = array_merge($row, ['Errors' => implode('; ', $validationErrors)]);
                            $hasErrors = true;
                            $errorCount++;
                        }
                    } else {
                        $invalidRows[] = array_merge($row, ['Errors' => implode('; ', $validationErrors)]);
                        $hasErrors = true;
                        $errorCount++;
                    }
                }

                if (!empty($invalidRows)) {
                    $needsFixingFile = 'NeedsFixing.csv';
                    $file = fopen($needsFixingFile, 'w');
                    fputcsv($file, array_merge($headers, ['Errors'])); // Add headers with Errors column
                    foreach ($invalidRows as $invalidRow) {
                        fputcsv($file, $invalidRow);
                    }
                    fclose($file);
                    echo "<p>Error or errors found in some data. Download the needs fixing file to find and fix the errors, then re-upload the needs fixing file. All other data not in the file has been entered into the database.</p>";
                    echo "<a href='$needsFixingFile' download>Download Needs Fixing File</a>";

                    // Display duplicate SerialNum errors on the front page
                    if (!empty($duplicateSerialNumErrors)) {
                        echo "<p>Duplicate Serial Number Errors:</p><ul>";
                        foreach ($duplicateSerialNumErrors as $error) {
                            echo "<li>$error</li>";
                        }
                        echo "</ul>";
                    }
                }

                echo "<p>Number of rows successfully added: $successCount</p>";
                echo "<p>Number of rows with errors: $errorCount</p>";
            } else {
                echo "Please upload a CSV or Excel file.";
            }
        } else {
            echo "File upload failed.";
        }
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
</body>
</html>
