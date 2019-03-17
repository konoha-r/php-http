<?php
namespace Konoha\Sound;

class Sound{

    private $d = '';
    private $data = '';
    private $header = '';

    private $datasize = 0;//データサイズ
    private $fmtid = 0;//フォーマットID
    private $chsize = 0;//チャンネル数
    private $freq = 0;//サンプリング周波数

    private $voice_dir_path;

    //   277 311     369 415 466     554 622     739 830 932
    // 261 293 329 349 391 440 493 523 587 659 698 783 880 987 1046
    //  ド  レ  ミ ファ ソ  ラ  シ  ド  レ  ミ ファ ソ  ラ  シ  ド

    private $map = array(
        0 => array(
            'ド' => 131,
            'ド#' => 138,
            'レb' => 138,
            'レ' => 146,
            'レ#' => 155,
            'ミb' => 155,
            'ミ' => 164,
            'ファ' => 174,
            'ファ#' => 184,
            'ソb' => 184,
            'ソ' => 195,
            'ソ#' => 207,
            'ラb' => 207,
            'ラ' => 220,
            'ラb' => 233,
            'シ#' => 233,
            'シ' => 246,
            'ど' => 131,
            'ど#' => 138,
            'れb' => 138,
            'れ' => 146,
            'れ#' => 155,
            'みb' => 155,
            'み' => 164,
            'ふぁ' => 174,
            'ふぁ#' => 184,
            'そb' => 184,
            'そ' => 195,
            'そ#' => 207,
            'らb' => 207,
            'ら' => 220,
            'ら#' => 233,
            'しb' => 233,
            'し' => 246,
        ),
        1 => array(
            'ド' => 261,
            'ド#' => 277,
            'レb' => 277,
            'レ' => 293,
            'レ#' => 311,
            'ミb' => 311,
            'ミ' => 329,
            'ファ' => 349,
            'ファ#' => 369,
            'ソb' => 369,
            'ソ' => 391,
            'ソ#' => 415,
            'ラb' => 415,
            'ラ' => 440,
            'ラ#' => 466,
            'シb' => 466,
            'シ' => 493,
        ),
        2 => array(
            'ド' => 523,
            'ド#' => 554,
            'レb' => 554,
            'レ' => 587,
            'レ#' => 622,
            'ミb' => 622,
            'ミ' => 659,
            'ファ' => 698,
            'ファ#' => 739,
            'ソb' => 739,
            'ソ' => 783,
            'ソ#' => 830,
            'ラb' => 830,
            'ラ' => 880,
            'ラ#' => 932,
            'シb' => 987,
            'シ' => 1046,
        ),
    );

    public function __construct(){
    }

    public function getData(){ return $this->data; }
    public function getDataSize(){ return $this->datasize; }
    public function setVoiceDirPath($dir_path){ $this->voice_dir_path = $dir_path; }

    private function getVoiceFilePath($filename){
        return $filename[0] == '/' ? $filename : $this->voice_dir_path . '/'. $filename;
    }

    public function loadFile($filename){/*{{{*/
        $fpath = $this->getVoiceFilePath($filename);
        $fp = fopen($fpath,'rb');
        if(!$fp){ return false; }

        $data = '';
        $header = '';
        $fields = join('/', array(
            'H8ChunkID', 'VChunkSize', 'H8Format',
            'H8Subchunk1ID', 'VSubchunk1Size',
            'vAudioFormat', 'vNumChannels', 'VSampleRate',
            'VByteRate', 'vBlockAlign', 'vBitsPerSample' )
        );

        $header = fread($fp, 36);
        $info = unpack($fields, $header);

        if($info['Subchunk1Size'] > 16){ $header .= fread($fp, $info['Subchunk1Size']-16); }

        $header .=  fread($fp, 4);
        $size = unpack('vsize',fread($fp, 4));
        $size = $size['size'];
//        $size = 32000 * 0.5;
        $size = 32000 * 3;

        $data = fread($fp, $size);

        $this->d = $header . pack('V', strlen($data)) . $data;
        $this->header = $header;
        $this->data = $data;
        $this->datasize = strlen($data);

        fclose($fp);
    }/*}}}*/

    public function resize($second){/*{{{*/
        $this->data = substr($this->data, 0, 32000 * $second);
        $this->datasize = strlen($this->data);
        $this->d = $this->header . pack('V', $this->datasize) . $this->data;
    }/*}}}*/

    public function saveFile($path){/*{{{*/
        file_put_contents($path,$this->d);
    }/*}}}*/

    public function connectSound(&$sound, $muon=0){/*{{{*/
        $this->datasize = strlen($this->data. $sound->getData());
        $muon = intval($muon);
        if( $muon > 0){
            $this->datasize += $muon;
            $d = '';
            for($i=0;$i<$muon;$i++){
                $d .= "\x00";
            }
            $this->data .= $d;
        }
        $this->data = $this->data . $sound->getData();
        $this->d = $this->header . pack('V',$this->datasize) . $this->data;
    }/*}}}*/

    public function shiftWave($frq){/*{{{*/
        // 音源の高さ
        //$f1 = $frq / 440;
        $f1 = $frq / 349;
        $f2 = 0;
        $data = $this->data;
        $dst = '';
        //$dst = substr($data, 0, 44);
        while(true){
            //$d = substr($data,floor($f2)*2,10);
            $d = substr($data, floor($f2)*2, 2);
            //$d = substr($data,44+floor($f2)*2,2);
            $dst .= $d[0];
            $dst .= $d[1];
            if( $f2 > $this->datasize ){ break; }
            $f2 += $f1;
        }

        $this->data = $dst;
        $this->datasize = strlen($this->data);
        $this->d = $this->header . pack('V', $this->datasize). $this->data;
    }/*}}}*/

    function combine($sound){/*{{{*/
        $f2 = 0;
        while(true){
            $src1 = 0;
            $src2 = 0;

            if( $f2 < $this->datasize ){
                $d = unpack('s', substr($this->data,$f2,2));
                $src1 = $d[1];
            }

            if( $f2 < $sound->getDataSize() ){
                $d = unpack('s', substr($sound->getData(),$f2,2));
                $src2 = $d[1];
            }

            //合成
            $src3 = floor($src1 + $src2);

            //音割れ防止
            if($src3 < -32768){
                $src3 = -32768;
            }else if($src3 > 32767){
                $src3 = 32767;
            }

            $d = pack('v',$src3);
            $this->data[$f2] = $d[0];
            $this->data[$f2+1] = $d[1];

            $f2 += 2;
            if( ($f2 > $this->datasize) && ($f2 > $sound->getDataSize()) ){
                break;
            }
        }

        $this->datasize = strlen($this->data);
        $this->d = $this->header . pack('V',$this->datasize) . $this->data;

    }/*}}}*/

    public function build($src, $p1, $p2, $len){/*{{{*/
        $this->loadFile("{$src}.aiff.wav");
        $f = isset($this->map[$p2][$p1]) ? $this->map[$p2][$p1] : null;
        $this->shiftWave($f);
        $this->resize($len * 0.5);
    }/*}}}*/

}


