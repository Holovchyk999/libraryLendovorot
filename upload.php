<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $countryCode = $_POST['countryCode'];
    $landingName = $_POST['landingName'];
    $zipFile = $_FILES['zipFile'];

    $targetDir = __DIR__ . '/';
    $targetFile = $targetDir . basename($zipFile['name']);

    if (move_uploaded_file($zipFile['tmp_name'], $targetFile)) {
        $zip = new ZipArchive;
        if ($zip->open($targetFile) === TRUE) {
            $extractPath = $targetDir . $countryCode . '.' . $landingName;
            $zip->extractTo($extractPath);
            $zip->close();

            // Remove the uploaded ZIP file
            unlink($targetFile);

            // Remove __MACOSX directory if it exists
            $macosxDir = $extractPath . '/__MACOSX';
            if (is_dir($macosxDir)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($macosxDir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($files as $fileinfo) {
                    $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                    $todo($fileinfo->getRealPath());
                }

                rmdir($macosxDir);
            }

            // Move files from the extracted directory to target directory if there's a subdirectory
            $extractedFiles = scandir($extractPath);
            foreach ($extractedFiles as $file) {
                if ($file != '.' && $file != '..') {
                    $filePath = $extractPath . '/' . $file;
                    if (is_dir($filePath)) {
                        $subFiles = scandir($filePath);
                        foreach ($subFiles as $subFile) {
                            if ($subFile != '.' && $subFile != '..') {
                                rename($filePath . '/' . $subFile, $extractPath . '/' . $subFile);
                            }
                        }
                        rmdir($filePath);
                    }
                }
            }

            echo json_encode(['status' => 'success', 'message' => 'Завантаження пройшло успішно']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Не вдалося відкрити ZIP файл']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Не вдалося завантажити файл']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Неправильний метод запиту']);
}
?>
