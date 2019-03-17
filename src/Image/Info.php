<?php
namespace Konoha\Image;

class Info{

    private $data;

    public function __construct($imgsize){
        $this->data = [
            'width' => $imgsize[0],
            'height' => $imgsize[1],
            'bits' => $imgsize['bits'],
            'channels' => $imgsize['channels'],
            'mime' => $imgsize['mime'],
        ];
    }

    public function __get($name){
        if( array_key_exists($name, $this->data) ){ return $this->data[$name]; }
        throw new \Exception('not exists property');
    }

    public function __set($name, $value){
        if( array_key_exists($name, $this->data) ){ throw new \Exception("'{$name}' can't write property."); }
        $this->$name = $value;
    }

}

