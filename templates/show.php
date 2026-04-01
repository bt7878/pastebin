<?php /** @var string $text */ ?>

<?php include __DIR__ . '/partials/header.php'; ?>

<main class="page">
    <section class="container stack">
        <div class="stack">
            <label for="content">Paste:</label>
            <div id="content" class="content-display">
                <?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>

        <a href="/" class="button">Create New</a>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
