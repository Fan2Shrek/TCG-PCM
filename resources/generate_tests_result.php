#!/usr/bin/env php
<?php

$args = $_SERVER['argv'];

if (count($args) < 2) {
	echo "Usage: php generate_tests_result.php <test_results_file>\n";
	exit(1);
}

$coverageFile = 'api/var/coverage.xml';
$previousResultsFile = $args[1];;

exec('make tests-ci', $results, $code);

if ($code !== 0) {
	echo "Error running tests: " . implode("\n", $results) . "\n";
	exit(1);
}

$results = join("\n", $results);

preg_match('/(\d+)\s+tests/', $results, $matchesTests);
$testsCount = $matchesTests[1] ?? 0;

preg_match('/(\d+)\s+assertions/', $results, $matchesAssertions);
$assertionsCount = $matchesAssertions[1] ?? 0;

preg_match('/Time:\s+(\d{2}:\d{2}\.\d{3})/', $results, $matchesTime);
$time = $matchesTime[1] ?? '00:00.000';

$coverageXml = simplexml_load_file($coverageFile);

$totalElements = 0;
$coveredElements = 0;

$elementsList = [];
foreach ($coverageXml->xpath('//metrics') as $metric) {
    $elementsList[] = [
        'elements' => (int)$metric['elements'],
        'coveredelements' => (int)$metric['coveredelements']
    ];
}

$last = array_slice($elementsList, -1);
$totalElements = $last[0]['elements'];
$coveredElements = $last[0]['coveredelements'];

$coverage = round(($coveredElements / $totalElements) * 100, 2);

### Compute diff ###

$previousState = file_get_contents($previousResultsFile);
$previousData = json_decode($previousState, true);

$testsDiff = $testsCount - ($previousData['tests'] ?? 0);
$assertionsDiff = $assertionsCount - ($previousData['assertions'] ?? 0);
$coverageDiff = round($coverage - ($previousData['coverage'] ?? 0), 2);

$markdown = <<<MD
Duration: $time

| Metric | Current | Previous | Diff |
|--------|---------|----------|------|
| Tests | $testsCount | {$previousData['tests']} | $testsDiff |
| Assertions | $assertionsCount | {$previousData['assertions']} | $assertionsDiff |
| Coverage | $coverage% | {$previousData['coverage']}% | $coverageDiff% |

MD;

echo $markdown;

exit(0);
