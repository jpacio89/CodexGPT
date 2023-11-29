<?php
$outputDir = './data/docs';
$filename = './data/search.jsons';
$handle = fopen($filename, "r");
$id = -1;

$start = @file_get_contents('./data/last-docs-start.log');

if (!$start) {
    $start = 0;
} else {
    $start++;
}

if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $id++;

        if ($id < $start) {
            echo "Skipping $id \n";
            continue;
        }

        // Decode each line into a PHP array
        $decodedLine = json_decode($line, true);

        if ($decodedLine === null) {
            echo "Error decoding JSON: " . json_last_error_msg() . "\n";
        } else {
            // Process the PHP array as needed
            $title = $decodedLine['title'];
            $ePrint = $decodedLine['e-print'];

            $command = "curl '".$ePrint."' "
                    . "-H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7' "
                    . "-H 'Accept-Language: pt-PT,pt;q=0.9,en-US;q=0.8,en;q=0.7,sv;q=0.6' "
                    . "-H 'Cache-Control: no-cache' "
                    . "-H 'Connection: keep-alive' "
                    . "-H 'Cookie: browser=85.229.12.213.1701035913605216; arxiv_labs={%22sameSite%22:%22strict%22%2C%22expires%22:365%2C%22last_tab%22:%22tabone%22%2C%22scite-toggle%22:%22enabled%22%2C%22litmaps-toggle%22:%22disabled%22}; arxiv-search-parameters=\"{\\\"order\\\": \\\"announced_date_first\\\"\\054 \\\"size\\\": \\\"200\\\"\\054 \\\"abstracts\\\": \\\"show\\\"\\054 \\\"date-date_type\\\": \\\"submitted_date\\\"}\"' "
                    . "-H 'Pragma: no-cache' "
                    . "-H 'Referer: https://arxiv.org/format/math/9201222' "
                    . "-H 'Sec-Fetch-Dest: document' "
                    . "-H 'Sec-Fetch-Mode: navigate' "
                    . "-H 'Sec-Fetch-Site: same-origin' "
                    . "-H 'Sec-Fetch-User: ?1' "
                    . "-H 'Upgrade-Insecure-Requests: 1' "
                    . "-H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36' "
                    . "-H 'sec-ch-ua: \"Google Chrome\";v=\"119\", \"Chromium\";v=\"119\", \"Not?A_Brand\";v=\"24\"' "
                    . "-H 'sec-ch-ua-mobile: ?0' "
                    . "-H 'sec-ch-ua-platform: \"macOS\"' "
                    . "--compressed";

            $output = shell_exec($command);

            echo $output . PHP_EOL;

            $tree = implode('/', [
                mt_rand(0,50),
                mt_rand(0,50),
                mt_rand(0,50),
                mt_rand(0,50)
            ]);
            $directoryPath = $outputDir . '/' . $tree;

            if (!file_exists($directoryPath)) {
                mkdir($directoryPath, 0777, true);
            }
            
            echo "Saving $id \n";
            $decodedLine['id'] = $id;
            $decodedLine['tree'] = $tree;
            $filePath = $directoryPath . '/' . $id . '.paper';            
            file_put_contents($filePath, $output);
            file_put_contents('./data/docs.jsons', json_encode($decodedLine) . PHP_EOL, FILE_APPEND);
            file_put_contents('./data/last-docs-start.log', $id);

            echo "\n\n\n\n\n";
        }
    }
}

?>
