<?php

$docs = getArxivDocs();

//$directory = '/Volumes/Alpha/data/citation_metadata/csv';
$directory = '/Volumes/Beta/data/citations/csv';
$papers = parseCsvFiles($directory, $docs);
print_r($papers);

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
        $docs[$decodedLine['omid']] = $decodedLine;
      }
    }
  }

  return $docs;
}


function parseCsvFiles($directory, $docs) {
  $papers = [];

  // Find all CSV files in the specified directory
  $csvFiles = glob($directory . '/*.csv');

  $i = 0;
  $fileCount = count($csvFiles);

  foreach ($csvFiles as $file) {
      echo 'File ' . $i . '/' . $fileCount . PHP_EOL;
      $paperCount = 0;

      // Open the CSV file
      if (($handle = fopen($file, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
              // Extract title and omid
              $citing = $data[1] ?? '';
              $cited = $data[2] ?? '';
              $isJournal = $data[5] ?? '';
              $isAuthor = $data[6] ?? '';

              if (isset($docs[$cited])) {
                $docs[$cited]['citing'][] = $citing;
                echo $cited . ' -> ' . $citing . PHP_EOL;
                //file_put_contents('./data/docs-with-citations.jsons', json_encode($docs));
                file_put_contents('./data/citations.jsons', json_encode([$cited, $citing]) . PHP_EOL, FILE_APPEND);
              }

              $paperCount++;

              if ($paperCount % 1000000 === 0) {
                echo 'Reference: ' . $paperCount . PHP_EOL;
              }
          }

          fclose($handle);
      }

      $i++;
  }

  return $papers;
}

?>