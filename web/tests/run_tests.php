<?php

$xml = simplexml_load_file('junit.xml');

if ($xml === false) {
    echo "Erreur: Impossible de charger le fichier junit.xml\n";
    exit(1);
}

$totalTests = (int)$xml['tests'];
$failures = (int)$xml['failures'];
$errors = (int)$xml['errors'];
$skipped = (int)$xml['skipped'];

$passed = $totalTests - $failures - $errors - $skipped;

echo "Résumé des tests :\n";
echo "Total des tests : $totalTests\n";
echo "Réussis : $passed\n";
echo "Échoués : $failures\n";
echo "Erreurs : $errors\n";
echo "Ignorés : $skipped\n";

if ($failures > 0 || $errors > 0) {
    echo "\nTests échoués :\n";
    foreach ($xml->xpath('//testcase[failure or error]') as $testcase) {
        $className = (string)$testcase['class'];
        $testName = (string)$testcase['name'];
        echo "- $className::$testName\n";
    }
    exit(1);
} else {
    echo "\nTous les tests ont réussi !\n";
    exit(0);
}