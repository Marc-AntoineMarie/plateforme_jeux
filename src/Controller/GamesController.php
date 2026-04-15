<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Utility\Text;

class GamesController extends AppController
{
    // On charge le modèle Scores pour pouvoir enregistrer les résultats plus tard
    public function initialize(): void
    {
        parent::initialize();
    }
    public function index()
    {
        // On charge la table et on l'assigne à une variable locale
        $scoresTable = $this->fetchTable('Scores');

        // On utilise la variable locale pour faire le find()
        $recentScores = $scoresTable->find()
            ->order(['created' => 'DESC'])
            ->limit(5)
            ->all();

        $this->set(compact('recentScores'));
    }

    /**
     * Logique du Mastermind
     */
    public function mastermind()
    {
        $session = $this->request->getSession();

        // 1. Initialisation de la partie
        if (!$session->check('Mastermind.solution')) {
            $colors = ['red', 'blue', 'green', 'yellow', 'purple', 'orange'];
            $solution = [];
            for ($i = 0; $i < 4; $i++) {
                $solution[] = $colors[array_rand($colors)];
            }
            $session->write('Mastermind.solution', $solution);
            $session->write('Mastermind.attempts', []);
        }

        $solution = $session->read('Mastermind.solution');
        $attempts = $session->read('Mastermind.attempts');

        // 2. Traitement du coup joué
        if ($this->request->is('post')) {
            $guess = $this->request->getData('guess');

            if ($guess && count($guess) === 4) {
                $result = $this->_checkGuess($solution, $guess);
                $attempts[] = ['guess' => $guess, 'result' => $result];
                $session->write('Mastermind.attempts', $attempts);

                // Victoire ?
                if ($result['exact'] === 4) {
                    $this->Flash->success('Félicitations ! Vous avez trouvé en ' . count($attempts) . ' coups.');

                    // On enregistre le score en BDD
                    $this->_saveScore('Mastermind', count($attempts));

                    $session->delete('Mastermind'); // On vide la partie
                    return $this->redirect(['action' => 'mastermind']);
                }
            }
        }

        $this->set(compact('attempts'));
    }

    /**
     * Calcul des pions bien et mal placés
     */
    private function _checkGuess($solution, $guess) {
        $exact = 0;
        $near = 0;
        $solCopy = $solution;
        $guessCopy = $guess;

        // Pions bien placés
        foreach ($guess as $i => $color) {
            if ($color === $solution[$i]) {
                $exact++;
                $solCopy[$i] = null;
                $guessCopy[$i] = null;
            }
        }
        // Pions mal placés
        foreach ($guessCopy as $i => $color) {
            if ($color !== null && in_array($color, $solCopy)) {
                $near++;
                $index = array_search($color, $solCopy);
                $solCopy[$index] = null;
            }
        }
        return ['exact' => $exact, 'near' => $near];
    }

    /**
     * Sauvegarde le score en BDD pour l'utilisateur connecté
     */
        private function _saveScore($gameName, $points) {
        // On récupère la table Scores via le TableLocator
        $scoresTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Scores');

        $score = $scoresTable->newEmptyEntity();
        // Assure-toi que l'utilisateur ID 1 existe bien dans ta table 'users'
        $score->user_id = 1;
        $score->game_name = $gameName;
        $score->score = $points;

        if ($scoresTable->save($score)) {
            return true;
        } else {
            // Si ça échoue, on affiche l'erreur pour débugger
            debug($score->getErrors());
            return false;
        }
    }

    public function filler($inviteCode = null)
    {
        $this->fetchTable('FillerGames');
        $size = 12;
        $colors = ['#ff007c', '#00d2ff', '#9d50bb', '#2ecc71', '#f1c40f', '#e67e22'];

        // On récupère l'ID de l'utilisateur connecté (Simulé à 1 si pas d'Auth encore)
        $currentUserId = 1;
        try {
            if ($this->Authentication->getIdentity()) {
                $currentUserId = $this->Authentication->getIdentity()->getIdentifier();
            }
        } catch (\Exception $e) {}

        // --- CAS 1 : CRÉATION D'UNE NOUVELLE PARTIE ---
        if (!$inviteCode) {
            $grid = [];
            for ($y = 0; $y < $size; $y++) {
                for ($x = 0; $x < $size; $x++) {
                    $grid[$y][$x] = $colors[array_rand($colors)];
                }
            }

            $game = $this->FillerGames->newEmptyEntity();
            $game->invite_code = substr(md5((string)microtime()), 0, 8); // Code court
            $game->p1_id = $currentUserId;
            $game->grid = json_encode($grid);
            $game->p1_owned = json_encode([[$size - 1, 0]]);
            $game->p2_owned = json_encode([[0, $size - 1]]);
            $game->current_turn = 1;

            if ($this->FillerGames->save($game)) {
                return $this->redirect(['action' => 'filler', $game->invite_code]);
            }
        }

        // --- CAS 2 : LECTURE DE LA PARTIE VIA LE CODE ---
        $game = $this->FillerGames->findByInviteCode($inviteCode)->firstOrFail();

        // Si un J2 arrive et que la place est libre
        if ($game->p2_id === null && $game->p1_id !== $currentUserId) {
            $game->p2_id = $currentUserId;
            $this->FillerGames->save($game);
        }

        // Désérialisation des données
        $grid = json_decode($game->grid, true);
        $p1_owned = json_decode($game->p1_owned, true);
        $p2_owned = json_decode($game->p2_owned, true);
        $turn = $game->current_turn;

        // Déterminer si c'AS le tour du joueur actuel
        $isMyTurn = ($turn == 1 && $currentUserId == $game->p1_id) || ($turn == 2 && $currentUserId == $game->p2_id);

        // --- CAS 3 : TRAITEMENT DU COUP ---
        if ($this->request->is('post') && $isMyTurn) {
            $chosenColor = $this->request->getData('color');

            $p1_c = $grid[$p1_owned[0][0]][$p1_owned[0][1]];
            $p2_c = $grid[$p2_owned[0][0]][$p2_owned[0][1]];
            $forbidden = ($turn == 1) ? $p2_c : $p1_c;

            if ($chosenColor !== $forbidden) {
                $owned = ($turn == 1) ? $p1_owned : $p2_owned;
                $newOwned = $this->_expandTerritory($grid, $owned, $chosenColor, $size);

                foreach ($newOwned as $pos) {
                    $grid[$pos[0]][$pos[1]] = $chosenColor;
                }

                // Mise à jour de l'entité
                $game->grid = json_encode($grid);
                if ($turn == 1) $game->p1_owned = json_encode($newOwned);
                else $game->p2_owned = json_encode($newOwned);

                // Vérification victoire
                $p1_count = ($turn == 1) ? count($newOwned) : count($p1_owned);
                $p2_count = ($turn == 2) ? count($newOwned) : count($p2_owned);

                if ($p1_count + $p2_count >= $size * $size) {
                    $this->_saveScore("Filler", max($p1_count, $p2_count));
                    $this->Flash->success("Partie Terminée !");
                    $this->FillerGames->delete($game); // On ferme la partie
                    return $this->redirect(['action' => 'index']);
                }

                $game->current_turn = ($turn == 1) ? 2 : 1;
                $this->FillerGames->save($game);
            }
            return $this->redirect(['action' => 'filler', $inviteCode]);
        }

        $this->set(compact('grid', 'colors', 'turn', 'p1_owned', 'p2_owned', 'inviteCode', 'isMyTurn', 'game'));
    }

    /**
     * Algorithme d'expansion (Flood Fill modifié)
     */
    private function _expandTerritory($grid, $owned, $targetColor, $size) {
        $stack = $owned;
        $newOwned = $owned;
        $checked = []; // Pour éviter les boucles infinies

        while (!empty($stack)) {
            $curr = array_pop($stack);
            $y = $curr[0];
            $x = $curr[1];

            // Directions : Haut, Bas, Gauche, Droite
            $neighbors = [[$y-1, $x], [$y+1, $x], [$y, $x-1], [$y, $x+1]];

            foreach ($neighbors as $n) {
                $ny = $n[0];
                $nx = $n[1];

                if ($ny >= 0 && $ny < $size && $nx >= 0 && $nx < $size) {
                    $key = "$ny-$nx";
                    if (!isset($checked[$key])) {
                        $checked[$key] = true;
                        // Si la case a la couleur cible OU est déjà dans le territoire (pour recolorer)
                        if ($grid[$ny][$nx] === $targetColor && !in_array([$ny, $nx], $newOwned)) {
                            $newOwned[] = [$ny, $nx];
                            $stack[] = [$ny, $nx];
                        }
                    }
                }
            }
        }
        return $newOwned;
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $game = $this->Games->newEmptyEntity();
        if ($this->request->is('post')) {
            $game = $this->Games->patchEntity($game, $this->request->getData());
            if ($this->Games->save($game)) {
                $this->Flash->success(__('The game has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The game could not be saved. Please, try again.'));
        }
        $this->set(compact('game'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Game id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $game = $this->Games->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $game = $this->Games->patchEntity($game, $this->request->getData());
            if ($this->Games->save($game)) {
                $this->Flash->success(__('The game has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The game could not be saved. Please, try again.'));
        }
        $this->set(compact('game'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Game id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $game = $this->Games->get($id);
        if ($this->Games->delete($game)) {
            $this->Flash->success(__('The game has been deleted.'));
        } else {
            $this->Flash->error(__('The game could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
