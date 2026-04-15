<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit User'), ['action' => 'edit', $user->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete User'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete # {0}?', $user->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Users'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New User'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="users view content">
            <h3><?= h($user->username) ?></h3>
            <table>
                <tr>
                    <th><?= __('Username') ?></th>
                    <td><?= h($user->username) ?></td>
                </tr>
                <tr>
                    <th><?= __('Email') ?></th>
                    <td><?= h($user->email) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($user->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($user->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($user->modified) ?></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="related">
    <h4><?= __('Mes Scores') ?></h4>
    <?php if (!empty($user->scores)) : ?>
    <div class="table-responsive">
            <table>
                <tr>
                    <th><?= __('Jeu') ?></th>
                    <th><?= __('Score / Tentatives') ?></th>
                    <th><?= __('Date') ?></th>
                </tr>
                <?php foreach ($user->scores as $score) : ?>
                <tr>
                    <td><?= h($score->game_name) ?></td>
                    <td><?= h($score->score) ?></td>
                    <td><?= h($score->created) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php else : ?>
            <p>Aucun score enregistré pour le moment. Allez jouer !</p>
        <?php endif; ?>
    </div>
</div>
<div class="users view" style="color: white; padding: 20px; background: rgba(0,0,0,0.5); border-radius: 10px;">
    <h3 style="color: #00d2ff;"><?= h($user->username) ?></h3>
    <table class="table" style="color: white; width: 100%;">
        <tr>
            <th><?= __('Username') ?></th>
            <td><?= h($user->username) ?></td>
        </tr>
        <tr>
            <th><?= __('Membre depuis') ?></th>
            <td><?= h($user->created) ?></td>
        </tr>
    </table>
    <div style="margin-top: 20px;">
        <?= $this->Html->link(__('Déconnexion'), ['action' => 'logout'], ['style' => 'color: #ff007c;']) ?>
    </div>
</div>
