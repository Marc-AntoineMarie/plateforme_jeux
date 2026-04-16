<div style="background: #2c3e50; padding: 20px; border-radius: 10px; text-align: center;">
    <h3>Labyrinthe - PA restants : <?= $user->pa ?> / 15</h3>

    <div style="display: inline-block; border: 4px solid #34495e; line-height: 0;">
        <?php
        $p1 = explode(',', $game->p1_pos);
        $p2 = explode(',', $game->p2_pos);
        $t = explode(',', $game->treasure_pos);

        foreach ($map as $y => $row): ?>
            <div style="display: block;">
                <?php foreach ($row as $x => $cell):
                    $char = $cell;
                    $color = ($char == '#') ? '#1a1a1a' : '#ecf0f1';
                    $content = '';

                    if ($y == $p1[0] && $x == $p1[1]) $content = '🧙';
                    elseif ($y == $p2[0] && $x == $p2[1]) $content = '🧛';
                    elseif ($y == $t[0] && $x == $t[1]) $content = '💰';
                ?>
                    <div style="display: inline-block; width: 30px; height: 30px;
                                background: <?= $color ?>; border: 1px solid #bdc3c7;
                                vertical-align: top; line-height: 30px; font-size: 20px;">
                        <?= $content ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top: 20px;">
        <?= $this->Form->create(null) ?>
            <button name="move" value="up">⬆️</button><br>
            <button name="move" value="left">⬅️</button>
            <button name="move" value="down">⬇️</button>
            <button name="move" value="right">➡️</button>
        <?= $this->Form->end() ?>
    </div>
</div>
