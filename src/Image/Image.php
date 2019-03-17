<?php
namespace Konoha\Image;

class Image{

    private $image;
    private $info;

    public function __construct($filepath){
        list($this->image, $this->info) = Loader::load($filepath);
    }

    public function getInfo(){ return $this->info; }

    public function getLabs($w=50, $h=50){ return $labs = new Labs($this->image, $w, $h); }
    public function getDistance(Image $img, $w=50, $h=50){ return $this->getLabs($w, $h)->getDistance($img->getLabs($w, $h)); }
    public function callback(Callable $f){ return call_user_func_array($f, [$this->image]); }

}

