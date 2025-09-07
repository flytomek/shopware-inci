<?php declare(strict_types=1);

namespace Codematic\Inci\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1757240896Inci extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1757240896;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `codematic_inci` (
    `id` BINARY(16) NOT NULL,
    `name` VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci,
    `slug` VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci,
    `alternative_names` LONGTEXT COLLATE utf8mb4_unicode_ci,
    `cas_number` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
    `polish_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
    `description` LONGTEXT COLLATE utf8mb4_unicode_ci,
    `main_functions` LONGTEXT COLLATE utf8mb4_unicode_ci,
    `safety_information` LONGTEXT COLLATE utf8mb4_unicode_ci,
    `rating` INT(11) COLLATE utf8mb4_unicode_ci,
    `rating_description` LONGTEXT COLLATE utf8mb4_unicode_ci,
    `resources` LONGTEXT COLLATE utf8mb4_unicode_ci,
    `natural` TINYINT(1) DEFAULT 0 COLLATE utf8mb4_unicode_ci,
    `active` TINYINT(1) DEFAULT 1 COLLATE utf8mb4_unicode_ci,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    INDEX `idx_active` (`active`),
    INDEX `idx_rating` (`rating`),
    INDEX `idx_natural` (`natural`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        $sql = 'DROP TABLE IF EXISTS `codematic_inci`';
        $connection->executeStatement($sql);
    }
}