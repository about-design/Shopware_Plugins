<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1781000200AddExperiencePlatformMenuFields extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1781000200;
    }

    public function update(Connection $connection): void
    {
        if (!$this->columnExists($connection, 'meta_b2b_experience', 'show_in_platform_menu')) {
            $connection->executeStatement(<<<'SQL'
ALTER TABLE `meta_b2b_experience`
    ADD COLUMN `show_in_platform_menu` TINYINT(1) NOT NULL DEFAULT 0 AFTER `visible_for_sales_representative`,
    ADD COLUMN `include_in_content_hub` TINYINT(1) NOT NULL DEFAULT 1 AFTER `show_in_platform_menu`,
    ADD COLUMN `platform_menu_parent_id` BINARY(16) NULL AFTER `include_in_content_hub`,
    ADD COLUMN `menu_icon_name` VARCHAR(255) NULL AFTER `platform_menu_parent_id`,
    ADD KEY `fk.meta_b2b_experience.platform_menu_parent_id` (`platform_menu_parent_id`),
    ADD CONSTRAINT `fk.meta_b2b_experience.platform_menu_parent_id`
        FOREIGN KEY (`platform_menu_parent_id`) REFERENCES `b2bsellers_platform_menu_item` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
SQL);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
