<?php 
include 'header.php'; 
include 'functions.php'; 

// Inizializza sessione
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inserisci 3 libri di esempio solo se la sessione è vuota
if(empty($_SESSION['books'])) {
    addBook('Libro 1', 'Autore A', 2020, 15.5, 200);
    addBook('Libro 2', 'Autore B', 2018, 20, 150);
    addBook('Libro 3', 'Autore C', 2022, 12, 300);
}
?>

<div class="container mt-4">
    <h1 class="mb-4 text-center">📚 Libreria PHP</h1>

    <!-- FORM AGGIUNTA LIBRO -->
    <form method="post" class="mb-4 bg-light p-3 rounded shadow-sm">
        <div class="row g-2">
            <div class="col-md-2"><input type="text" name="titolo" class="form-control" placeholder="Titolo" required></div>
            <div class="col-md-2"><input type="text" name="autore" class="form-control" placeholder="Autore" required></div>
            <div class="col-md-2"><input type="number" name="anno" class="form-control" placeholder="Anno" required></div>
            <div class="col-md-2"><input type="number" step="0.01" name="prezzo" class="form-control" placeholder="Prezzo" required></div>
            <div class="col-md-2"><input type="number" name="pagine" class="form-control" placeholder="Pagine" required></div>
            <div class="col-md-2"><button type="submit" name="add" class="btn btn-success w-100">Aggiungi</button></div>
        </div>
    </form>

    <!-- FORM RICERCA -->
    <form method="get" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cerca per titolo...">
            <button class="btn btn-primary" type="submit">Cerca</button>
        </div>
    </form>

    <?php
    // AGGIUNTA
    if (isset($_POST['add'])) {
        addBook($_POST['titolo'], $_POST['autore'], $_POST['anno'], $_POST['prezzo'], $_POST['pagine']);
        header("Location: index.php");
        exit();
    }

    // ELIMINAZIONE
    if (isset($_POST['delete'])) {
        deleteBook($_POST['delete_titolo']);
        header("Location: index.php");
        exit();
    }

    // FORM MODIFICA
    if (isset($_POST['edit'])) {
        $bookDaModificare = null;
        foreach ($_SESSION['books'] as $book) {
            if ($book->titolo === $_POST['edit_titolo']) {
                $bookDaModificare = $book;
                break;
            }
        }

        if ($bookDaModificare) {
            echo '
            <div class="bg-light p-3 rounded shadow mb-4">
                <h4>Modifica libro: ' . htmlspecialchars($bookDaModificare->titolo) . '</h4>
                <form method="post">
                    <input type="hidden" name="vecchio_titolo" value="' . htmlspecialchars($bookDaModificare->titolo) . '">
                    <div class="row g-2">
                        <div class="col-md-2"><input type="text" name="titolo" class="form-control" value="' . htmlspecialchars($bookDaModificare->titolo) . '" required></div>
                        <div class="col-md-2"><input type="text" name="autore" class="form-control" value="' . htmlspecialchars($bookDaModificare->autore) . '" required></div>
                        <div class="col-md-2"><input type="number" name="anno" class="form-control" value="' . htmlspecialchars($bookDaModificare->anno) . '" required></div>
                        <div class="col-md-2"><input type="number" step="0.01" name="prezzo" class="form-control" value="' . htmlspecialchars($bookDaModificare->prezzo) . '" required></div>
                        <div class="col-md-2"><input type="number" name="pagine" class="form-control" value="' . htmlspecialchars($bookDaModificare->pagine) . '" required></div>
                        <div class="col-md-2"><button type="submit" name="update" class="btn btn-success w-100">Salva</button></div>
                    </div>
                </form>
            </div>
            ';
        }
    }

    // AGGIORNAMENTO DATI LIBRO
    if (isset($_POST['update'])) {
        editBook($_POST['vecchio_titolo'], [
            'titolo' => $_POST['titolo'],
            'autore' => $_POST['autore'],
            'anno' => $_POST['anno'],
            'prezzo' => $_POST['prezzo'],
            'pagine' => $_POST['pagine']
        ]);
        header("Location: index.php");
        exit();
    }

    // RICERCA
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $risultati = searchBook($_GET['search']);
        echo "<h3>Risultati ricerca:</h3>";
        if ($risultati) {
            echo "<ul class='list-group mb-3'>";
            foreach ($risultati as $book) {
                echo "<li class='list-group-item'>{$book->titolo} di {$book->autore}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Nessun risultato trovato.</p>";
        }
    }

    // STAMPA TABELLA
    printBooks();
    ?>

    <!-- GRAFICI LINEE -->
    <h3 class="mt-5">📊 Grafici Libreria</h3>
    <div class="row">
        <div class="col-md-6">
            <h5>Prezzo per Titolo</h5>
            <canvas id="prezzoChart"></canvas>
        </div>
        <div class="col-md-6">
            <h5>Pagine per Autore</h5>
            <canvas id="pagineChart"></canvas>
        </div>
    </div>

    <!-- Chart.js incluso prima dello script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    <?php
    $titoli = [];
    $prezzi = [];
    $autori = [];
    $pagine = [];

    foreach ($_SESSION['books'] as $book) {
        $titoli[] = addslashes($book->titolo);
        $prezzi[] = $book->prezzo;
        $autori[] = addslashes($book->autore);
        $pagine[] = $book->pagine;
    }
    ?>

    // Prezzo vs Titolo (linea)
    const ctxPrezzo = document.getElementById('prezzoChart').getContext('2d');
    new Chart(ctxPrezzo, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($titoli); ?>,
            datasets: [{
                label: 'Prezzo (€)',
                data: <?php echo json_encode($prezzi); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Pagine vs Autore (linea)
    const ctxPagine = document.getElementById('pagineChart').getContext('2d');
    new Chart(ctxPagine, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($autori); ?>,
            datasets: [{
                label: 'Numero Pagine',
                data: <?php echo json_encode($pagine); ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });
    </script>

</div>

<?php include 'footer.php'; ?>
