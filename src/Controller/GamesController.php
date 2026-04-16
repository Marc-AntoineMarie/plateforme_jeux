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

        $game = $fillerGamesTable->findByInviteCode($inviteCode)->first();

        if (!$game) {
            $this->Flash->success("La partie est terminée !");
            return $this->redirect(['action' => 'index']);
        }

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

        if ($this->request->is('post')) {
            if (!$isMyTurn) {
                return $this->redirect(['action' => 'filler', $inviteCode]);
            }

            $chosenColor = $this->request->getData('color');
            if ($chosenColor) {
                $this->_logAction('Filler', [
                    'invite_code' => $inviteCode,
                    'couleur_choisie' => $chosenColor,
                    'joueur' => ($turn == 1) ? 'J1' : 'J2'
                ]);

                $owned = ($turn == 1) ? $p1_owned : $p2_owned;
                $newOwned = $this->_expandTerritory($grid, $owned, $chosenColor, $size);

                foreach ($newOwned as $pos) {
                    $grid[$pos[0]][$pos[1]] = $chosenColor;
                }

                $game->grid = json_encode($grid);
                if ($turn == 1) $game->p1_owned = json_encode($newOwned);
                else $game->p2_owned = json_encode($newOwned);

                $totalCells = $size * $size;
                $p1_count = ($turn == 1) ? count($newOwned) : count($p1_owned);
                $p2_count = ($turn == 2) ? count($newOwned) : count($p2_owned);

                if (($p1_count + $p2_count) >= $totalCells) {
                    $resultP1 = "Égalité ($p1_count pts)";
                    $resultP2 = "Égalité ($p2_count pts)";

                    if ($p1_count > $p2_count) {
                        $resultP1 = "Victoire ($p1_count pts)";
                        $resultP2 = "Défaite ($p2_count pts)";
                    } elseif ($p2_count > $p1_count) {
                        $resultP1 = "Défaite ($p1_count pts)";
                        $resultP2 = "Victoire ($p2_count pts)";
                    }

                    $this->_saveScoreForUser((int)$game->p1_id, "Filler", $resultP1);
                    if ($game->p2_id) {
                        $this->_saveScoreForUser((int)$game->p2_id, "Filler", $resultP2);
                    }

                    $fillerGamesTable->delete($game);
                    $this->Flash->success("Partie Terminée ! Scores enregistrés.");
                    return $this->redirect(['action' => 'index']);
                }

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
