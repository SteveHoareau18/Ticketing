<?php
$xml = simplexml_load_file('junit.xml');
$csv = fopen('test_results.csv', 'w');

fputcsv($csv, ['Test Name', 'Status']);

foreach ($xml->xpath('//testcase') as $testcase) {
    $testName = $testcase['name'];
    $status = 'Passed';

    if ($testcase->failure) {
        $status = 'Failed';
    } elseif ($testcase->error) {
        $status = 'Error';
    } elseif ($testcase->skipped) {
        $status = 'Skipped';
    }

    fputcsv($csv, [$testName, $status]);
}

fclose($csv);

echo "CSV file 'test_results.csv' has been created.\n";

// Afficher le contenu du CSV
$content = file_get_contents('test_results.csv');
echo $content;
