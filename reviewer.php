<!DOCTYPE html>
<html>
<head>
    <title>Fetch Keywords</title>
    <link rel="stylesheet" href="bootstrap.min.css">
</head>

<body class="container mt-5">
    <!-- Display JIRA log at the top of the page -->
    <div class="row mb-4">
        <div class="col-md-12">
        
        </div>
    </div>

    <h1 class="mb-4">Fetch Keywords</h1>

    <form method="post" action="">
        <div class="form-group">
            <label for="jiraId">JIRA ID:</label>
           <input type="text" class="form-control" name="jiraId" id="jiraId" required>
        </div>

        <div class="form-group">
            <label for="inputData">Enter data:</label>
            <textarea class="form-control" name="inputData" id="inputData" rows="5" cols="40" maxlength="524288"></textarea>
        </div>
        <button type="submit" class="btn btn-primary" name="generate">Generate</button>
    </form>

<?php
session_start();

define('LOGS_FOLDER', 'logs');


function sanitizeInput($input) {
    // Remove leading and trailing whitespaces
    $input = trim($input);
    // Convert special characters to HTML entities
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user input
    $jiraId = isset($_POST['jiraId']) ? sanitizeInput($_POST['jiraId']) : '';
    $userData = isset($_POST['inputData']) ? sanitizeInput($_POST['inputData']) : '';

    $keywordsFile = 'Keywords.txt';
    if (!file_exists($keywordsFile)) {
        echo '<p class="mt-4">Keywords file not found.</p>';
    } else {
        $keywordsFile = fopen($keywordsFile, 'r');
        $keywords = array();

        if ($keywordsFile !== false) {
            while (($row = fgets($keywordsFile)) !== false) {
                $data = explode(',', $row);
                $keywords[trim(strtolower($data[0]))] = $data[1];
            }
            fclose($keywordsFile);

            if (isset($_POST["generate"])) {
                $outputFileName = $jiraId . '_output.png';

                $result = analyzeKeywords($keywords, $userData);

                  //  echo '<h2 class="mt-4">Results:</h2>';

                    if (!empty($result)) {
                        echo '<h2 class="mt-4">Below are the security recommendations:</h2>';
                        echo '<table class="table table-bordered table-striped">';
                        echo '<thead class="thead-dark"><tr><th></th><th></th><th></th></tr></thead>';
                        echo '<tbody>';
                        foreach ($result as $keyword => $data) {
                            //    echo "<tr><td>$keyword</td><td>:</td><td>{$data['description']}</td></tr>";
                        }
                        echo '</tbody></table>';

                        generatePngImage($result, $outputFileName);

                        // Update JIRA log
                        updateJiraLog($jiraId, $outputFileName, true, 'JiraLog.txt');
                    } else {
                        echo '<p class="mt-4">No matching keywords found.</p>';

                        // Generate PNG image even when no keywords are found
                        generatePngImage(array(), $outputFileName);

                        // Update JIRA log with no keywords found
                        updateJiraLog($jiraId, $outputFileName, false, 'JiraLog.txt');
                    }
                }
            } else {
                echo '<p class="mt-4">Failed to open Keywords file.</p>';
            }
        }
    }

    function analyzeKeywords($keywords, $userData) {
        $result = array();
        $lowerUserData = strtolower($userData);

        foreach ($keywords as $keyword => $description) {
            $lowerKeyword = strtolower($keyword);
            $lowerPluralKeyword = $lowerKeyword . 's';

            if (preg_match("/\b$lowerKeyword\b/i", $lowerUserData) || preg_match("/\b$lowerPluralKeyword\b/i", $lowerUserData)) {
                if (!in_array($lowerKeyword, array_keys($result))) {
                    $result[$lowerKeyword] = array('description' => $description);
                }
            }
        }
        return $result;
    }

    function generatePngImage($result, $outputFileName) {
        $imageWidth = 1000;
        $imageHeight = 400;
        $image = imagecreate($imageWidth, $imageHeight);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);

        $fontColor = imagecolorallocate($image, 0, 0, 0);
        $font = 'C:\Windows\Fonts\arial.ttf';

        $fontSize = 16;
        $textX = 20;
        $textY = 50;

        foreach ($result as $keyword => $data) {
            $text = "$keyword: {$data['description']}";
            imagettftext($image, $fontSize, 0, $textX, $textY, $fontColor, $font, $text);
            $textY += 30;
        }

      $logsFolderPath = LOGS_FOLDER . DIRECTORY_SEPARATOR;

    if (!is_dir($logsFolderPath)) {
        mkdir($logsFolderPath, 0777, true);
    }

    $outputFilePath = $logsFolderPath . $outputFileName;  
	   
	   
    if (!imagepng($image, $outputFileName)) {
        imagedestroy($image);
        throw new Exception('Failed to save PNG image.');
    }

        $uuid = uniqid();

        $_SESSION['encryptedImagePath'] = base64_encode($outputFileName);
        $_SESSION['uuid'] = $uuid;

        echo "<img src='decrypt.php?path=". base64_encode($outputFileName) . "' alt='Output Image' style='width: 100%;'>";

        imagedestroy($image);
    }

    function displayJiraLog() {
        $jiraLogFile = 'JiraLog.txt';

        $logEntries = [];
        if (file_exists($jiraLogFile)) {
            $logEntries = file($jiraLogFile, FILE_IGNORE_NEW_LINES);
        }

        $count = min(10, count($logEntries));
        for ($i = 0; $i < $count; $i++) {
            list($jiraId, $imagePath, $keywordsFound) = explode(',', $logEntries[$i]);

            // Display JIRA ID as a hyperlink to the corresponding image
            echo '<a href="decrypt.php?path=' . base64_encode($imagePath) . '">' . $jiraId . '</a>';

            // Add a note indicating whether keywords were found or not
            if ($keywordsFound) {
                echo ' ';
            } else {
                echo ' ';
            }

            // Add a comma and space after each entry
            if ($i < $count - 1) {
                echo ', ';
            }
        }
    }

    function updateJiraLog($jiraId, $outputFileName, $keywordsFound, $jiraLogFile) {
        $logEntries = [];

        if (file_exists($jiraLogFile)) {
            $logEntries = file($jiraLogFile, FILE_IGNORE_NEW_LINES);
        }

        // Add the new JIRA ID, base64-encoded image path, and whether keywords were found to the log
        array_unshift($logEntries, "$jiraId," . base64_encode($outputFileName) . ",$keywordsFound");
        $logEntries = array_slice($logEntries, 0, 10);

        file_put_contents($jiraLogFile, implode(PHP_EOL, $logEntries));
    }
    ?>
</body>
</html>
