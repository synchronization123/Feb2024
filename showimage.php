<html>
<head>
    <title>Fetch Keywords</title>
    <link rel="stylesheet" href="bootstrap.min.css">
</head>

<body class="container mt-5">
    <h1 class="mb-4">Fetch Keywords</h1>

    <form method="post" action="" id="keywordForm">
        <div class="form-group">
            <label for="inputData">Enter data:</label>
            <textarea class="form-control" name="inputData" id="inputData" rows="5" cols="40" maxlength="524288"></textarea>
        </div>
        <button type="button" class="btn btn-primary" name="generate" onclick="generateKeywords()">Generate</button>
    </form>

    <!-- Modal -->
    <div class="modal fade" id="outputModal" tabindex="-1" role="dialog" aria-labelledby="outputModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="outputModalLabel">Security Recommendations</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="outputModalBody">
                    <!-- Output content will be displayed here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // ... (your existing PHP code)

        if (isset($_POST["generate"])) {
            // Get user input data
            $userData = isset($_POST['inputData']) ? $_POST['inputData'] : '';

            // Analyze keywords in user input
            $result = analyzeKeywords($keywords, $userData);

            // Display results in the modal
            echo '<script>';
            echo 'function generateKeywords() {';
            echo 'var outputModal = document.getElementById("outputModal");';
            echo 'var outputModalBody = document.getElementById("outputModalBody");';
            echo 'outputModalBody.innerHTML = "";';

            if (!empty($result)) {
                echo 'outputModalBody.innerHTML += "<h2>Below are the security recommendations:</h2>";';
                echo 'outputModalBody.innerHTML += "<table class=\'table table-bordered table-striped\'>";';
                echo 'outputModalBody.innerHTML += "<thead class=\'thead-dark\'><tr><th></th><th></th><th></th></tr></thead>";';
                echo 'outputModalBody.innerHTML += "<tbody>";';
                foreach ($result as $keyword => $data) {
                    echo 'outputModalBody.innerHTML += "<tr><td>' . $keyword . '</td><td>:</td><td>' . $data['description'] . '</td></tr>";';
                }
                echo 'outputModalBody.innerHTML += "</tbody></table>";';
                echo '}';
                echo 'else {';
                echo 'outputModalBody.innerHTML = "<p>No matching keywords found.</p>";';
                echo '}';
                echo '$("#outputModal").modal("show");'; // Show the modal
                echo '}';
                echo '</script>';
            }
        }
    }
    ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
