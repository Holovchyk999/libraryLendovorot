<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лендоворот</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Лендоворот</h1>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Лендінги</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="upload-tab" data-toggle="tab" href="#upload" role="tab" aria-controls="upload" aria-selected="false">Завантажити Лендінг</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="instructions-tab" data-toggle="tab" href="#instructions" role="tab" aria-controls="instructions" aria-selected="false">Інструкція</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <!-- Лендінги -->
            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                <div class="geo-list mt-4">
                    <!-- Placeholders for country codes -->
                </div>
                <div class="landing-list mt-4">
                    <h3 id="country-heading"></h3>
                    <ul id="landing-container"></ul>
                </div>
            </div>
            <!-- Завантажити Лендінг -->
            <div class="tab-pane fade" id="upload" role="tabpanel" aria-labelledby="upload-tab">
                <div class="upload-form mt-4">
                    <h3>Завантажити новий лендінг</h3>
                    <form id="uploadForm" action="upload.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="countryCode">Код країни</label>
                            <input type="text" class="form-control" id="countryCode" name="countryCode" required>
                        </div>
                        <div class="form-group">
                            <label for="landingName">Назва лендінгу</label>
                            <input type="text" class="form-control" id="landingName" name="landingName" required>
                        </div>
                        <div class="form-group">
                            <label for="zipFile">Виберіть ZIP файл</label>
                            <input type="file" class="form-control" id="zipFile" name="zipFile" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Завантажити</button>
                    </form>
                    <div id="uploadProgress" class="progress mt-3" style="display: none;">
                        <div id="uploadProgressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                    <p id="uploadStatus" class="mt-3"></p>
                </div>
            </div>
            <!-- Інструкція -->
            <div class="tab-pane fade" id="instructions" role="tabpanel" aria-labelledby="instructions-tab">
                <div class="instructions mt-4">
                    <?php include 'instructions.html'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="downloadModal" tabindex="-1" role="dialog" aria-labelledby="downloadModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="downloadModalLabel">Завантажити Лендінг</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="downloadForm" action="download.php" method="get">
                    <div class="modal-body">
                        <input type="hidden" id="landingPage" name="page">
                        <div class="form-group">
                            <label for="phone_code">Код телефону(gb, sr, pl, ua...) для динамічного коду вводь: auto</label>
                            <input type="text" class="form-control" id="phone_code" name="phone_code" required>
                        </div>
                        <div class="form-group">
                            <label for="gi">gi(тут id твого бокса)</label>
                            <input type="text" class="form-control" id="gi" name="gi" required>
                        </div>
                        <div class="form-group">
                            <label for="mpc_2">MPC_2(сюди вводь свій id баєра)</label>
                            <input type="text" class="form-control" id="mpc_2" name="mpc_2" required>
                        </div>
                        <div class="form-group">
                            <label for="lang_code">Код мови ленда(en, ua, ru...(маленькі букви))</label>
                            <input type="text" class="form-control" id="lang_code" name="lang_code" required>
                        </div>
                        <div class="form-group">
                            <label for="min_digits">Мінімальна кількість цифр в номері</label>
                            <input type="number" class="form-control" id="min_digits" name="min_digits" required>
                        </div>
                        <div class="form-group">
                            <label for="max_digits">Максимальна кількість цифр в номері</label>
                            <input type="number" class="form-control" id="max_digits" name="max_digits" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрити</button>
                        <button type="submit" class="btn btn-primary">Завантажити</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
