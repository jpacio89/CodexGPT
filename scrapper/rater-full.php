<?php
ini_set('memory_limit', '2048M');

// NOTE:
// STEP 5: calculate citation scores for every paper given a certain depth

$docs = getArxivDocs();
$graph = getCitations();
$maxLevel = 5;

$listSize = 0;
$docKeys = array_keys($docs);

foreach ($docKeys as $docKey) {
    $doc = $docs[$docKey];
    $omid = $doc['omid'];
    $title = $doc['title'];
    $cache = [];

    echo $listSize . ': ' . $doc['title'] . PHP_EOL;

    for ($i = 1; $i <= $maxLevel; ++$i) {
        $list = countDescendants($graph, $omid, $i);

        /*foreach ($list as $citingOmid) {
            echo '  ' . $citingOmid . PHP_EOL;
            findDocById($docs, $citingOmid);
        }*/

        $counts[$i] = count($list);
    }

    $doc['scores'] = $counts;  

    $listSize++;
    echo PHP_EOL;
    file_put_contents('./data/scores.jsons', json_encode($doc) . PHP_EOL, FILE_APPEND);
}

echo PHP_EOL . "List Size: " . $listSize . PHP_EOL;

function countDescendants($graph, $omid, $level) {
    global $cache;
    $maxChildren = 10000;

    if (isset($cache[$omid . '-' . $level])) {
        return $cache[$omid . '-' . $level];
    }

    if (!isset($graph[$omid])) {
        return [];
    }
    
    if ($level <= 0) {
      return $graph[$omid];
    }

    $list = [];

    foreach ($graph[$omid] as $child) {
        $list = array_merge($list, countDescendants($graph, $child, $level - 1)); 
        $list = array_unique($list);

        if (count($list) > $maxChildren) {
            $list = array_slice($list, 0, $maxChildren);
            break;
        }
    }

    $cache[$omid . '-' . $level] = $list;
    return $list;
}

function getCitations() {
    $graph = [];
    $handle = fopen('./data/citations-full.jsons', "r");

    while (($line = fgets($handle)) !== false) {
        $citation = json_decode($line, true);
        $graph[$citation[0]][] = $citation[1];
    }

    fclose($handle);
    return $graph;
}

function getArxivDocs() {
    $filename = './data/docs-with-omid-full.jsons';
    $handle = fopen($filename, "r");
    $docs = [];

    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $decodedLine = json_decode($line, true);

            if ($decodedLine === null) {
                echo "Error decoding JSON: " . json_last_error_msg() . "\n";
            } else {
                $docs[$decodedLine['omid']] = $decodedLine;
            }
        }
    }

    return $docs;
}

function findDocById(&$docs, $omid) {
    if (isset($doc[$omid])) {
        echo '    ' . $doc[$omid]['title'] . PHP_EOL;
    }
}

?>
