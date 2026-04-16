<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
// Ajoute cette ligne pour la validation
use Cake\Validation\Validator;

class GameLogsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('game_logs');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    // Ajoute ceci pour être sûr que Cake n'arrête pas la sauvegarde
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('game_name')
            ->requirePresence('game_name', 'create')
            ->notEmptyString('game_name');

        $validator
            ->scalar('action_details')
            ->requirePresence('action_details', 'create')
            ->notEmptyString('action_details');

        return $validator;
    }
}
