<?php
// API endpoint
$url = 'https://demo.defectdojo.org/api/v2/tests/?test_type=84&limit=800000';

// Set up cURL
$ch = curl_init($url);
$headers = array(
    'Authorization: Token d614279a2728c0ace637752c9aa1e4c6e484119d',
    'Content-Type: application/json'
);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the cURL request
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for errors
if ($response === false || $http_status != 200) {
    echo 'Error fetching data: ' . curl_error($ch);
    exit;
}

// Close cURL
curl_close($ch);

// Decode JSON response
$data = json_decode($response, true);

// Check if data is empty
if (empty($data['results'])) {
    echo 'No data found.';
    exit;
}

// Extract unique dates from the dataset for the "Created" column
$createdDates = array();
foreach ($data['results'] as $test) {
    $createdDates[date('Y-m-d', strtotime($test['created']))] = true;
}
$createdDates = array_keys($createdDates);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Data</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #dataTable th,
        #dataTable td {
            max-width: 200px;
            white-space: normal;
            word-wrap: break-word;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <table class="table table-bordered" id="dataTable">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Test Type</th>
                    <th>Scan Type</th>
                    <th>Target Start</th>
                    <th>Target End</th>
                    <th>Percent Complete</th>
                    <th>Updated</th>
                    <th>Created</th>
                    <th>Engagement</th>
                    <th>Environment</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['results'] as $test) : ?>
                    <tr>
                        <td><?= $test['id'] ?></td>
                        <td><?= $test['test_type_name'] ?></td>
                        <td><?= $test['scan_type'] ?></td>
                        <td><?= date('Y-m-d', strtotime($test['target_start'])) ?></td>
                        <td><?= date('Y-m-d', strtotime($test['target_end'])) ?></td>
                        <td><?= $test['percent_complete'] ?></td>
                        <td><?= date('Y-m-d', strtotime($test['updated'])) ?></td>
                        <td><?= date('Y-m-d', strtotime($test['created'])) ?></td>
                        <td><?= $test['engagement'] ?></td>
                        <td><?= $test['environment'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
