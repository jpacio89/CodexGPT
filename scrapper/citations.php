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
            $citationLink = str_replace('https://arxiv.org/abs/', 'https://api.semanticscholar.org/arXiv:', $link);
            $encodedTitle = urlencode('"' . $title . '"');
            $searchUrl = "https://scholar.google.com/scholar?hl=en&as_sdt=0%2C5&q=" . $encodedTitle . "&btnG=";

            echo "Searching... " . $citationLink . PHP_EOL;

            //$search = fetch_url_with_curl('https://ui.adsabs.harvard.edu/abs/arXiv:2303.03745');
            $search = fetch_url_with_curl('https://ui.adsabs.harvard.edu/search/q=references(bibcode%3A2023arXiv230303745M)&sort=first_author%20asc%2C%20bibcode%20asc&p_=0');
            

            file_put_contents('./test.html', $search);

            if ($search) {
                // Load the HTML content into DOMDocument
                $dom = new DOMDocument();
                @libxml_use_internal_errors(true);
                $dom->loadHTML($search);
                libxml_clear_errors();
    
                // Create a DOMXPath instance
                $xpath = new DOMXPath($dom);
    
                // Query for the canonical link element
                $canonicalLink = $xpath->query("//link[@rel='canonical']")->item(0);
    
                if ($canonicalLink) {
                    $href = $canonicalLink->getAttribute('href');

                    // Extract the ID from the href attribute
                    $parts = explode('/', $href);
                    $id = $parts[count($parts) - 2];
    
                    echo "ID: " . $id . "\n";
                } else {
                    echo "Canonical link not found.\n";
                }
            } else {
                echo "Failed to fetch data, sleeping for 60 seconds.\n";
                sleep(60);
            }

            //echo $search . PHP_EOL;
            die();
            /*if (!$search) {
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

                    echo "Title: " . $title . "\n";
                    echo "Citations: " . $citations . "\n";
                    echo "Citations Link: " . $href . "\n\n";
                }
            }

            sleep(5);*/
        }
    }

    fclose($handle);
} else {
    echo "Error opening the file.";
}

function fetch_url_with_curl($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
        'Accept-Language: pt-PT,pt;q=0.9,en-US;q=0.8,en;q=0.7,sv;q=0.6',
        'Cache-Control: no-cache',
        'Pragma: no-cache',
        'Referer: https://scholar.google.com/schhp?hl=en&as_sdt=2005&sciodt=0,5',
        'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36'
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch);
        $response = false;
    }

    curl_close($ch);

    return $response;
}

?>
