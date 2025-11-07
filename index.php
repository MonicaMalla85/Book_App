<?php include 'header.php'; ?>
<?php include 'functions.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4 text-center">📚 Libreria PHP</h1>

    <!-- FORM AGGIUNTA -->
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
    if (isset($_POST['add'])) {
        addBook($_POST['titolo'], $_POST['autore'], $_POST['anno'], $_POST['prezzo'], $_POST['pagine']);
        echo "<div class='alert alert-success'>Libro aggiunto con successo!</div>";
    }

    if (isset($_POST['delete'])) {
        deleteBook($_POST['delete_titolo']);
        echo "<div class='alert alert-warning'>Libro eliminato con successo.</div>";
    }

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

    printBooks();
    ?>
</div>

<?php include 'footer.php'; ?>
