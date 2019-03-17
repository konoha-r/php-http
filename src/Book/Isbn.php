<?php
namespace Konoha\Book;

use Konoha\Book\Isbn\Isbn13;
use Konoha\Book\Isbn\Isbn10;
use Konoha\Book\Isbn\Exception;

abstract class Isbn{

    protected $isbn;

    public function __toString(){ return $this->isbn; }

    public function getValue(){ return $this->isbn; }
    public function getValueWithoutDigit(){
        return substr($this->isbn, 0, $this->getLength() - 1);
    }
    abstract public function getLength();

    public static function fromString($isbn){
        $isbn = str_replace('-', '', $isbn);
        $length = strlen($isbn);
        if( $length == 13 ){ return new Isbn13($isbn); }
        if( $length == 10 ){ return new Isbn10($isbn); }
        throw new IsbnException('Unsupport ISBN');
    }

    public static function is_valid($isbn){ return false; }
    public static function calc_checkdegit($isbn){ return false; }

}

