<?php
namespace Konoha\Http;

class Http{

    private $options = array();

    public function __construct($options){
        $this->options = $options;
    }

    public function exec($url, $options){
        $curl = curl_init($url);
        $options[CURLOPT_HEADER] = true;
        $options[CURLOPT_RETURNTRANSFER] = true;
        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        $header_size = $info['header_size'];
        $header = substr($result, 0, $header_size);
        $body = substr($result, $header_size);
        curl_close($curl);
        return array(
            'header' => $header,
            'body' => $body,
            'info' => $info,
        );
    }

    function download($url) {
        $ch  = curl_init($url);
        $tmp = tmpfile();
        // echo "file: \n";
        $options = $this->getOptions();
        $options[CURLOPT_URL] = $url;
        $options[CURLOPT_HEADERFUNCTION] = function($ch, $header) use (&$filename) {
            $regex = '/^Content-Disposition: attachment; filename="*(.+?)"*$/i';
            if (preg_match($regex, $header, $matches)) {
                $filename = rtrim($matches[1]);
            }else{
                $filename = date('Ymd_his').'.nico';
            }
            return strlen($header);
        };
        $options[CURLOPT_FILE] = $tmp; // 転送内容が書き込まれるファイル
        $options[CURLOPT_FAILONERROR] = true; // 400以上のコードが返ってきたら失敗と判断する
        $options[CURLOPT_SSL_VERIFYPEER] = false; // 証明書の検証を行わない
        curl_setopt_array($ch, $options);
        try {
            if (!curl_exec($ch)) {
                throw new \ErrorException(curl_error($ch));
            } elseif ($filename === null) {
                throw new \ErrorException('ヘッダのContent-Dispotitionフィールドからファイル名を取得できませんでした。');
            } elseif (!@rename(stream_get_meta_data($tmp)['uri'], $filename)) {
                throw new \ErrorException(error_get_last()['message']);
            }
        } catch(\ErrorException $e) {
            echo "Error: ".$e->getFile().':'.$e->getLine().' '.$e->getMessage();
            //error_log($e->getFile().':'.$e->getLine().' '.$e->getMessage(), 3, './dl_error.log');
        }
    }

    public function setOptions($options){
        $this->options = $options + $this->options;
    }

    public function getOptions(){ return $this->options; }

    public function get($url){
        $options = $this->getOptions();
        foreach(array(CURLOPT_POST, CURLOPT_CUSTOMREQUEST) as $v){
            if( isset($options[$v]) ){ unset($options[$v]); }
        }
        $options[CURLOPT_HTTPGET] = true;
        return $this->exec($url, $options);
    }

    public function post($url, $params=array()){
        $options = $this->getOptions();
        foreach(array(CURLOPT_HTTPGET, CURLOPT_CUSTOMREQUEST) as $v){
            if( isset($options[$v]) ){ unset($options[$v]); }
        }
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = is_array($params) ? http_build_query($params) : $params;
        return $this->exec($url, $options);
    }

    public function put($url, $data){
        $options = $this->getOptions();
        foreach(array(CURLOPT_HTTPGET, CURLOPT_POST) as $v){
            if( isset($options[$v]) ){ unset($options[$v]); }
        }
        $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
        $options[CURLOPT_POSTFIELDS] = $data;
        return $this->exec($url, $options);
    }

    public function delete($url){
        $options = $this->getOptions();
        foreach(array(CURLOPT_HTTPGET, CURLOPT_POST) as $v){
            if( isset($options[$v]) ){ unset($options[$v]); }
        }
        $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        $options[CURLOPT_POSTFIELDS] = $data;
        return $this->exec($url, $options);
    }

}

