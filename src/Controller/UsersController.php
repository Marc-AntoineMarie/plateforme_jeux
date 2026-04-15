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

        if ($result && $result->isValid()) {
            $target = $this->request->getQuery('redirect', [
                'controller' => 'Games',
                'action' => 'index',
            ]);
            return $this->redirect($target);
        }

        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error(__('Identifiant ou mot de passe invalide'));
        }
    }

    public function logout()
    {
        $this->Authentication->logout();
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    public function view($id = null)
    {
        if (!$id) {
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
