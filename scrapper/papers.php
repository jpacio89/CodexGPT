<?php

function fetch_arxiv_results($start) {
    $url = 'https://arxiv.org/search/?query=a&searchtype=all&abstracts=show&order=announced_date_first&size=200&date-date_type=submitted_date&start=' . $start;
    
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
        $ePrintLink = str_replace("/pdf/", "/e-print/", $pdfLink);

        if (!$link || !$doi) {
          continue;
        }
        
        $results[] = [
            'title' => $title,
            'doi' => $doi,
            'link' => $link,
            'abstract' => $abstract,
            'pdf' => $pdfLink,
            'e-print' => $ePrintLink,
        ];
    }

    return $results;
}

// Example usage
$start = 0;
$max_iterations = 10; // Define how many pages you want to iterate through
for ($i = 0; $i < $max_iterations; $i++) {
    $html = fetch_arxiv_results($start);
    $paperInfo = parse_html($html);
    
    // Process or print the paper information
    foreach ($paperInfo as $info) {
        echo "Title: " . $info['title'] . "\n";
        echo "DOI: " . $info['doi'] . "\n";
        echo "Link: " . $info['link'] . "\n";
        echo "Source: " . $info['e-print'] . "\n\n";
    }

    $start += 200; // Assuming 200 is the pagination step
}

?>
