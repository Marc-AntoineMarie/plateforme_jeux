<div style="text-align: center; color: white; margin-bottom: 20px;">
    <?php if (!$game->p2_id): ?>
        <div style="background: rgba(0,0,0,0.3); padding: 10px; border-radius: 5px; margin-bottom: 20px;">
            <strong>Inviter un joueur :</strong><br>
            <input type="text" readonly value="<?= $this->Url->build(['action' => 'maze', $game->invite_code], ['fullBase' => true]) ?>" style="width: 80%; background: #333; color: #fff; border: 1px solid #555; padding: 5px; text-align: center;">
        </div>
    <?php endif; ?>

    <h3>PA : <?= $user->pa ?> / 15</h3>
    <p>Prochain gain dans : <span id="timer" style="font-weight: bold; color: #f1c40f;">--s</span></p>

    <div style="display: inline-block; border: 4px solid #1a1a1a; background: #1a1a1a; line-height: 0;">
        <?php foreach ($map as $y => $row): ?>
            <div style="white-space: nowrap;">
                <?php foreach ($row as $x => $cell):
                    $color = ($cell == '#') ? '#1a1a1a' : '#ecf0f1';
                    $icon = '';
                    if ($y == $p1[0] && $x == $p1[1]) $icon = '🧙';
                    elseif ($p2 && $y == $p2[0] && $x == $p2[1]) $icon = '🧛';
                    elseif ($y == $treasure[0] && $x == $treasure[1]) $icon = '💰';
                ?>
                    <div style="display: inline-block; width: 30px; height: 30px; background: <?= $color ?>; border: 1px solid rgba(0,0,0,0.05); line-height: 30px; font-size: 20px; vertical-align: top;">
                        <?= $icon ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top: 20px;">
        <?= $this->Form->create(null) ?>
            <button name="move" value="up" style="padding: 10px 20px; cursor: pointer;">⬆️</button><br>
            <button name="move" value="left" style="padding: 10px 20px; cursor: pointer;">⬅️</button>
            <button name="move" value="down" style="padding: 10px 20px; cursor: pointer;">⬇️</button>
            <button name="move" value="right" style="padding: 10px 20px; cursor: pointer;">➡️</button>
        <?= $this->Form->end() ?>
    </div>
</div>

<script>
    let secondsLeft = <?= $secondsToWait ?>;
    const timerDisplay = document.getElementById('timer');

    function updateTimer() {
        if (secondsLeft <= 0) {
            timerDisplay.innerHTML = "Actualisation...";
            window.location.reload();
            return;
        }
        timerDisplay.innerHTML = secondsLeft + "s";
        secondsLeft--;
    }

    setInterval(updateTimer, 1000);
    updateTimer();
</script>
