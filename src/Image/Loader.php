<?php
namespace Konoha\Image;

class Loader{

    public static function load($filepath){/*{{{*/
        $imgsize = getimagesize($filepath);
        if($imgsize['mime'] == "image/jpeg" || $imgsize['mime'] == "image/pjpeg"){
            return [ImageCreateFromJPEG($filepath), new Info($imgsize)];
        }else if($imgsize['mime'] == "image/gif"){
            return [ImageCreateFromGIF($filepath), new Info($imgsize)];
        }else if($imgsize['mime'] == "image/png" || $imgsize['mime'] == "image/x-png"){
            return [ImageCreateFromPNG($filepath), new Info($imgsize)];
        }

        throw new \ErrorException('Unsupported image');
    }/*}}}*/

}

