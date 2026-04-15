<?php
// On identifie les couleurs actuelles pour les interdire
$p1_c = $grid[$p1_owned[0][0]][$p1_owned[0][1]];
$p2_c = $grid[$p2_owned[0][0]][$p2_owned[0][1]];
?>

<div class="filler-container">
    <div style="text-align:center; margin-bottom: 20px;">
        <?php if ($game->p2_id === null): ?>
            <div style="background: rgba(157, 80, 187, 0.2); border: 1px solid #9d50bb; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                <p style="margin: 0 0 10px 0;">🎮 <b>Partie en attente d'un adversaire</b></p>
                <div style="display: flex; gap: 10px;">
                    <input type="text" value="<?= $this->Url->build(['action' => 'filler', $inviteCode], true) ?>"
                           id="inviteLink" readonly
                           style="flex: 1; background: rgba(0,0,0,0.3); border: 1px solid #444; color: #00d2ff; text-align: center; border-radius: 5px;">
                    <button onclick="copyLink()" class="button" style="padding: 5px 15px !important;">Copier</button>
                </div>
            </div>
        <?php endif; ?>

        <h2 style="color: <?= $isMyTurn ? '#2ecc71' : '#666' ?>; text-shadow: 0 0 10px rgba(0,0,0,0.5);">
            <?= $isMyTurn ? "🟢 C'EST VOTRE TOUR" : "🔴 ATTENTE DE L'ADVERSAIRE..." ?>
        </h2>
    </div>

    <div class="filler-layout" style="display: flex; gap: 40px; justify-content: center; align-items: flex-start; <?= !$isMyTurn ? 'opacity: 0.7;' : '' ?>">

        <div class="grid-container" style="display: grid; grid-template-columns: repeat(12, 35px); gap: 3px; background: #1a1a2e; padding: 12px; border-radius: 10px; border: 2px solid #333;">
            <?php foreach ($grid as $y => $row): ?>
                <?php foreach ($row as $x => $color): ?>
                    <?php
                        $isP1 = false; foreach($p1_owned as $p) if($p[0]==$y && $p[1]==$x) $isP1 = true;
                        $isP2 = false; foreach($p2_owned as $p) if($p[0]==$y && $p[1]==$x) $isP2 = true;
                    ?>
                    <div class="cell" style="
                        width: 35px; height: 35px;
                        background-color: <?= h($color) ?>;
                        border-radius: 4px;
                        display: flex; align-items: center; justify-content: center;
                        position: relative;
                        <?= $isP1 ? 'outline: 3px solid #ff007c; z-index: 1; box-shadow: 0 0 10px #ff007c;' : '' ?>
                        <?= $isP2 ? 'outline: 3px solid #00d2ff; z-index: 1; box-shadow: 0 0 10px #00d2ff;' : '' ?>
                    ">
                        <?php if($isP1) echo '<span style="color:white; font-weight:bold; font-size:12px; text-shadow: 1px 1px 2px #000;">1</span>'; ?>
                        <?php if($isP2) echo '<span style="color:white; font-weight:bold; font-size:12px; text-shadow: 1px 1px 2px #000;">2</span>'; ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <div style="width: 220px; background: rgba(255,255,255,0.05); padding: 20px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.1);">
            <div style="margin-bottom: 20px;">
                <p style="color: #ff007c; font-weight: bold; margin: 5px 0;">J1 : <?= count($p1_owned) ?> pts</p>
                <p style="color: #00d2ff; font-weight: bold; margin: 5px 0;">J2 : <?= count($p2_owned) ?> pts</p>
            </div>

            <hr style="border: 0; border-top: 1px solid #444; margin: 15px 0;">

            <p style="font-size: 14px; margin-bottom: 10px; color: #aaa;">
                <?= $isMyTurn ? "Choisissez une couleur :" : "Verrouillé..." ?>
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; <?= !$isMyTurn ? 'pointer-events: none;' : '' ?>">
                <?php foreach ($colors as $color):
                    $isDisabled = ($color == $p1_c || $color == $p2_c);
                ?>
                    <?php if ($isDisabled): ?>
                        <div style="background: <?= $color ?>; width: 60px; height: 60px; border-radius: 10px; opacity: 0.1; border: 2px solid #fff; cursor: not-allowed;"></div>
                    <?php else: ?>
                        <?= $this->Form->postLink('', ['action' => 'filler', $inviteCode], [
                            'data' => ['color' => $color],
                            'style' => "background:$color; width:60px; height:60px; border-radius:10px; display:block; border: 2px solid rgba(255,255,255,0.3); transition: transform 0.2s;",
                            'class' => 'color-btn'
                        ]) ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: 25px;">
                 <?= $this->Html->link('Quitter la partie', ['action' => 'index'], ['style' => 'color: #666; font-size: 12px; text-decoration: none;']) ?>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Fonction pour copier le lien
    function copyLink() {
        var copyText = document.getElementById("inviteLink");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        alert("Lien copié ! Envoie-le à ton adversaire.");
    }

    // 2. Auto-refresh si ce n'est pas notre tour
    <?php if (!$isMyTurn): ?>
        setTimeout(function() {
            location.reload();
        }, 3000); // Vérifie toutes les 3 secondes
    <?php endif; ?>
</script>
