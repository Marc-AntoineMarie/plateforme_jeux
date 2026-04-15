<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ScoresFixture
 */
class ScoresFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'user_id' => 1,
                'game_name' => 'Lorem ipsum dolor sit amet',
                'score' => 1,
                'created' => '2026-04-15 12:56:59',
            ],
        ];
        parent::init();
    }
}
