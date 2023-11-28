<?php
$filename = './data/search.jsons';
$handle = fopen($filename, "r");
$i = 1;

if ($handle) {
    while (($line = fgets($handle)) !== false) {
        sleep(5);

        // Decode each line into a PHP array
        $decodedLine = json_decode($line, true);

        if ($decodedLine === null) {
            echo "Error decoding JSON: " . json_last_error_msg() . "\n";
        } else {
            // Process the PHP array as needed
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

            echo $output;
            $uuid = generateUUIDv4();
            file_put_contents('./data/papers/' . $uuid . '.paper', $output);


            // Define the pattern to search for '\paper{...}'
            $pattern = '/\\\\paper\s(.*)/';

            // Array to hold matches
            $matches = [];

            // Search for all matches
            preg_match_all($pattern, $output, $matches);

            // $matches[1] will contain all the 'some_text' parts from '\paper{some_text}'
            foreach ($matches[1] as $match) {
                echo "Found: " . $match . "\n";
            }
            
            echo "\n\n\n\n\n";
        }
    }
}

function generateUUIDv4() {
    $data = openssl_random_pseudo_bytes(16);

    // Set version to 0100 (UUID version 4)
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

?>
