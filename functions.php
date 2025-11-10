<?php
require_once 'Book.php';
session_start();

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
    $risultati = array_filter($_SESSION['books'], function($book) use ($termine) {
        return stripos($book->titolo, $termine) !== false;
    });
    return $risultati;
}

function deleteBook($titolo) {
    $_SESSION['books'] = array_filter($_SESSION['books'], function($book) use ($titolo) {
        return $book->titolo !== $titolo;
    });
    setcookie('books', serialize($_SESSION['books']), time() + (86400 * 30), "/");
}

// ✏️ Edit book
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

    foreach ($_SESSION['books'] as $book) {
        echo "
            <tr>
                <td>{$book->titolo}</td>
                <td>{$book->autore}</td>
                <td>{$book->anno}</td>
                <td>{$book->prezzo}</td>
                <td>{$book->pagine}</td>
                <td>
                    <form method='post' class='d-inline'>
                        <input type='hidden' name='delete_titolo' value='{$book->titolo}'>
                        <button type='submit' name='delete' class='btn btn-sm btn-danger'>Elimina</button>
                    </form>
                    <form method='post' class='d-inline'>
                        <input type='hidden' name='edit_titolo' value='{$book->titolo}'>
                        <button type='submit' name='edit' class='btn btn-sm btn-warning'>Modifica</button>
                    </form>
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