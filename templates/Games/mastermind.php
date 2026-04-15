<?php
/**
 * @var \App\View\AppView $this
 * @var array $attempts
 */
?>
<div class="mastermind-container">
    <h2>Mastermind</h2>

    <div class="game-board">
        <?= $this->Form->create(null) ?>
            <fieldset>
                <legend><?= __('Devinez la combinaison (4 couleurs)') ?></legend>
                <div style="display: flex; gap: 15px;">
                    <?php
                    $colorsList = [
                        'red' => '🔴 Rouge',
                        'blue' => '🔵 Bleu',
                        'green' => '🟢 Vert',
                        'yellow' => '🟡 Jaune',
                        'purple' => '🟣 Violet',
                        'orange' => '🟠 Orange'
                    ];
                    for ($i = 0; $i < 4; $i++): ?>
                        <?= $this->Form->control("guess.$i", [
                            'type' => 'select',
                            'options' => $colorsList,
                            'label' => false,
                            'empty' => '(Couleur)'
                        ]) ?>
                    <?php endfor; ?>
                </div>
            </fieldset>
            <?= $this->Form->button(__('Vérifier la combinaison'), ['class' => 'button']) ?>
        <?= $this->Form->end() ?>
    </div>

    <hr>

    <h3>Historique des tentatives</h3>
    <div class="history">
        <?php if (empty($attempts)): ?>
            <p>Aucune tentative pour le moment. Bonne chance !</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Tentative</th>
                        <th>Couleurs choisies</th>
                        <th>Bien placés</th>
                        <th>Mal placés</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($attempts) as $index => $attempt): ?>
                        <tr>
                            <td><?= count($attempts) - $index ?></td>
                            <td>
                                <?php foreach ($attempt['guess'] as $color): ?>
                                    <span class="dot" style="background-color: <?= h($color) ?>; border: 1px solid #ccc; padding: 2px 8px; border-radius: 50%; margin-right: 5px;"></span>
                                <?php endforeach; ?>
                            </td>
                            <td><strong><?= $attempt['result']['exact'] ?></strong></td>
                            <td><?= $attempt['result']['near'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
