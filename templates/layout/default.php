<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */

$cakeDescription = 'CakePHP: the rapid development php framework';
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700&display=swap" rel="stylesheet">
    <title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['game_style']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <nav class="top-nav">
        <div class="top-nav-title">
            <a href="<?= $this->Url->build('/') ?>"><span>Gamer</span>Platform</a>
        </div>

        <div class="top-nav-links">
            <?php if (isset($authUser)): ?>
                <span style="color: #00d2ff; font-weight: bold; margin-right: 15px;">
                        <?= h($authUser->username) ?>
                </span>
                <?= $this->Html->link('Games', ['controller' => 'Games', 'action' => 'index'], ['style' => 'color: #00ff2aff; margin-right: 15px;']) ?>
                <?= $this->Html->link('Mastermind', ['controller' => 'Games', 'action' => 'mastermind']) ?>
                <?= $this->Html->link('Filler', ['controller' => 'Games', 'action' => 'filler']) ?>
                <?= $this->Html->link('Profil', ['controller' => 'Users', 'action' => 'view']) ?>
                <?= $this->Html->link('Déconnexion', ['controller' => 'Users', 'action' => 'logout'], ['style' => 'color: #ff007c;']) ?>
            <?php else: ?>
                <?= $this->Html->link('Connexion', ['controller' => 'Users', 'action' => 'login']) ?>
            <?php endif; ?>
        </div>
    </nav>
    <main class="main">
        <div class="container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <footer>
    </footer>
</body>
</html>
