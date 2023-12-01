<?php

$filename = './data/search.jsons';
$handle = fopen($filename, "r");

if ($handle) {
    while (($line = fgets($handle)) !== false) {
        // Decode each line into a PHP array
        $decodedLine = json_decode($line, true);

        if ($decodedLine === null) {
            echo "Error decoding JSON: " . json_last_error_msg() . "\n";
        } else {
            // Process the PHP array as needed
            $title = $decodedLine['title'];
            $link = $decodedLine['link'];
            $encodedTitle = urlencode('"' . $title . '"');
            $searchUrl = "https://scholar.google.com/scholar?hl=en&as_sdt=0%2C5&q=" . $encodedTitle . "&btnG=";

            //echo "Searching... " . $searchUrl . PHP_EOL;

            $search = fetch_url_with_curl($searchUrl);
            
            file_put_contents('./test.html', $search);

            if (!$search) {
                echo "Failed to fetch data, sleeping for 60 seconds.\n";
                sleep(60);
                continue;
            }

            // Parse the HTML content
            $dom = new DOMDocument();
            @libxml_use_internal_errors(true);
            $dom->loadHTML($search);
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);

            // Find the container with class 'gs_fl gs_flb'
            $containers = $xpath->query("//div[contains(@class, 'gs_fl gs_flb')]");
            if ($containers->length > 0) {
                $container = $containers->item(0);

                // Find anchor tag within this container with 'cites=' in href
                $anchors = $xpath->query(".//a[contains(@href, 'cites=')]", $container);
                if ($anchors->length > 0) {
                    $anchor = $anchors->item(0);
                    $href = $anchor->getAttribute('href');
                    $citedByText = $anchor->textContent;

                    // Parse the number of citations
                    preg_match('/Cited by (\d+)/', $citedByText, $matches);
                    $citations = $matches[1] ?? '0';

                    echo PHP_EOL;
                    echo "Title: " . $title . "\n";
                    echo "Citations: " . $citations . "\n";
                    echo "Citations Link: " . 'https://scholar.google.com/' . $href . PHP_EOL . PHP_EOL;
                }
            }
        }

        sleep(10);
    }

    fclose($handle);
} else {
    echo "Error opening the file.";
}

function fetch_url_with_curl($url) {
    $command = str_replace('$URL', "$url", file_get_contents('./request.data'));
    $output = @shell_exec($command);
    return $output;
}
?>
