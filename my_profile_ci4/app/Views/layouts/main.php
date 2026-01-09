<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? '個人形象網站') ?> - <?= esc($profile['name'] ?? '王小明') ?></title>
    <link rel="stylesheet" href="<?= base_url('css/styles.css') ?>">
</head>
<body>
    <?= $this->include('partials/header') ?>

    <?= $this->renderSection('content') ?>

    <?= $this->include('partials/footer') ?>
</body>
</html>
