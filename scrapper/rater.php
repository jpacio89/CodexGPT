<?php

// NOTE:
// STEP 5: calculate citation scores for every paper given a certain depth

$docs = getArxivDocs();
$graph = getCitations();
$level = 15;

$listSize = 0;

foreach ($docs as $doc) {
    $omid = $doc['omid'];
    $title = $doc['title'];

    $list = countDescendants($graph, $omid, $level);
    $count = count($list);

    if ($count > 0) {
      $listSize++;
      echo "$title -> $count" . PHP_EOL;
    }
}

echo PHP_EOL . "List Size: " . $listSize . PHP_EOL;

function countDescendants($graph, $omid, $level) {
    if (!isset($graph[$omid])) {
        return [];
    }
    
    if ($level <= 0) {
      return $graph[$omid];
    }

    $list = [];

    foreach ($graph[$omid] as $child) {
      $list = array_merge($list, countDescendants($graph, $child, $level - 1));  
    }

    $list = array_unique($list);
    
    return $list;
}

function getCitations() {
    $graph = [];
    $handle = fopen('./data/citations.jsons', "r");

    while (($line = fgets($handle)) !== false) {
        $citation = json_decode($line, true);
        $graph[$citation[0]][] = $citation[1];
    }

    fclose($handle);
    return $graph;
}

function getArxivDocs() {
    $filename = './data/docs-with-omid.jsons';
    $handle = fopen($filename, "r");
    $docs = [];

    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $decodedLine = json_decode($line, true);

            if ($decodedLine === null) {
                echo "Error decoding JSON: " . json_last_error_msg() . "\n";
            } else {
                $docs[] = $decodedLine;
            }
        }
    }

    return $docs;
}

?>
