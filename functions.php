<?php
require_once 'Book.php';
session_start();

// Inizializza sessione dai cookie
if (!isset($_SESSION['books'])) {
    $_SESSION['books'] = [];
    if (isset($_COOKIE['books'])) {
        $_SESSION['books'] = unserialize($_COOKIE['books']);
    }
}

function addBook($titolo, $autore, $anno, $prezzo, $pagine) {
    $book = new Book($titolo, $autore, $anno, $prezzo, $pagine);
    $_SESSION['books'][] = $book;
    setcookie('books', serialize($_SESSION['books']), time() + (86400 * 30), "/");
}

function searchBook($termine) {
    return array_filter($_SESSION['books'], function($book) use ($termine) {
        return stripos($book->titolo, $termine) !== false;
    });
}

function deleteBook($titolo) {
    $_SESSION['books'] = array_filter($_SESSION['books'], function($book) use ($titolo) {
        return $book->titolo !== $titolo;
    });
    $_SESSION['books'] = array_values($_SESSION['books']);
    setcookie('books', serialize($_SESSION['books']), time() + (86400 * 30), "/");
}

function editBook($vecchioTitolo, $nuoviDati) {
    foreach ($_SESSION['books'] as &$book) {
        if ($book->titolo === $vecchioTitolo) {
            $book->titolo = $nuoviDati['titolo'] ?? $book->titolo;
            $book->autore = $nuoviDati['autore'] ?? $book->autore;
            $book->anno = $nuoviDati['anno'] ?? $book->anno;
            $book->prezzo = $nuoviDati['prezzo'] ?? $book->prezzo;
            $book->pagine = $nuoviDati['pagine'] ?? $book->pagine;
            break;
        }
    }
    setcookie('books', serialize($_SESSION['books']), time() + (86400 * 30), "/");
}

function printBooks() {
    if (empty($_SESSION['books'])) {
        echo "<p class='text-muted'>Nessun libro inserito.</p>";
        return;
    }

    echo '
    <div class="table-responsive mt-4">
        <table class="table table-striped table-bordered table-hover align-middle shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>Titolo</th>
                    <th>Autore</th>
                    <th>Anno</th>
                    <th>Prezzo (€)</th>
                    <th>Pagine</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
    ';

    foreach ($_SESSION['books'] as $index => $book) {
        $editModalId = "editModal$index";
        $deleteModalId = "deleteModal$index";
        echo "
            <tr>
                <td>{$book->titolo}</td>
                <td>{$book->autore}</td>
                <td>{$book->anno}</td>
                <td>{$book->prezzo}</td>
                <td>{$book->pagine}</td>
                <td>
                    <div class='d-flex justify-content-between'>
                        <button type='button' class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#$editModalId'>Modifica</button>
                        <button type='button' class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#$deleteModalId'>Elimina</button>
                    </div>

                    <!-- Modal Modifica -->
                    <div class='modal fade' id='$editModalId' tabindex='-1' aria-hidden='true'>
                        <div class='modal-dialog'>
                            <div class='modal-content'>
                                <div class='modal-header'>
                                    <h5 class='modal-title'>Modifica libro</h5>
                                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Chiudi'></button>
                                </div>
                                <div class='modal-body'>
                                    <form method='post'>
                                        <input type='hidden' name='vecchio_titolo' value='{$book->titolo}'>
                                        <div class='mb-2'><input type='text' name='titolo' class='form-control' value='{$book->titolo}' required></div>
                                        <div class='mb-2'><input type='text' name='autore' class='form-control' value='{$book->autore}' required></div>
                                        <div class='mb-2'><input type='number' name='anno' class='form-control' value='{$book->anno}' required></div>
                                        <div class='mb-2'><input type='number' step='0.01' name='prezzo' class='form-control' value='{$book->prezzo}' required></div>
                                        <div class='mb-2'><input type='number' name='pagine' class='form-control' value='{$book->pagine}' required></div>
                                        <button type='submit' name='update' class='btn btn-success w-100'>Salva</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Eliminazione -->
                    <div class='modal fade' id='$deleteModalId' tabindex='-1' aria-hidden='true'>
                        <div class='modal-dialog'>
                            <div class='modal-content'>
                                <div class='modal-header'>
                                    <h5 class='modal-title'>Conferma eliminazione</h5>
                                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Chiudi'></button>
                                </div>
                                <div class='modal-body'>
                                    Sei sicuro di voler eliminare il libro <strong>{$book->titolo}</strong>?
                                </div>
                                <div class='modal-footer'>
                                    <form method='post' class='w-100'>
                                        <input type='hidden' name='delete_titolo' value='{$book->titolo}'>
                                        <button type='submit' name='delete' class='btn btn-danger w-100'>Sì, elimina</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        ";
    }

    echo '
            </tbody>
        </table>
    </div>
    ';
}
?>
