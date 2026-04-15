<div class="games-hub">
    <div style="text-align: center; margin-bottom: 50px;">
        <h1 style="font-size: 3rem; text-transform: uppercase; letter-spacing: 5px; color: #00d2ff;">Game Center</h1>
        <p style="color: #aaa;">Choisissez votre défi du jour</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 50px;">
        <a href="<?= $this->Url->build(['action' => 'mastermind']) ?>" style="text-decoration: none;">
            <div class="game-card" style="background: linear-gradient(135deg, #1b1b2f 0%, #0f0c29 100%); padding: 40px; border-radius: 20px; border: 2px solid #9d50bb; text-align: center; transition: transform 0.3s, box-shadow 0.3s;">
                <h2 style="color: #9d50bb;">🧠 Mastermind</h2>
                <p style="color: #eee;">Trouvez la combinaison secrète en un minimum de coups.</p>
                <span class="button" style="background: #9d50bb !important;">Jouer Solo</span>
            </div>
        </a>

        <a href="<?= $this->Url->build(['action' => 'filler']) ?>" style="text-decoration: none;">
            <div class="game-card" style="background: linear-gradient(135deg, #1b1b2f 0%, #0f0c29 100%); padding: 40px; border-radius: 20px; border: 2px solid #00d2ff; text-align: center; transition: transform 0.3s, box-shadow 0.3s;">
                <h2 style="color: #00d2ff;">🎨 Filler</h2>
                <p style="color: #eee;">Conquérez le territoire de votre adversaire.</p>
                <span class="button" style="background: #00d2ff !important;">Multijoueur</span>
            </div>
        </a>
    </div>

    <div style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 15px;">
        <h3 style="margin-top: 0;">🏆 Derniers Exploits</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <?php foreach ($recentScores as $score): ?>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <td style="padding: 10px;"><?= h($score->game_name) ?></td>
                    <td style="padding: 10px; color: #00d2ff; font-weight: bold;"><?= h($score->score) ?> pts</td>
                    <td style="padding: 10px; text-align: right; color: #666; font-size: 0.8rem;"><?= $score->created->timeAgoInWords() ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<style>
    .game-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.5);
    }
</style>
