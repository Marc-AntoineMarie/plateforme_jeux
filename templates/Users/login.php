<div class="users form" style="max-width: 400px; margin: 50px auto; padding: 20px; background: rgba(255,255,255,0.05); border-radius: 15px;">
    <?= $this->Flash->render() ?>
    <h3 style="text-align: center; color: #00d2ff;">Connexion</h3>
    <?= $this->Form->create() ?>
    <fieldset style="border: none;">
        <div style="margin-bottom: 15px;">
            <?= $this->Form->control('username', ['label' => 'Nom d\'utilisateur', 'style' => 'width:100%; padding:8px; border-radius:5px; border:1px solid #444; background:#1a1a2e; color:#fff;']) ?>
        </div>
        <div style="margin-bottom: 20px;">
            <?= $this->Form->control('password', ['label' => 'Mot de passe', 'style' => 'width:100%; padding:8px; border-radius:5px; border:1px solid #444; background:#1a1a2e; color:#fff;']) ?>
        </div>
    </fieldset>
    <?= $this->Form->button(__('Se connecter'), ['style' => 'width: 100%; background: #00d2ff; color: #fff; border: none; padding: 10px; border-radius: 5px; cursor: pointer; font-weight: bold;']); ?>
    <?= $this->Form->end() ?>

    <p style="text-align: center; margin-top: 15px; font-size: 0.9rem;">
        Pas encore de compte ? <?= $this->Html->link("S'inscrire", ['action' => 'add']) ?>
    </p>
</div>
