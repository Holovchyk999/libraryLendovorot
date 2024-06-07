<?php
if (!isset($_GET['page'])) {
    die("No page specified.");
}

$page = $_GET['page'];
$directory = __DIR__ . '/' . $page;

if (!is_dir($directory)) {
    die("Invalid page specified.");
}

$indexPath = $directory . '/index.html';

if (!file_exists($indexPath)) {
    die("Index file not found.");
}

$newFormContent = <<<HTML
<form action="api.php" method="post" id="dynamicForm" class="caffForm was-validated">
    <input type="hidden" name="subid" value="{subid}">
    <input type="hidden" name="pixel" value="{pixel}">
    <input type="hidden" name="country" value="">
    <input type="hidden" name="dialCode" value="">
    <div class="loader d-none" id="loader">
        <img src="./ForForm/img/loader.gif" alt="Loading..." />
    </div>
    <div class="row">
        <input id="firstName" name="first_name" style="margin-top: 17px !important;" type="text" placeholder="Іме" class="" >
        <input id="lastName" name="last_name" style="margin-top: 17px !important;" type="text" placeholder="Презиме" class="" >
        <input id="email" name="email" type="email" style="margin-top: 17px !important;" placeholder="Email" class="" >
        <div style="padding: 0;">
            <div class="form-group">
                <input id="phoneFiled" name="phone" style="margin-top: 7px !important;" type="tel" placeholder="912 945 945" class=" phone" >
            </div>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="ageCheck" name="ageCheck" required>
            <label class="form-check-label" for="ageCheck">I am over 18 years old.</label>
        </div>
        <div class="form-check">
            <input style="width: 20px;" type="checkbox" class="form-check-input" id="callCheck" name="callCheck" required>
            <label class="form-check-label" for="callCheck">I agree to receive a call from a manager within 24 hours.</label>
        </div>
        <button id="submitBtn" type="submit" class="">SEND</button>
    </div>
</form>
HTML;

$headContent = <<<HTML
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
<link rel="stylesheet" href="./ForForm/css/styleForLoader.css">
<link rel="stylesheet" href="./ForForm/css/styleForm.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
    window.bgdataLayer = window.bgdataLayer || [];
    function bge(){bgdataLayer.push(arguments);}
    bge('init', "{pixel}");
</script>
<script async src="https://api.imotech.video/ad/events.js?pixel_id={pixel}"></script>
<script>
    var date = new Date();
    date.setTime(date.getTime() + (5 * 24 * 60 * 60 * 1000));
    if (!'{pixel}'.match('{')) {
      document.cookie = "pixel={pixel}; " + "expires=" + date.toUTCString() + "";
    }
</script>
HTML;

$bodyContent = <<<HTML
<!-- Завантаження jQuery Validate -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>
<!-- Завантаження intl-tel-input -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<!-- Завантаження utils для intl-tel-input -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>
<!-- Завантаження перекладів -->
<script src="./ForForm/js/translateForm.js"></script>
<!-- Завантаження кастомного скрипта -->
<script src="./ForForm/js/scriptForm.js"></script>
HTML;

$indexContent = file_get_contents($indexPath);

// Видаляємо стару форму і вставляємо нову на те саме місце
$indexContent = preg_replace('/<form.*?<\/form>/is', $newFormContent, $indexContent);

// Вставляємо нові стилі і скрипти перед закриваючим тегом </head>
$indexContent = preg_replace('/<\/head>/', $headContent . '</head>', $indexContent, 1);

// Вставляємо нові скрипти перед закриваючим тегом </body>
$indexContent = preg_replace('/<\/body>/', $bodyContent . '</body>', $indexContent, 1);

// Зберігаємо оновлений файл
file_put_contents($indexPath, $indexContent);

echo "Form and head content replaced successfully in $indexPath.";
?>
