<?php
namespace Konoha\Book\Isbn;

use Konoha\Book\Isbn;

class Isbn10 extends Isbn{

    public function __construct($isbn){
        $isbn = str_replace('-', '', $isbn);
        if( !self::is_valid($isbn) ){ throw new IsbnException('Not ISBN10'); }
        $this->isbn = $isbn;
    }

    public function getLength(){ return 10; }

    public static function is_valid($isbn){
        $length = strlen($isbn);
        if( $length != 10 ){ return false; }
        $x = strtoupper($isbn[9]);
        return self::calc_checkdegit($isbn) == $x;
    }

    public static function calc_checkdegit($isbn){
        $length = strlen($isbn);
        if( $length < 9 ){ return false; }
        $mw = ($isbn[0] * 10) + ($isbn[1] * 9) + ($isbn[2] * 8) 
             + ($isbn[3] * 7) + ($isbn[4] * 6) + ($isbn[5] * 5) 
             + ($isbn[6] * 4) + ($isbn[7] * 3) + ($isbn[8] * 2);
        $remainder = $mw % 11;
        $check_digit = 11 - $remainder;
        if( $check_digit == 11 ){ return 'X'; }
        if( $check_digit == 10 ){ return '0'; }
        return $check_digit;
    }

}

