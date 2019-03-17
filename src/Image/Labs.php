<?php
namespace Konoha\Image;

class Labs{

    private $labs = null;

    public function __construct($image, $w=50, $h=50){
        $this->labs = self::to_labs($image, $w, $h);
    }

    public function getValue(){ return $this->labs; }
    public function getDistance(Labs $labs){ return self::distance($this->labs, $labs->labs); }

    public static function to_labs($image, $thumb_width=50, $thumb_height=50){/*{{{*/
        $width = imagesx($image);
        $height = imagesy($image);
        $thumb = imagecreatetruecolor($thumb_width, $thumb_height);
        imagecopyresampled($thumb, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);

        $labs = [];
        for($x=0; $x < $thumb_width; $x++){
            for($y=0; $y < $thumb_height; $y++){
                $index = imagecolorat($thumb, $x, $y);
                $rgb   = imagecolorsforindex($thumb, $index);
                $labs[$x][$y] = self::rgb2lab( array($rgb['red'], $rgb['green'], $rgb['blue']) );
            }
        }
        return $labs;
    }/*}}}*/

    public static function rgb2lab($rgb) { return $lab = self::xyz2lab(self::rgb2xyz($rgb)); }

    public static function rgb2xyz($rgb){/*{{{*/
        $r = self::_rgb2xyz($rgb[0]);
        $g = self::_rgb2xyz($rgb[1]);
        $b = self::_rgb2xyz($rgb[2]);
    
        return $xyz = [
            $r * 0.4360747 + $g * 0.3850649 + $b * 0.1430804,
            $r * 0.2225045 + $g * 0.7168786 + $b * 0.0606169,
            $r * 0.0139322 + $g * 0.0971045 + $b * 0.7141733,
        ];
    }/*}}}*/

    protected static function _rgb2xyz($rgb){/*{{{*/
        $t = $rgb / 255;
        $t = ($t > 0.04045) ? (($t + 0.055) / 1.055)**2.4 : $t / 12.92;
        return  $t * 100;
    }/*}}}*/

    public static function xyz2lab($xyz){/*{{{*/
        // Chromatic Adaptation Matrices
        // D50
        $var_x = $xyz[0] / (0.96422 * 100);
        $var_y = $xyz[1] / (1.0000 * 100);      
        $var_z = $xyz[2] / (0.82521 * 100);

        $threadhold = 0.008856;
        $var_x = ($var_x > $threadhold) ? $var_x**(1/3) : (7.787 * $var_x) + (16 / 116);
        $var_y = ($var_y > $threadhold) ? $var_y**(1/3) : (7.787 * $var_y) + (16 / 116);
        $var_z = ($var_z > $threadhold) ? $var_z**(1/3) : (7.787 * $var_z) + (16 / 116);

        $l = ( 116 * $var_y ) - 16;
        $a = 500 * ( $var_x - $var_y );
        $b = 200 * ( $var_y - $var_z );

        return [$l, $a, $b];
    }/*}}}*/

    public static function distance($src_labs, $dist_labs){/*{{{*/
        $distance = 0;
        foreach($src_labs as $x => $x_labs ){
            foreach($x_labs as $y => $value){
                $distance += self::lab_distance($value, $dist_labs[$x][$y]);
            }
        }
        return $distance;
    }/*}}}*/

    private static function lab_distance($p1, $p2){
        return ( ($p2[0] - $p1[0])**2 + ($p2[1] - $p1[1])**2 + ($p2[2] - $p1[2])**2 )**(1/2);
        //return sqrt( ($p2[0] - $p1[0])**2 + ($p2[1] - $p1[1])**2 + ($p2[2] - $p1[2])**2 );
    }

}

