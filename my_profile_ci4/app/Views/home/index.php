<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<header>
    <h1><?= esc($profile['name'] ?? '王小明') ?></h1>
    <p><?= esc($profile['title'] ?? '全端工程師') ?></p>
</header>

<main>
    <section id="about">
        <h2>關於我</h2>
        <div class="about-content">
            <div class="profile-image"><?= mb_substr($profile['name'] ?? '王', 0, 1) ?></div>
            <div class="about-text">
                <?php
                $bioLines = explode("\n", $profile['bio'] ?? '');
                foreach ($bioLines as $line):
                    if (trim($line) !== ''):
                ?>
                    <p><?= esc($line) ?></p>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </div>
    </section>

    <section id="skills">
        <h2>技能專長</h2>
        <div class="skills-container">
            <?php foreach ($skills as $category => $skillList): ?>
            <div class="skill-category">
                <h3><?= esc($category) ?></h3>
                <div class="skill-tags">
                    <?php foreach ($skillList as $skill): ?>
                    <span class="skill-tag"><?= esc($skill) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="experience">
        <h2>工作經歷</h2>
        <div class="timeline">
            <?php foreach ($experiences as $exp): ?>
            <div class="timeline-item">
                <h3><?= esc($exp['company']) ?></h3>
                <span class="date"><?= esc($exp['start_date']) ?> - <?= esc($exp['end_date'] ?? '現在') ?></span>
                <p><strong><?= esc($exp['position']) ?></strong></p>
                <p><?= esc($exp['description']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="education">
        <h2>學歷與證照</h2>
        <div class="timeline">
            <?php foreach ($education as $edu): ?>
            <div class="timeline-item">
                <h3><?= esc($edu['institution']) ?></h3>
                <span class="date">
                    <?= esc($edu['start_date']) ?>
                    <?php if ($edu['end_date']): ?>
                     - <?= esc($edu['end_date']) ?>
                    <?php endif; ?>
                </span>
                <?php if ($edu['degree']): ?>
                <p><strong><?= esc($edu['degree']) ?></strong></p>
                <?php endif; ?>
                <p><?= esc($edu['description']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="portfolio-preview">
        <h2>作品集</h2>
        <p>查看我參與開發的專案與作品。</p>
        <a href="<?= base_url('portfolio') ?>" class="btn">瀏覽完整作品集</a>
    </section>

    <section id="contact">
        <h2>聯絡方式</h2>
        <p>歡迎透過以下方式與我聯繫，期待與您交流！</p>
        <div class="contact-links">
            <?php if (!empty($profile['email'])): ?>
            <a href="mailto:<?= esc($profile['email']) ?>" class="contact-link">Email</a>
            <?php endif; ?>
            <?php if (!empty($profile['github'])): ?>
            <a href="<?= esc($profile['github']) ?>" class="contact-link" target="_blank">GitHub</a>
            <?php endif; ?>
            <?php if (!empty($profile['linkedin'])): ?>
            <a href="<?= esc($profile['linkedin']) ?>" class="contact-link" target="_blank">LinkedIn</a>
            <?php endif; ?>
        </div>
    </section>
</main>

<?= $this->endSection() ?>
