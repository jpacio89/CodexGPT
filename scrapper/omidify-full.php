<?php

// NOTE:
// STEP 3: add the pub_date and omid to the json data gathered in step 1 and store in docs-with-omid.jsons

$docs = getArxivDocs();

$directory = '/Volumes/Beta/data/papers-metadata';
$papers = parseCsvFiles($directory, $docs);
print_r($papers);

function getArxivDocs() {
  $filename = './data/search.jsons';
  $handle = fopen($filename, "r");
  $docs = [];

  if ($handle) {
    while (($line = fgets($handle)) !== false) {
      $decodedLine = json_decode($line, true);
  
      if ($decodedLine === null) {
        echo "Error decoding JSON: " . json_last_error_msg() . "\n";
      } else {
        $docs[strtolower($decodedLine['title'])] = $decodedLine;
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
              $title = $data[1] ?? '';
              $pub_date = $data[7] ?? '';
              $omid = '';

              if (preg_match('/omid:([a-zA-Z0-9\/]+)/', $data[0], $matches)) {
                  $omid = 'omid:' . $matches[1];
              }

              // Add to papers array
              $paper = [
                'title' => $title,
                'omid' => $omid,
                'pub_date' => $pub_date,
              ];

              $lowerTitle = strtolower($paper['title']);

              if (isset($docs[$lowerTitle])) {
                $docs[$lowerTitle]['omid'] = $paper['omid'];
                $docs[$lowerTitle]['pub_date'] = $paper['pub_date'];
                //$papers[] = $docs[$lowerTitle];
                print_r($paper);
                file_put_contents('./data/docs-with-omid.jsons', json_encode($docs[$lowerTitle]) . PHP_EOL, FILE_APPEND);
              }

              $paperCount++;
          }

          // echo 'Paper count: ' . $paperCount . PHP_EOL;
          fclose($handle);
      }

      $i++;
  }

  return $papers;
}

?>