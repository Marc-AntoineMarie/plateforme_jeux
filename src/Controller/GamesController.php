<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

class GamesController extends AppController
{
    public function index()
    {
        $identity = $this->Authentication->getIdentity();
        $currentUserId = $identity ? $identity->getIdentifier() : null;
        $scoresTable = $this->fetchTable('Scores');

        if ($currentUserId) {
            $recentScores = $scoresTable->find()
                ->where(['user_id' => $currentUserId])
                ->order(['created' => 'DESC'])
                ->limit(5)
                ->all();
        } else {
            $recentScores = [];
        }

        $this->set(compact('recentScores'));
    }

    private function _loadMap($mapName) {
        $path = WWW_ROOT . 'maps' . DS . $mapName . '.txt';
        if (!file_exists($path)) return null;

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $map = [];
        foreach ($lines as $y => $line) {
            $map[$y] = str_split($line);
        }
        return $map;
    }

    public function maze($inviteCode = null) {
        $identity = $this->Authentication->getIdentity();
        if (!$identity) return $this->redirect(['controller' => 'Users', 'action' => 'login']);

        $userId = $identity->getIdentifier();
        $mazeTable = $this->fetchTable('MazeGames');
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->get($userId);
        $now = new \Cake\I18n\DateTime();

        // --- 1. CRÉATION (Nouvelle Game) ---
        if (!$inviteCode) {
            $map = $this->_loadMap('level1');
            if (!$map) {
                $this->Flash->error("Erreur : La carte n'a pas pu être chargée.");
                return $this->redirect(['action' => 'index']);
            }

            $available = [];
            foreach ($map as $y => $row) {
                foreach ($row as $x => $char) {
                    if ($char === '.') $available[] = [$y, $x];
                }
            }

            shuffle($available);
            $t_pos = array_pop($available);
            $validSpawns = [];
            foreach ($available as $pos) {
                if ((abs($pos[0] - $t_pos[0]) + abs($pos[1] - $t_pos[1])) >= 5) {
                    $validSpawns[] = $pos;
                }
            }
            if (count($validSpawns) < 2) $validSpawns = $available;

            $game = $mazeTable->newEmptyEntity();
            $game->invite_code = substr(md5((string)microtime()), 0, 8);
            $game->p1_id = $userId;
            $game->map_name = 'level1';
            $game->p1_pos = $validSpawns[0][0] . ',' . $validSpawns[0][1];
            $game->p2_pos = $validSpawns[1][0] . ',' . $validSpawns[1][1];
            $game->treasure_pos = $t_pos[0] . ',' . $t_pos[1];

            // Reset PA
            $user->pa = 15;
            $user->last_pa_update = $now;
            $usersTable->save($user);

            $mazeTable->save($game);
            return $this->redirect(['action' => 'maze', $game->invite_code]);
        }

        // --- 2. RÉCUPÉRATION DE LA PARTIE ---
        $game = $mazeTable->findByInviteCode($inviteCode)->first();
        if (!$game) return $this->redirect(['action' => 'index']);

        // J2 rejoint
        if ($game->p2_id === null && $game->p1_id != $userId) {
            $game->p2_id = $userId;
            $mazeTable->save($game);
            $user->pa = 15;
            $user->last_pa_update = $now;
            $usersTable->save($user);
        }

        // --- 3. RECHARGE DES PA ---
        $diffSeconds = $now->getTimestamp() - $user->last_pa_update->getTimestamp();
        if ($diffSeconds >= 60) {
            $intervals = floor($diffSeconds / 60);
            $user->pa = min($user->pa + ($intervals * 5), 15);
            $user->last_pa_update = $now;
            $usersTable->save($user);
            $diffSeconds = 0;
        }

        // --- 4. DÉPLACEMENT ---
        if ($this->request->is('post')) {
            $direction = $this->request->getData('move');
            $map = $this->_loadMap($game->map_name);

            if ($user->pa >= 1) {
                $isP1 = ($userId == $game->p1_id);
                $currentPos = explode(',', $isP1 ? $game->p1_pos : $game->p2_pos);
                $y = (int)$currentPos[0]; $x = (int)$currentPos[1];

                if ($direction == 'up') $y--;
                elseif ($direction == 'down') $y++;
                elseif ($direction == 'left') $x--;
                elseif ($direction == 'right') $x++;

                if (isset($map[$y][$x]) && $map[$y][$x] !== '#') {
                    $newPos = "$y,$x";
                    if ($isP1) $game->p1_pos = $newPos; else $game->p2_pos = $newPos;
                    $user->pa -= 1;
                    $usersTable->save($user);
                    $mazeTable->save($game);

                    if ($newPos === $game->treasure_pos) {
                        $this->_saveScoreForUser($userId, 'Maze', 'Vainqueur');
                        $mazeTable->delete($game);
                        $this->Flash->success("Trésor trouvé ! Victoire !");
                        return $this->redirect(['action' => 'index']);
                    }
                } else {
                    $this->Flash->error("Mur !");
                }
            }
            return $this->redirect(['action' => 'maze', $inviteCode]);
        }

        // --- 5. PRÉPARATION VUE ---
        $map = $this->_loadMap($game->map_name);
        $secondsToWait = 60 - $diffSeconds;

        // On explose les positions pour l'affichage
        $p1 = explode(',', $game->p1_pos);
        $p2 = ($game->p2_id) ? explode(',', $game->p2_pos) : null;
        $treasure = explode(',', $game->treasure_pos);

        $this->set(compact('game', 'map', 'user', 'userId', 'secondsToWait', 'p1', 'p2', 'treasure'));
    }

    public function mastermind()
    {
        $session = $this->request->getSession();

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

        if ($this->request->is('post')) {
            $guess = $this->request->getData('guess');

            if ($guess && count($guess) === 4) {
                $this->_logAction('Mastermind', [
                    'tentative' => $guess,
                    'numero_coup' => count($attempts) + 1
                ]);
                $result = $this->_checkGuess($solution, $guess);
                $attempts[] = ['guess' => $guess, 'result' => $result];
                $session->write('Mastermind.attempts', $attempts);

                if ($result['exact'] === 4) {
                    $this->Flash->success('Félicitations !');
                    $this->_saveScore('Mastermind', count($attempts));
                    $session->delete('Mastermind');
                    return $this->redirect(['action' => 'mastermind']);
                }
            }
        }
        $this->set(compact('attempts'));
    }

    public function filler($inviteCode = null)
    {
        $identity = $this->Authentication->getIdentity();
        if (!$identity) {
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $currentUserId = $identity->getIdentifier();
        $fillerGamesTable = $this->fetchTable('FillerGames');
        $size = 12;
        $colors = ['#ff007c', '#00d2ff', '#9d50bb', '#2ecc71', '#f1c40f', '#e67e22'];

        // --- CAS 1 : CRÉATION DE PARTIE ---
        if (!$inviteCode) {
            $grid = [];
            for ($y = 0; $y < $size; $y++) {
                for ($x = 0; $x < $size; $x++) {
                    $grid[$y][$x] = $colors[array_rand($colors)];
                }
            }

            $game = $fillerGamesTable->newEmptyEntity();
            $game->invite_code = substr(md5((string)microtime()), 0, 8);
            $game->p1_id = $currentUserId;
            $game->p2_id = null;
            $game->grid = json_encode($grid);
            $game->p1_owned = json_encode([[$size - 1, 0]]);
            $game->p2_owned = json_encode([[0, $size - 1]]);
            $game->current_turn = 1;

            if ($fillerGamesTable->save($game)) {
                return $this->redirect(['action' => 'filler', $game->invite_code]);
            }
        }

        // --- CAS 2 : REJOINDRE OU CONTINUER ---
        $game = $fillerGamesTable->findByInviteCode($inviteCode)->first();

        if (!$game) {
            $this->Flash->success("La partie est terminée !");
            return $this->redirect(['action' => 'index']);
        }

        // Le J2 rejoint la partie
        if ($game->p2_id === null && $game->p1_id != $currentUserId) {
            $game->p2_id = $currentUserId;
            $fillerGamesTable->save($game);
            $this->Flash->success("Vous avez rejoint l'arène !");
        }

        $grid = json_decode($game->grid, true);
        $p1_owned = json_decode($game->p1_owned, true);
        $p2_owned = json_decode($game->p2_owned, true);
        $turn = $game->current_turn;

        $isP1 = ($currentUserId == $game->p1_id);
        $isP2 = ($currentUserId == $game->p2_id);
        $isMyTurn = ($turn == 1 && $isP1) || ($turn == 2 && $isP2);

        // --- TRAITEMENT DU COUP ---
        if ($this->request->is('post')) {
            if (!$isMyTurn) {
                return $this->redirect(['action' => 'filler', $inviteCode]);
            }

            $chosenColor = $this->request->getData('color');
            if ($chosenColor) {
                // 1. Enregistrement du log de l'action
                $this->_logAction('Filler', [
                    'invite_code' => $inviteCode,
                    'couleur_choisie' => $chosenColor,
                    'joueur' => ($turn == 1) ? 'J1' : 'J2'
                ]);

                // 2. Mise à jour du territoire
                $owned = ($turn == 1) ? $p1_owned : $p2_owned;
                $newOwned = $this->_expandTerritory($grid, $owned, $chosenColor, $size);

                foreach ($newOwned as $pos) {
                    $grid[$pos[0]][$pos[1]] = $chosenColor;
                }

                // 3. Mise à jour temporaire pour le calcul de fin de partie
                if ($turn == 1) {
                    $p1_owned = $newOwned;
                    $game->p1_owned = json_encode($newOwned);
                } else {
                    $p2_owned = $newOwned;
                    $game->p2_owned = json_encode($newOwned);
                }
                $game->grid = json_encode($grid);

                $p1_count = count($p1_owned);
                $p2_count = count($p2_owned);
                $totalCells = $size * $size;

                // --- VÉRIFICATION FIN DE PARTIE ---
                // On vérifie si les joueurs sont bloqués
                $noMovesP1 = !$this->_hasMovesLeft($grid, $p1_owned, $size);
                $noMovesP2 = !$this->_hasMovesLeft($grid, $p2_owned, $size);

                if (($p1_count + $p2_count) >= $totalCells || $noMovesP1 || $noMovesP2) {
                    $resultP1 = "Égalité ($p1_count pts)";
                    $resultP2 = "Égalité ($p2_count pts)";

                    if ($p1_count > $p2_count) {
                        $resultP1 = "Victoire ($p1_count pts)";
                        $resultP2 = "Défaite ($p2_count pts)";
                    } elseif ($p2_count > $p1_count) {
                        $resultP1 = "Défaite ($p1_count pts)";
                        $resultP2 = "Victoire ($p2_count pts)";
                    }

                    // Sauvegarde des scores finaux
                    $this->_saveScoreForUser((int)$game->p1_id, "Filler", $resultP1);
                    if ($game->p2_id) {
                        $this->_saveScoreForUser((int)$game->p2_id, "Filler", $resultP2);
                    }

                    $fillerGamesTable->delete($game);

                    $msg = "Partie Terminée !";
                    if ($noMovesP1 || $noMovesP2) $msg .= " (Joueur bloqué)";

                    $this->Flash->success($msg);
                    return $this->redirect(['action' => 'index']);
                }

                // 4. Changement de tour et sauvegarde
                $game->current_turn = ($turn == 1) ? 2 : 1;
                $fillerGamesTable->save($game);
            }
            return $this->redirect(['action' => 'filler', $inviteCode]);
        }

        $this->set(compact('grid', 'colors', 'turn', 'p1_owned', 'p2_owned', 'inviteCode', 'isMyTurn', 'game', 'currentUserId'));
    }

    // --- MÉTHODES PRIVÉES ---

    private function _checkGuess($solution, $guess) {
        $exact = 0; $near = 0;
        $solCopy = $solution; $guessCopy = $guess;
        foreach ($guess as $i => $color) {
            if ($color === $solution[$i]) {
                $exact++;
                $solCopy[$i] = null; $guessCopy[$i] = null;
            }
        }
        foreach ($guessCopy as $i => $color) {
            if ($color !== null && in_array($color, $solCopy)) {
                $near++;
                $index = array_search($color, $solCopy);
                $solCopy[$index] = null;
            }
        }
        return ['exact' => $exact, 'near' => $near];
    }

    private function _saveScore($gameName, $points) {
        $identity = $this->Authentication->getIdentity();
        if (!$identity) return false;
        $scoresTable = $this->fetchTable('Scores');
        $score = $scoresTable->newEmptyEntity();
        $score->user_id = $identity->getIdentifier();
        $score->game_name = $gameName;
        $score->score = (string)$points;
        return $scoresTable->save($score);
    }

    private function _saveScoreForUser($userId, $gameName, $resultText) {
        $scoresTable = $this->fetchTable('Scores');
        $score = $scoresTable->newEmptyEntity();
        $score->user_id = $userId;
        $score->game_name = $gameName;
        $score->score = $resultText;
        return $scoresTable->save($score);
    }

    private function _expandTerritory($grid, $owned, $targetColor, $size) {
        $stack = $owned;
        $newOwned = $owned;
        $checked = [];
        foreach($owned as $p) { $checked["{$p[0]}-{$p[1]}"] = true; }
        while (!empty($stack)) {
            $curr = array_pop($stack);
            $neighbors = [[$curr[0]-1, $curr[1]], [$curr[0]+1, $curr[1]], [$curr[0], $curr[1]-1], [$curr[0], $curr[1]+1]];
            foreach ($neighbors as $n) {
                $ny = $n[0]; $nx = $n[1];
                if ($ny >= 0 && $ny < $size && $nx >= 0 && $nx < $size) {
                    $key = "$ny-$nx";
                    if (!isset($checked[$key]) && $grid[$ny][$nx] === $targetColor) {
                        $checked[$key] = true;
                        $newOwned[] = [$ny, $nx];
                        $stack[] = [$ny, $nx];
                    }
                }
            }
        }
        return $newOwned;
    }

    // private function _logAction($gameName, $details) {
    //     if (!isset($this->Authentication)) return;

    //     $identity = $this->Authentication->getIdentity();
    //     if (!$identity) return;

    //     $logsTable = $this->fetchTable('GameLogs');
    //     $log = $logsTable->newEmptyEntity();

    //     $log->user_id = $identity->getIdentifier();
    //     $log->game_name = $gameName;
    //     $log->action_details = is_array($details) ? json_encode($details) : (string)$details;

    //     if (!$logsTable->save($log)) {
    //         // CELA VA S'AFFICHER DANS TA PAGE SI LA SAUVEGARDE ÉCHOUE
    //         debug($log->getErrors());
    //         die('Erreur lors de la sauvegarde du log');
    //     }
    // }

    private function _hasMovesLeft($grid, $owned, $size) {
        // On récupère la couleur actuelle du J1 (bas-gauche) et J2 (haut-droite)
        $p1_color = $grid[$size - 1][0];
        $p2_color = $grid[0][$size - 1];

        foreach ($owned as $pos) {
            $y = $pos[0];
            $x = $pos[1];
            $neighbors = [[$y-1, $x], [$y+1, $x], [$y, $x-1], [$y, $x+1]];

            foreach ($neighbors as $n) {
                $ny = $n[0]; $nx = $n[1];
                if ($ny >= 0 && $ny < $size && $nx >= 0 && $nx < $size) {
                    $neighborColor = $grid[$ny][$nx];
                    // Si une case voisine n'est ni au J1 ni au J2, on peut encore bouger !
                    if ($neighborColor !== $p1_color && $neighborColor !== $p2_color) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function _logAction($gameName, $details) {
        $userId = null;
        $identity = $this->Authentication->getIdentity();

        if ($identity) {
            $userId = $identity->getIdentifier();
        } elseif ($this->request->getSession()->check('Auth.User.id')) {
            // Backup au cas où l'identity bug mais la session est là
            $userId = $this->request->getSession()->read('Auth.User.id');
        }

        if (!$userId) return;

        $logsTable = $this->fetchTable('GameLogs');
        $log = $logsTable->newEmptyEntity();

        $log->setAccess('*', true);

        $log->user_id = $userId;
        $log->game_name = $gameName;

        // On s'assure que action_details est bien une chaîne
        if (is_array($details)) {
            $log->action_details = json_encode($details);
        } else {
            $log->action_details = (string)$details;
        }

        if (!$logsTable->save($log)) {
            // Si ça échoue encore, on écrit dans les logs de CakePHP (logs/error.log)
            $this->log("Échec log action : " . json_encode($log->getErrors()), 'error');
        }
    }
}
