<?php

$outputDir = '/Volumes/Alpha/data/docs';
$filename = './data/docs.jsons';
$handle = fopen($filename, "r");

if ($handle) {
  while (($line = fgets($handle)) !== false) {
      // Decode each line into a PHP array
      $decodedLine = json_decode($line, true);
  
      echo "> " . $decodedLine['title'] . PHP_EOL;

      $handle2 = fopen($filename, "r");

      while (($line2 = fgets($handle2)) !== false) {
        // Decode each line into a PHP array
        $decodedLine2 = json_decode($line2, true);
        $path = $outputDir . '/' . $decodedLine2['tree'] . '/' . $decodedLine2['id'] . '.paper';
        $content = file_get_contents($path);

        if (strpos(strtolower($content), strtolower($decodedLine['title'])) !== false) {
          // echo "The substring was found.\n";
          echo "    " . $decodedLine['title'] . PHP_EOL;
        }

        //die();
        if ($decodedLine2['id'] % 1000 === 0) {
          echo $decodedLine2['id'] . PHP_EOL;
        }

        // unset($content);
      }
  }
}

?>