<?php

function fetchLandingPages($directory) {
    $landingPages = [];
    $files = scandir($directory);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && is_dir($directory . '/' . $file)) {
            $landingPages[] = $file;
        }
    }
    return $landingPages;
}

$landingPages = fetchLandingPages(__DIR__);

// Сортування лендінгів за кодом країни
usort($landingPages, function($a, $b) {
    return substr($a, 0, 2) <=> substr($b, 0, 2);
});

// Групування лендінгів за країнами
$groupedByCountry = [];
foreach ($landingPages as $page) {
    $countryCode = substr($page, 0, 2);
    if (!isset($groupedByCountry[$countryCode])) {
        $groupedByCountry[$countryCode] = [];
    }
    $groupedByCountry[$countryCode][] = $page;
}

echo json_encode($groupedByCountry);
?>