<?php

$outputDir = '/Volumes/Alpha/data/docs';
$filename = './data/search.jsons';
$batchSize = 10; // Number of lines to read and process at a time

// Initialize cURL multi handle
$mh = curl_multi_init();

// Open the file for reading
$handle = fopen($filename, "r");

if (!$handle) {
    die("Unable to open file");
}

$id = -1;

$start = @file_get_contents('./data/last-docs-start.log');

if (!$start) {
    $start = 0;
} else {
    $start++;
}

while (!feof($handle)) {
    $handles = [];
    $lines = [];
    $count = 0;

    // Read up to batchSize lines from the file
    while ($count < $batchSize && ($line = fgets($handle)) !== false) {
        $id++;

        if ($id < $start) {
            echo "Skipping $id \n";
            continue;
        }
        
        $decodedLine = json_decode($line, true);
        $tree = implode('/', [
          mt_rand(0,50),
          mt_rand(0,50),
          mt_rand(0,50),
          mt_rand(0,50)
        ]);
        $decodedLine['id'] = $id;
        $decodedLine['tree'] = $tree;

        if ($decodedLine === null) {
            echo "Error decoding JSON: " . json_last_error_msg() . "\n";
            continue;
        }

        $ePrint = $decodedLine['e-print'];
        $ch = curl_init($ePrint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
            CURLOPT_ENCODING => '',
            CURLOPT_HTTPHEADER => [
              'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
              'Accept-Language: pt-PT,pt;q=0.9,en-US;q=0.8,en;q=0.7,sv;q=0.6',
              'Cache-Control: no-cache',
              'Connection: keep-alive',
            ],
        ]);
        curl_multi_add_handle($mh, $ch);
        $handles[] = $ch;
        $lines[] = $decodedLine;
        $count++;
    }

    // Check for active transfers and execute them
    do {
        curl_multi_exec($mh, $active);
    } while ($active);

    // Process the responses
    foreach ($handles as $index => $ch) {
        $output = curl_multi_getcontent($ch);

        if (!$output) {
            echo "Something is fishy!!\n";
            sleep(5);
        }
        
        echo $output . PHP_EOL;
        
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);

        $decodedLine = $lines[$index];
        $directoryPath = $outputDir . '/' . $decodedLine['tree'];

        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, 0777, true);
        }

        $id = $decodedLine['id'];
        
        echo "Saving $id \n";

        $filePath = $directoryPath . '/' . $id . '.paper';            
        file_put_contents($filePath, $output);
        file_put_contents('./data/docs.jsons', json_encode($decodedLine) . PHP_EOL, FILE_APPEND);
        file_put_contents('./data/last-docs-start.log', $id);
    }
}

// Close the multi handle and file
curl_multi_close($mh);
fclose($handle);

?>
