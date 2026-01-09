<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<header>
    <h1>我的作品集</h1>
    <p>精選專案展示</p>
</header>

<main>
    <section>
        <h2>專案作品</h2>
        <div class="portfolio-grid">
            <?php foreach ($projects as $project): ?>
            <div class="project-card">
                <h3><?= esc($project['title']) ?></h3>
                <p><?= esc($project['description']) ?></p>
                <div class="project-tech">
                    <?php
                    $tags = array_map('trim', explode(',', $project['tech_tags'] ?? ''));
                    foreach ($tags as $tag):
                        if (!empty($tag)):
                    ?>
                    <span class="tech-tag"><?= esc($tag) ?></span>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
                <?php if (!empty($project['link'])): ?>
                <a href="<?= esc($project['link']) ?>" class="btn">查看詳情</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section>
        <h2>開源貢獻</h2>
        <div class="portfolio-grid">
            <?php foreach ($opensource as $project): ?>
            <div class="project-card">
                <h3><?= esc($project['title']) ?></h3>
                <p><?= esc($project['description']) ?></p>
                <div class="project-tech">
                    <?php
                    $tags = array_map('trim', explode(',', $project['tech_tags'] ?? ''));
                    foreach ($tags as $tag):
                        if (!empty($tag)):
                    ?>
                    <span class="tech-tag"><?= esc($tag) ?></span>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
                <?php if (!empty($project['link'])): ?>
                <a href="<?= esc($project['link']) ?>" class="btn">GitHub</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section style="text-align: center;">
        <p><a href="<?= base_url('/') ?>" class="btn">返回首頁</a></p>
    </section>
</main>

<?= $this->endSection() ?>
