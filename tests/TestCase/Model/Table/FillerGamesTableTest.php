<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\FillerGamesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\FillerGamesTable Test Case
 */
class FillerGamesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\FillerGamesTable
     */
    protected $FillerGames;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.FillerGames',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('FillerGames') ? [] : ['className' => FillerGamesTable::class];
        $this->FillerGames = $this->getTableLocator()->get('FillerGames', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->FillerGames);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\FillerGamesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\FillerGamesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
