<?php
namespace Konoha\Book\Isbn;

use Konoha\Book\Isbn;

class Isbn13 extends Isbn{

    public function __construct($isbn){
        $isbn = str_replace('-', '', $isbn);
        if( !self::is_valid($isbn) ){ throw new IsbnException('Not ISBN13'); }
        $this->isbn = $isbn;
    }

    public function getLength(){ return 13; }

    public static function is_valid($isbn){
        $length = strlen($isbn);
        if( $length != 13 ){ return false; }
        return self::calc_checkdegit($isbn) == $isbn[12];
    }

    public static function calc_checkdegit($isbn){
        $length = strlen($isbn);
        if( $length < 12 ){ return false; }
        $mw = ($isbn[0] * 1) + ($isbn[1] * 3) 
             + ($isbn[2] * 1) + ($isbn[3] * 3) 
             + ($isbn[4] * 1) + ($isbn[5] * 3) 
             + ($isbn[6] * 1) + ($isbn[7] * 3) 
             + ($isbn[8] * 1) + ($isbn[9] * 3) 
             + ($isbn[10] * 1) + ($isbn[11] * 3);
        $remainder = $mw % 10;
        $check_digit = 10 - $remainder;
        if( $check_digit == 10 ){ return '0'; }
        return $check_digit;
    }

}

