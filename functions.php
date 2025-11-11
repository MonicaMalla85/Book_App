<?php
require_once 'Book.php';
session_start();

// Inizializzazione sessione/cookie
if (!isset($_SESSION['books'])) {
    $_SESSION['books'] = [];
    if (isset($_COOKIE['books'])) {
        $_SESSION['books'] = unserialize($_COOKIE['books']);
    }
}

/* 🔹 Aggiungi libro con copertina automatica */
function addBook($titolo, $autore, $anno, $prezzo, $pagine) {
    // Mappa parole chiave → copertine
    $copertine = [
        'geisha' => 'geisha.jpeg',
        'potter' => 'harry_potter.jpg',
        'karamazov' => 'karamazov.jpg',
        'kitchen' => 'kitchen.png'
    ];

    $copertina = null;
    foreach ($copertine as $chiave => $file) {
        if (stripos($titolo, $chiave) !== false) {
            $copertina = $file;
            break;
        }
    }

    $book = new Book($titolo, $autore, $anno, $prezzo, $pagine, $copertina);
    $_SESSION['books'][] = $book;
    setcookie('books', serialize($_SESSION['books']), time() + (86400 * 30), "/");
}

/* 🔹 Ricerca per titolo */
function searchBook($termine) {
    return array_filter($_SESSION['books'], function($book) use ($termine) {
        return stripos($book->titolo, $termine) !== false;
    });
}

/* 🔹 Elimina libro con conferma */
function deleteBook($titolo) {
    $_SESSION['books'] = array_filter($_SESSION['books'], function($book) use ($titolo) {
        return $book->titolo !== $titolo;
    });
    setcookie('books', serialize($_SESSION['books']), time() + (86400 * 30), "/");

    $_SESSION['message'] = "✅ Il libro '$titolo' è stato eliminato con successo.";
}

/* 🔹 Modifica libro */
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

/* 🔹 Stampa tabella libri + modali */
function printBooks() {
    if (empty($_SESSION['books'])) {
        echo "<p class='text-muted'>Nessun libro inserito.</p>";
        return;
    }

    // Messaggio di conferma (eliminazione)
    if (isset($_SESSION['message'])) {
        echo "<div class='alert alert-success text-center mt-3'>" . htmlspecialchars($_SESSION['message']) . "</div>";
        unset($_SESSION['message']);
    }

    echo '
    <div class="table-responsive mt-4">
        <table class="table table-striped table-bordered table-hover align-middle shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>Copertina</th>
                    <th>Titolo</th>
                    <th>Autore</th>
                    <th>Anno</th>
                    <th>Prezzo (€)</th>
                    <th>Pagine</th>
                    <th class="text-center">Azioni</th>
                </tr>
            </thead>
            <tbody>
    ';

    foreach ($_SESSION['books'] as $index => $book) {
        $imgTag = $book->copertina 
            ? "<img src='img_copertine/{$book->copertina}' alt='copertina' class='rounded' style='width:60px;height:auto;'>"
            : "<span class='text-muted'>—</span>";

        $modalEditId = "modalEdit$index";
        $modalDeleteId = "modalDelete$index";

        echo "
            <tr>
                <td class='text-center'>$imgTag</td>
                <td>{$book->titolo}</td>
                <td>{$book->autore}</td>
                <td>{$book->anno}</td>
                <td>{$book->prezzo}</td>
                <td>{$book->pagine}</td>
                <td class='text-center'>
                    <button class='btn btn-sm btn-warning me-2' data-bs-toggle='modal' data-bs-target='#$modalEditId'>✏️</button>
                    <button class='btn btn-sm btn-danger' data-bs-toggle='modal' data-bs-target='#$modalDeleteId'>🗑️</button>
                </td>
            </tr>

            <!-- Modal Modifica -->
            <div class='modal fade' id='$modalEditId' tabindex='-1' aria-hidden='true'>
                <div class='modal-dialog modal-dialog-centered'>
                    <div class='modal-content'>
                        <div class='modal-header bg-warning text-dark'>
                            <h5 class='modal-title'>Modifica Libro</h5>
                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Chiudi'></button>
                        </div>
                        <form method='post'>
                            <div class='modal-body'>
                                <input type='hidden' name='vecchio_titolo' value='{$book->titolo}'>
                                <div class='mb-2'><input type='text' name='titolo' class='form-control' value='{$book->titolo}' required></div>
                                <div class='mb-2'><input type='text' name='autore' class='form-control' value='{$book->autore}' required></div>
                                <div class='mb-2'><input type='number' name='anno' class='form-control' value='{$book->anno}' required></div>
                                <div class='mb-2'><input type='number' step='0.01' name='prezzo' class='form-control' value='{$book->prezzo}' required></div>
                                <div class='mb-2'><input type='number' name='pagine' class='form-control' value='{$book->pagine}' required></div>
                            </div>
                            <div class='modal-footer'>
                                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Annulla</button>
                                <button type='submit' name='update' class='btn btn-warning text-dark'>Salva modifiche</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Eliminazione -->
            <div class='modal fade' id='$modalDeleteId' tabindex='-1' aria-hidden='true'>
                <div class='modal-dialog modal-dialog-centered'>
                    <div class='modal-content'>
                        <div class='modal-header bg-danger text-white'>
                            <h5 class='modal-title'>Conferma eliminazione</h5>
                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Chiudi'></button>
                        </div>
                        <div class='modal-body'>
                            Sei sicuro di voler eliminare <strong>{$book->titolo}</strong>?
                        </div>
                        <div class='modal-footer'>
                            <form method='post'>
                                <input type='hidden' name='delete_titolo' value='{$book->titolo}'>
                                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Annulla</button>
                                <button type='submit' name='delete' class='btn btn-danger'>Elimina</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        ";
    }

    echo '
            </tbody>
        </table>
    </div>
    ';
}
?>
