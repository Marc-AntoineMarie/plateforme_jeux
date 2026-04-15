<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Score> $scores
 */
?>
<div class="scores index content">
    <?= $this->Html->link(__('New Score'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Scores') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('game_name') ?></th>
                    <th><?= $this->Paginator->sort('score') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scores as $score): ?>
                <tr>
                    <td><?= $this->Number->format($score->id) ?></td>
                    <td><?= $score->hasValue('user') ? $this->Html->link($score->user->username, ['controller' => 'Users', 'action' => 'view', $score->user->id]) : '' ?></td>
                    <td><?= h($score->game_name) ?></td>
                    <td><?= $this->Number->format($score->score) ?></td>
                    <td><?= h($score->created) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $score->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $score->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $score->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $score->id),
                            ]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>