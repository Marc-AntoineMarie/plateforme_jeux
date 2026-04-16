<?php
namespace App\Model\Table;
use Cake\ORM\Table;

class MazeGamesTable extends Table {
    public function initialize(array $config): void {
        parent::initialize($config);
        $this->setTable('maze_games');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }
}
