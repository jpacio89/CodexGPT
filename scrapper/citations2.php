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

            $command = "curl 'https://scholar.google.com/scholar?hl=pt-PT&as_sdt=0%2C5&q=%22Non+dentable+sets+in+Banach+spaces+with+separable+dual%22&btnG=' \
                -H 'authority: scholar.google.com' \
                -H 'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7' \
                -H 'accept-language: pt-PT,pt;q=0.9,en-US;q=0.8,en;q=0.7,sv;q=0.6' \
                -H 'cache-control: no-cache' \
                -H 'cookie: CONSENT=PENDING+164; SOCS=CAISHwgCEhJnd3NfMjAyMzEwMTktMF9SQzMaBXB0LVBUIAEaBgiAteGpBg; 1P_JAR=2023-11-20-9; GSP=LM=1701114956:S=dLWv9tOStLKKldgZ; SID=dQhTDhQollYCYRsYzSag7sgvZxa9e15CZUH4FQys7_JfM1IJXthlXRbaEMn3kzsbP49lPw.; __Secure-1PSID=dQhTDhQollYCYRsYzSag7sgvZxa9e15CZUH4FQys7_JfM1IJjvlld4nr9hQRdCDIofe5Aw.; __Secure-3PSID=dQhTDhQollYCYRsYzSag7sgvZxa9e15CZUH4FQys7_JfM1IJBxFOY-EVV84jUckMK2NUxg.; HSID=AQIXovGAp--AS4t2_; SSID=A7NOgsMck3REZG2sr; APISID=cIYZp6p_2Lakdc5k/AvKBhGPyvmAqUysOl; SAPISID=YzphLJNCVU1JFfwG/AJVB5080L3lNNJupw; __Secure-1PAPISID=YzphLJNCVU1JFfwG/AJVB5080L3lNNJupw; __Secure-3PAPISID=YzphLJNCVU1JFfwG/AJVB5080L3lNNJupw; AEC=Ackid1SyJZX8M4JLdk_ISdrQ0RQNA3fvyWEACWsniIGpt9wkaqWrb8X3HR0; NID=511=h-LogsXG1lNJr0yl3wEb7HTuA71Hr_s7K6HprAppwFFFZrzHNmdEa3RvyuX3hXBTcJJkL3yEZniTJcBRtNEoMoT0a4yLWO2kB1GSkP04PTi46VYMqf1dezKY4VrAIicYF6IIi2ww2iHQc8IN9gXxc7Y2ZDl4ClFkbOYsq1ovNRknoQvD7k_taYAnDmom48RJ62cG-kP854buZsFEXMV2Y9r6b7NE1mII_VdyztIyNsUaUVDjB3Cn1BFLh7cp7YbRZO1sUI1SRqMEbojtVi2nQIHWP2vPJXu9BrZqjS1VdunFDeLMwGuk2_qgKsQRdtMrBHaAUV2xAb0uUrm6YQPdXKnRjWFgltOWwDcRXjgF0pawYWV4N7lLNK6d5GcNTmRwPW0Q4IMrIgwpwXR2myAhibXJVYeD8rgYpho9pGu4Ijf0cCNrNq3VTpcBqNqAYI0YakF4xHsRqZ3EIKKkYpHzuze9TsF37HqRCiMJUWA5xQS5YBLJW-0rJSusroSqt-ri4fW3GNN8IkTySfoGNAIKwFpp1JE6GrEkSOPNuVqY7Lorw4iVxs6OypomkKYK33KbZkdpLfu5cCLcKL8hXgoWWE1rq1D3q5ONDQ; __Secure-1PSIDTS=sidts-CjIBNiGH7qzlqn3KgtlVL7RG6WMCdumMO1-0aZidzbbgmAOHk0BKNhvHT7AMUFFQA3r-8xAA; __Secure-3PSIDTS=sidts-CjIBNiGH7qzlqn3KgtlVL7RG6WMCdumMO1-0aZidzbbgmAOHk0BKNhvHT7AMUFFQA3r-8xAA; SIDCC=ACA-OxPUcy6wfXqMZ86VJdRHmA3MTLemJZjLeYwehpVxOl78bomSC92f6HFYKBPPJ-SQa-DBunY; __Secure-1PSIDCC=ACA-OxPXQBRzg8ZJuQAbvLRJp_DXBfSqV3eE-dGSrVikNwjbLFaJ28wWkOFuw1bTzj9PUrwjGws; __Secure-3PSIDCC=ACA-OxNJheZYkIlCrLCcYvlnQ8nS9Yb6ct3tPiUhPUtUJyhir1lbHqFuJXhQMInVmYCNGRpyclA' \
                -H 'pragma: no-cache' \
                -H 'referer: https://scholar.google.com/' \
                -H 'sec-ch-ua: \"Google Chrome\";v=\"119\", \"Chromium\";v=\"119\", \"Not?A_Brand\";v=\"24\"' \
                -H 'sec-ch-ua-arch: \"x86\"' \
                -H 'sec-ch-ua-bitness: \"64\"' \
                -H 'sec-ch-ua-full-version-list: \"Google Chrome\";v=\"119.0.6045.159\", \"Chromium\";v=\"119.0.6045.159\", \"Not?A_Brand\";v=\"24.0.0.0\"' \
                -H 'sec-ch-ua-mobile: ?0' \
                -H 'sec-ch-ua-model: ""' \
                -H 'sec-ch-ua-platform: \"macOS\"' \
                -H 'sec-ch-ua-platform-version: \"12.6.0\"' \
                -H 'sec-ch-ua-wow64: ?0' \
                -H 'sec-fetch-dest: document' \
                -H 'sec-fetch-mode: navigate' \
                -H 'sec-fetch-site: same-origin' \
                -H 'sec-fetch-user: ?1' \
                -H 'upgrade-insecure-requests: 1' \
                -H 'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36' \
                -H 'x-client-data: CIm2yQEIpLbJAQipncoBCMrqygEIkqHLAQjjmM0BCIagzQEI/rHNAQjcvc0BCOnFzQEI49rNAQil3M0BCLnfzQEIteDNAQjg4c0BCLbjzQEI3OPNAQjL6c0BGNXczQEYxuHNARin6s0B' \
                --compressed";

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
