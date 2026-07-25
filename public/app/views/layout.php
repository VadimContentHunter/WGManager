<?php
$content = $content ?? '';
?>

<!doctype html>
<html lang="ru">

<head>

    <meta charset="utf-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1">

    <title>WGManager</title>

    <link rel="stylesheet"
        href="/assets/css/style.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

    <?= $content ?>

    <div id="notify"></div>

    <!-- Сюда -->
    <div id="loader" class="loader hidden">
        <div class="loader-spinner"></div>
    </div>


    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/notify.js"></script>
    <script src="/assets/js/loader.js"></script>

    <script src="/assets/js/components/client-table.js"></script>
    <script src="/assets/js/components/settings-modal.js"></script>
    <script src="/assets/js/components/api-key-modal.js"></script>

    <script src="/assets/js/app.js"></script>
</body>

</html>