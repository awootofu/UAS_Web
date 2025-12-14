<?php

require __DIR__ . '/vendor/autoload.php';

// Test periode generation
$year = 2025;
$month = 12; // December

// Genap semester (July-December)
if ($month > 6) {
    $semester = 'genap';
    $startYear = $year;
    $endYear = $year + 1;
} else {
    $semester = 'ganjil';
    $startYear = $year - 1;
    $endYear = $year;
}

$periode = sprintf('%d/%d %s', $startYear, $endYear, $semester);
echo "Current Periode: " . $periode . "\n";

// Test periode options generation
$options = [];
$currentYear = 2025;

for ($i = 0; $i < 6; $i++) {
    $startYear = $currentYear - 1 + floor($i / 2);
    $endYear = $startYear + 1;
    $semester = ($i % 2 == 0) ? 'ganjil' : 'genap';
    
    $periode = sprintf('%d/%d %s', $startYear, $endYear, $semester);
    $options[$periode] = $periode;
}

echo "\nPeriode Options:\n";
foreach ($options as $option) {
    echo "- " . $option . "\n";
}
