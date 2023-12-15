<?php

// NOTE:
// STEP 4: for all papers in the docs-with-omid.jsons file, determine which papers cite them an store in citations.jsons

//$directory = '/Volumes/Alpha/data/citation_metadata/csv';
$directory = '/Volumes/Beta/data/citations/csv';
$papers = parseCsvFiles($directory);
print_r($papers);

function getArxivDocs($citations) {
  $filename = './data/docs-with-omid-full.jsons';
  $handle = fopen($filename, "r");
  $docs = [];
  $buffer = [];

  if ($handle) {
    while (($line = fgets($handle)) !== false) {
      $decodedLine = json_decode($line, true);
  
      if ($decodedLine === null) {
        echo "Error decoding JSON: " . json_last_error_msg() . "\n";
      } else if (isset($citations[$decodedLine['omid']])) {
        $cited = $decodedLine['omid'];
        $citings = array_keys($citations[$cited]);

        foreach ($citings as $citing) {
          $buffer[] = [$cited, $citing];
        }
      }
    }
  }

  return $buffer;
}


function parseCsvFiles($directory) {
  // Find all CSV files in the specified directory
  $csvFiles = glob($directory . '/*.csv');

  $i = -1;
  $fileCount = count($csvFiles);
  $citationCount = 0;
  $citations = [];

  $filesMap = getProgress();
  $fileBuffer = [];

  foreach ($csvFiles as $file) {
      $i++;
      echo 'File ' . $i . '/' . $fileCount . PHP_EOL;

      if (isset($filesMap[$file])) {
        continue;
      }

      // Open the CSV file
      if (($handle = fopen($file, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
              // Extract title and omid
              $citing = $data[1] ?? '';
              $cited = $data[2] ?? '';
              $isJournal = $data[5] ?? '';
              $isAuthor = $data[6] ?? '';

              $citations[$cited][$citing] = true;

              if ($citationCount % 1000000 === 0) {
                echo 'Reference: ' . $citationCount . PHP_EOL;
              }

              if ($citationCount % 100000 === 0) {
                $miniBuffer = getArxivDocs($citations);
                $fileBuffer = array_merge($fileBuffer, $miniBuffer);
                $citations = [];

                //foreach ($fileBuffer as $row) {
                //  echo $row[0] . ' -> ' . $row[1] . PHP_EOL;        
                //  file_put_contents('./data/citations-full.jsons', json_encode($row) . PHP_EOL, FILE_APPEND);
                //}
                //$fileBuffer = [];
              }

              $citationCount++;
          }

          fclose($handle);
      }

      foreach ($fileBuffer as $row) {
        echo $row[0] . ' -> ' . $row[1] . PHP_EOL;     
        file_put_contents('./data/citations-full.jsons', json_encode($row) . PHP_EOL, FILE_APPEND);
      }

      file_put_contents('./data/citations-full.progress', $file . PHP_EOL, FILE_APPEND);
      $fileBuffer = [];
  }

  if (count(array_keys($citations)) > 0) {
    getArxivDocs($citations);
    $citations = [];
  }

  return $citations;
}

function getProgress() {
  $text = file_get_contents('./data/citations-full.progress');
  $files = explode("\n", $text);
  $filesMap = [];

  foreach ($files as $file) {
    $filesMap[$file] = true;
  }

  return $filesMap;
}

?>