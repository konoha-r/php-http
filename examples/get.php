<?php
use \Konoha\Http\Http as Http;

require_once (dirname(__DIR__)).'/src/Http.php';


$cookieFile = 'example.cookie.txt';
$options = array(
    CURLOPT_HTTPHEADER => array(
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:62.0) Gecko/20100101 Firefox/62.0',
        'Accept-Language: ja,en-us;q=0.7,en;q=0.3',
        'Accept-Encoding: identity',
    ),
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_FOLLOWLOCATION => true, // Location: ヘッダをたどる。
    CURLOPT_MAXREDIRS => 10, //リダイレクト最大数
//    CURLOPT_SSL_VERIFYPEER => false, //
//    CURLOPT_AUTOREFERER => true, // redirect時にrefererを追加
);
$http = new Http($options);
$result = $http->get('https://github.com/konoha-r/php-http');
$body = $result['body'];
echo $body;





