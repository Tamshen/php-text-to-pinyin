<?php

require dirname(__FILE__) . '/../autoload.php';

use Overtrue\Pinyin\Pinyin;

$totalStart = microtime(true);
$text = file_get_contents(dirname(__FILE__) . '/input.txt');

$html = array();
$methods = array('sentence','fullSentence','name','passportName','phrase','permalink','polyphones','chars','abbr','nameAbbr');

foreach ($methods as $method) {
    $start = microtime(true);
    $result = call_user_func(Pinyin::class.'::'.$method, $text);
    $usage = round(microtime(true) - $start, 5) * 1000;
    $sample = mb_substr(is_array($result) ? implode(' ', $result) : (string) $result, 0, 30);

    $html[] = "<tr>
                <td><span class=\"text-teal-500\">{$method}</span></td>
                <td><span class=\"text-green-500\">{$usage} ms</span></td>
                <td>{$sample}...</td>
               </tr>
        ";
}
$totalUsage = round(microtime(true) - $totalStart, 5) * 1000;
$html = implode("\n", $html);
$textLength = mb_strlen($text);

echo <<<HTML
    <div class="m-2">
        <div class="px-1 bg-green-600 text-white">Pinyin</div>
        
        <div class="py-1">
            Converted <span class="text-teal-500">{$textLength}</span> chars with following methods:
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Time Usage</th>
                    <th>Result</th>
                </tr>
            </thead>
            {$html}
        </table>
        
        <div class="mt-1">
          Total usage: <span class="text-green-500">{$totalUsage}</span>ms
        </div>
    </div>
HTML;