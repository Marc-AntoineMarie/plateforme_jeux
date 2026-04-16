<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;

class UsersController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        // On autorise login et add pour les visiteurs
        $this->Authentication->addUnauthenticatedActions(['login', 'add']);
    }

    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();

        // 1. On force la déconnexion si on arrive sur la page de login pour éviter les restes de session
        if ($this->request->is('get') && $this->Authentication->getResult()->isValid()) {
            $this->Authentication->logout();
        }

        $result = $this->Authentication->getResult();

        // 2. Si on est en POST (tentative de connexion)
        if ($this->request->is('post')) {
            if ($result && $result->isValid()) {
                // SUCCÈS : On redirige
                $target = $this->request->getQuery('redirect', ['controller' => 'Games', 'action' => 'index']);
                return $this->redirect($target);
            } else {
                // ÉCHEC : On affiche l'erreur
                $this->Flash->error(__('Identifiants incorrects.'));
            }
        }
    }

    public function logout()
    {
        $result = $this->Authentication->getResult();
        // Si on est connecté, on détruit tout
        if ($result && $result->isValid()) {
            $this->Authentication->logout();
            $this->request->getSession()->destroy();
        }
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }
    public function view($id = null)
    {
        if (!$id) {
            // On récupère l'ID de la personne RÉELLEMENT connectée en session
            $id = $this->Authentication->getIdentity()->getIdentifier();
        }
        $user = $this->Users->get($id);
        $this->set(compact('user'));
    }

    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Compte créé ! Connectez-vous.'));
                return $this->redirect(['action' => 'login']);
            }
            $this->Flash->error(__('Erreur lors de l\'inscription.'));
        }
        $this->set(compact('user'));
    }
}
