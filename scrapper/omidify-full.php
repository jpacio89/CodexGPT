<?php

// NOTE:
// STEP 3: add the pub_date and omid to the json data gathered in step 1 and store in docs-with-omid.jsons

$directory = '/Volumes/Beta/data/papers-metadata';
$papers = parseCsvFiles($directory);
print_r($papers);

function getArxivDocs($papers) {
  $filename = './data/search.jsons';
  $handle = fopen($filename, "r");
  $doc = false;

  if ($handle) {
    while (($line = fgets($handle)) !== false) {
      $decodedLine = json_decode($line, true);
      $lcTitle = strtolower($decodedLine['title']);

      if ($decodedLine === null) {
        echo "Error decoding JSON: " . json_last_error_msg() . "\n";
        continue;
      } else if (isset($papers[$lcTitle])) {
        $merged = array_merge($papers[$lcTitle], $decodedLine);
        unset($merged['titleLowerCase']);
        print_r($merged);
        file_put_contents('./data/docs-with-omid-full.jsons', json_encode($merged) . PHP_EOL, FILE_APPEND);
      }
    }
    fclose($handle);
    return $doc;
  }

  return false;
}


function parseCsvFiles($directory) {
  $papers = [];

  // Find all CSV files in the specified directory
  $csvFiles = glob($directory . '/*.csv');

  $i = 0;
  $fileCount = count($csvFiles);
  $paperCount = 0;

  foreach ($csvFiles as $file) {
      echo 'File ' . $i . '/' . $fileCount . PHP_EOL;    

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
                'titleLowerCase' => strtolower($title),
                'omid' => $omid,
                'pub_date' => $pub_date,
              ];

              $papers[$paper['titleLowerCase']] = $paper;

              if ($paperCount % 200000 === 0) {
                getArxivDocs($papers);
                $papers = [];
              }

              $paperCount++;
          }

          // echo 'Paper count: ' . $paperCount . PHP_EOL;
          fclose($handle);
      }

      $i++;
  }

  if (count(array_keys($papers)) > 0) {
    getArxivDocs($papers);
    $papers = [];
  }

  return $papers;
}

?>