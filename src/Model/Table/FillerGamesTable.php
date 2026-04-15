<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * FillerGames Model
 *
 * @method \App\Model\Entity\FillerGame newEmptyEntity()
 * @method \App\Model\Entity\FillerGame newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\FillerGame> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FillerGame get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\FillerGame findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\FillerGame patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\FillerGame> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\FillerGame|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\FillerGame saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\FillerGame>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\FillerGame>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\FillerGame>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\FillerGame> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\FillerGame>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\FillerGame>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\FillerGame>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\FillerGame> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FillerGamesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('filler_games');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('invite_code')
            ->maxLength('invite_code', 20)
            ->allowEmptyString('invite_code')
            ->add('invite_code', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('grid')
            ->maxLength('grid', 4294967295)
            ->allowEmptyString('grid');

        $validator
            ->integer('p1_id')
            ->allowEmptyString('p1_id');

        $validator
            ->integer('p2_id')
            ->allowEmptyString('p2_id');

        $validator
            ->scalar('p1_owned')
            ->maxLength('p1_owned', 4294967295)
            ->allowEmptyString('p1_owned');

        $validator
            ->scalar('p2_owned')
            ->maxLength('p2_owned', 4294967295)
            ->allowEmptyString('p2_owned');

        $validator
            ->integer('current_turn')
            ->allowEmptyString('current_turn');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['invite_code'], ['allowMultipleNulls' => true]), ['errorField' => 'invite_code']);

        return $rules;
    }
}
