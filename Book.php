<?php
class Book {
    public $titolo;
    public $autore;
    public $anno;
    public $prezzo;
    public $pagine;

    public function __construct($titolo, $autore, $anno, $prezzo, $pagine) {
        $this->titolo = $titolo;
        $this->autore = $autore;
        $this->anno = $anno;
        $this->prezzo = $prezzo;
        $this->pagine = $pagine;
    }
}
?>

