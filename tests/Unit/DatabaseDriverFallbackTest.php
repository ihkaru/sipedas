<?php

namespace Tests\Unit;

require_once __DIR__ . '/../TestCase.php';

use Tests\TestCase;

class DatabaseDriverFallbackTest extends TestCase
{
    public function test_database_configuration_safely_falls_back_when_driver_missing(): void
    {
        $dbConfig = require config_path('database.php');

        $this->assertIsArray($dbConfig);
        $this->assertArrayHasKey('default', $dbConfig);
        $this->assertArrayHasKey('connections', $dbConfig);

        // If pdo_mysql is not loaded, default should never resolve to mysql
        if (!extension_loaded('pdo_mysql')) {
            $this->assertNotSame('mysql', $dbConfig['default'], 'Default connection must not be mysql when pdo_mysql extension is missing');
            $this->assertSame('sqlite', $dbConfig['default']);
        }

        // SQLite database path configuration
        $sqliteDb = $dbConfig['connections']['sqlite']['database'];
        $this->assertTrue(
            $sqliteDb === ':memory:' || str_ends_with($sqliteDb, '.sqlite') || file_exists($sqliteDb),
            'SQLite database path must resolve to a valid sqlite file or memory'
        );
    }
}
