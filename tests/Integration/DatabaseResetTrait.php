<?php

namespace App\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;

trait DatabaseResetTrait
{
    public function resetDatabase(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $connection = $em->getConnection();
        $schemaManager = $connection->createSchemaManager();
        $tables = $schemaManager->listTableNames();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0;');
        foreach ($tables as $table) {
            $connection->executeStatement("TRUNCATE TABLE `$table`;");
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1;');
    }
}
