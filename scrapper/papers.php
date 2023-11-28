<?php

function fetch_arxiv_results($date, $start) {
    $url = 'https://arxiv.org/search/advanced?advanced=&terms-0-operator=AND&terms-0-term=a&terms-0-field=all&classification-physics_archives=all&classification-include_cross_list=include&date-filter_by=date_range&date-from_date=' . $date . '&date-to_date=' . $date . '&date-date_type=submitted_date&abstracts=show&size=200&order=announced_date_first&start=' . $start;
    
    echo $url . PHP_EOL;

    // Initialize cURL session
    $ch = curl_init($url);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    
    // Execute cURL session
    $html = curl_exec($ch);
    
    // Close cURL session
    curl_close($ch);
    
    // Handle the case where cURL fails
    if ($html === false) {
        return "Error fetching data.";
    }
    
    return $html;
}

function parse_html($html) {
    // Create a new DOMDocument instance
    $dom = new DOMDocument();
    
    // Suppress errors due to malformed HTML
    libxml_use_internal_errors(true);
    
    // Load HTML into the DOMDocument
    $dom->loadHTML($html);
    
    // Clear the errors
    libxml_clear_errors();

    // Create XPath
    $xpath = new DOMXPath($dom);

    // Initialize an array to store the results
    $results = [];

    // Get each paper's listing
    $papers = $xpath->query("//li[contains(@class, 'arxiv-result')]");

    // Iterate through each paper and extract details
    foreach ($papers as $paper) {
        $titleNode = $xpath->query(".//p[contains(@class, 'title is-5')]", $paper)->item(0);
        $linkNode = $xpath->query(".//p[contains(@class, 'list-title')]/a", $paper)->item(0);
        $doiNode = $xpath->query(".//span[contains(@class, 'tag') and contains(text(), 'doi')]/following-sibling::span", $paper)->item(0);
        $abstractNode = $xpath->query(".//p[contains(@class, 'abstract')]", $paper)->item(0);
        $pdfLinkNode = $xpath->query(".//p[contains(@class, 'list-title')]/span/a[contains(@href, '/pdf/')]", $paper)->item(0);

        $title = $titleNode ? trim($titleNode->textContent) : NULL;
        $doi = $doiNode ? trim($doiNode->textContent) : NULL;
        $abstract = $abstractNode ? trim($abstractNode->textContent) : NULL;
        $link = $linkNode ? trim($linkNode->getAttribute('href')) : NULL;
        $pdfLink = $pdfLinkNode ? trim($pdfLinkNode->getAttribute('href')) : NULL;
        $ePrintLink = $pdfLink ? str_replace("/pdf/", "/e-print/", $pdfLink) : NULL;

        if (!$link || !$title) {
          continue;
        }

        $results[] = [
            'title' => $title,
            'doi' => $doi,
            'link' => $link,
            //'abstract' => $abstract,
            'pdf' => $pdfLink,
            'e-print' => $ePrintLink,
        ];
    }

    return $results;
}

// Example usage
$start = @file_get_contents('./data/last-search-start.log');
$date = @file_get_contents('./data/last-search-date.log');

if (!$date) {
    $date = '1991-02'; // Default start date
}

if (!$start) {
  $start = 0; // Default start date
}

while ($date < '2024-01') { // Assuming you want to stop at the end of 2023
    for ($i = 0;; $i++) {
        $html = fetch_arxiv_results($date, $start);
        $paperInfo = parse_html($html);
        
        print_r($paperInfo);
        
        if (count($paperInfo) === 0) {
            break;
        }
        
        // Process or print the paper information
        foreach ($paperInfo as $info) {
            $jsonInfo = json_encode($info);
            file_put_contents('./data/search.jsons', $jsonInfo . PHP_EOL, FILE_APPEND);
        }

        $start += 200; // Assuming 200 is the pagination step
        file_put_contents('./data/last-search-start.log', $start);
    }

    // Increment the month
    $dateObj = DateTime::createFromFormat('Y-m', $date);
    $dateObj->modify('first day of next month');
    $date = $dateObj->format('Y-m');
    file_put_contents('./data/last-search-date.log', $date);
    $start = 0; // Reset start for the new month
}

?>
