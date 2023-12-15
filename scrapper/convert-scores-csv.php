<?php

function convertJsonToCsv($inputFile, $outputFile) {
  $handle = fopen($inputFile, "r");
  if (!$handle) {
      die("Failed to open the input file");
  }

  $output = fopen($outputFile, "w");
  if (!$output) {
      die("Failed to open the output file");
  }

  $headersWritten = false;
  $headers = [];

  while (($line = fgets($handle)) !== false) {
      $data = json_decode($line, true);
      if (json_last_error() !== JSON_ERROR_NONE) {
          echo "Error decoding JSON: " . json_last_error_msg() . "\n";
          continue;
      }

      // Handling the scores array
      if (isset($data['scores']) && is_array($data['scores'])) {
          foreach ($data['scores'] as $key => $value) {
              $scoreHeader = "score-depth-$key";
              $data[$scoreHeader] = $value;
              if (!in_array($scoreHeader, $headers)) {
                  $headers[] = $scoreHeader;
              }
          }
          unset($data['scores']); // Remove the original scores array
      }

      if (!$headersWritten) {
          // Combine with the keys from the first data row
          $headers = array_merge(array_keys($data));
          fputcsv($output, $headers);
          $headersWritten = true;
      }

      // Ensure all rows have the same columns in the same order
      $rowData = [];
      foreach ($headers as $header) {
          $rowData[] = $data[$header] ?? ''; // Use empty string for missing values
      }

      fputcsv($output, $rowData);
  }

  fclose($handle);
  fclose($output);
}

// Usage
$inputFile = './data/scores-max=10000-date=15_12_2023.jsons';
$outputFile = './data/scores-max=10000-date=15_12_2023.csv';
convertJsonToCsv($inputFile, $outputFile);


?>