<?php include __DIR__ . '/partials/header.php'; ?>

<main class="page">
    <section class="container">
        <form action="/" method="post" onsubmit="return validateForm()" class="stack">
            <div class="stack">
                <label for="text">Text:</label>
                <textarea
                        id="text"
                        name="text"
                        cols="30"
                        rows="20"
                        placeholder="Paste your text here..."
                        oninput="this.setCustomValidity('')"
                ></textarea>
            </div>

            <input type="submit" value="Submit">
        </form>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
