<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1781000000CreateMetaB2bExperience extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1781000000;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
CREATE TABLE IF NOT EXISTS `meta_b2b_experience` (
    `id` BINARY(16) NOT NULL,
    `cms_page_id` BINARY(16) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `rule_id` BINARY(16) NULL,
    `sales_channel_id` BINARY(16) NULL,
    `priority` INT NOT NULL DEFAULT 0,
    `position` INT NOT NULL DEFAULT 0,
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`id`),
    INDEX `idx.meta_b2b_experience.active` (`active`),
    INDEX `idx.meta_b2b_experience.priority` (`priority`),
    INDEX `idx.meta_b2b_experience.position` (`position`),
    INDEX `idx.meta_b2b_experience.rule_id` (`rule_id`),
    INDEX `idx.meta_b2b_experience.sales_channel_id` (`sales_channel_id`),
    CONSTRAINT `fk.meta_b2b_experience.cms_page_id`
        FOREIGN KEY (`cms_page_id`) REFERENCES `cms_page` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk.meta_b2b_experience.rule_id`
        FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk.meta_b2b_experience.sales_channel_id`
        FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
