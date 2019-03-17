<?php
namespace Konoha\Sound;

class Wave{

    private $d = '';
    private $data = '';
    private $header = '';

    private $datasize = 0;//データサイズ
    private $fmtid = 0;//フォーマットID
    private $chsize = 0;//チャンネル数
    private $freq = 0;//サンプリング周波数


    public function __construct(){ }

    public function getData(){ return $this->data; }
    public function getDataSize(){ return $this->datasize; }

    public function loadFile($fpath){/*{{{*/

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
        file_put_contents($path, $this->d);
    }/*}}}*/

    public function connect(Wave $wave, $muon=0){/*{{{*/
        $this->datasize = strlen($this->data. $wave->data);
        $muon = intval($muon);
        if( $muon > 0){
            $this->datasize += $muon;
            $d = '';
            for($i=0;$i<$muon;$i++){
                $d .= "\x00";
            }
            $this->data .= $d;
        }
        $this->data = $this->data . $wave->data;
        $this->d = $this->header . pack('V',$this->datasize) . $this->data;
    }/*}}}*/

    /**
     * $f1 = 変更後の音の高さ / 元の高さ
     */
    public function shift($f1){/*{{{*/
        $f2 = 0;
        $data = $this->data;
        $dst = '';
        while(true){
            //$d = substr($data, floor($f2)*2, 10);
            //$d = substr($data, 44+floor($f2)*2, 2);

            $d = substr($data, floor($f2)*2, 2);
            $dst .= $d[0];
            $dst .= $d[1];
            if( $f2 > $this->datasize ){ break; }
            $f2 += $f1;
        }

        $this->data = $dst;
        $this->datasize = strlen($this->data);
        $this->d = $this->header . pack('V', $this->datasize). $this->data;
    }/*}}}*/

    public function combine(Wave $wave){/*{{{*/
        $f2 = 0;
        while(true){
            $src1 = 0;
            $src2 = 0;

            if( $f2 < $this->datasize ){
                $d = unpack('s', substr($this->data,$f2,2));
                $src1 = $d[1];
            }

            if( $f2 < $wave->getDataSize() ){
                $d = unpack('s', substr($wave->getData(),$f2,2));
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
            $this->data[$f2 + 1] = $d[1];

            $f2 += 2;
            if( ($f2 > $this->datasize) && ($f2 > $wave->getDataSize()) ){
                break;
            }
        }

        $this->datasize = strlen($this->data);
        $this->d = $this->header . pack('V',$this->datasize) . $this->data;

    }/*}}}*/

}

