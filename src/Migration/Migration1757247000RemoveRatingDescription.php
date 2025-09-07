<?php declare(strict_types=1);

namespace Codematic\Inci\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1757247000RemoveRatingDescription extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1757247000;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            ALTER TABLE `codematic_inci` DROP COLUMN IF EXISTS `rating_description`;
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // Nothing to do here
    }
}