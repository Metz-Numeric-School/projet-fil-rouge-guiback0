<?php

namespace App\Tests\Shared;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

trait DatabaseFixturesTrait
{
    protected AbstractDatabaseTool $databaseTool;

    protected function initDatabaseTool(): void
    {
        if (!isset($this->databaseTool)) {
            $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        }
    }

    protected function loadBaseFixtures(array $classes = [], bool $append = false): void
    {
        $this->initDatabaseTool();
        if (empty($classes)) {
            $this->databaseTool->loadFixtures();
            return;
        }
        $this->databaseTool->loadFixtures($classes, $append);
    }
}
