<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Setup;

use B2bSellersCore\Components\B2bPlatform\PlatformMenu\PlatformMenuItemDefinition;
use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;

class PlatformMenuInstaller
{
    private const MODULE_NAME = 'meta_b2b_experiences';

    private const SALES_REP_MODULE_NAME = 'meta_b2b_experiences_sales_rep';

    public function install(Connection $connection): void
    {
        $this->ensureCustomerMenuItem($connection);
        $this->ensureSalesRepresentativeMenuItem($connection);
    }

    public function uninstall(Connection $connection): void
    {
        $connection->delete('b2bsellers_platform_menu_item', ['technical_name' => self::MODULE_NAME]);
        $connection->delete('b2bsellers_platform_menu_item', ['technical_name' => self::SALES_REP_MODULE_NAME]);
    }

    private function ensureCustomerMenuItem(Connection $connection): void
    {
        if ($this->menuItemExists($connection, self::MODULE_NAME)) {
            $this->updateTechnicalName($connection, self::MODULE_NAME, self::MODULE_NAME);

            return;
        }

        $englishLanguageId = $this->getLanguageIdByCode($connection, 'en-GB');
        $germanLanguageId = $this->getLanguageIdByCode($connection, 'de-DE');

        if ($englishLanguageId === null || $germanLanguageId === null) {
            return;
        }

        $this->insertPlatformMenuItem($connection, [
            'id' => Uuid::randomBytes(),
            'parent_id' => Uuid::fromHexToBytes(PlatformMenuItemDefinition::CUSTOMER_MENU_ID),
            'active' => 1,
            'type' => 'platform',
            'technical_name' => self::MODULE_NAME,
            'module_name' => self::MODULE_NAME,
            'deletable' => 0,
            'child_count' => 0,
            'level' => 1,
            'path' => '|' . PlatformMenuItemDefinition::CUSTOMER_MENU_ID . '|',
            'icon_type' => 'icon',
            'icon_name' => 'regular-content',
            'after_platform_menu_item_id' => null,
            'translations' => [
                [
                    'language_id' => $englishLanguageId,
                    'internal_link' => 'experiences',
                    'name' => 'Content',
                ],
                [
                    'language_id' => $germanLanguageId,
                    'internal_link' => 'experiences',
                    'name' => 'Inhalte',
                ],
            ],
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function ensureSalesRepresentativeMenuItem(Connection $connection): void
    {
        if ($this->menuItemExists($connection, self::SALES_REP_MODULE_NAME)) {
            $this->updateTechnicalName($connection, self::SALES_REP_MODULE_NAME, self::MODULE_NAME);

            return;
        }

        $englishLanguageId = $this->getLanguageIdByCode($connection, 'en-GB');
        $germanLanguageId = $this->getLanguageIdByCode($connection, 'de-DE');

        if ($englishLanguageId === null || $germanLanguageId === null) {
            return;
        }

        $this->insertPlatformMenuItem($connection, [
            'id' => Uuid::randomBytes(),
            'parent_id' => Uuid::fromHexToBytes(PlatformMenuItemDefinition::SALES_REPRESENTATIVE_MENU_ID),
            'active' => 1,
            'type' => 'platform',
            'technical_name' => self::SALES_REP_MODULE_NAME,
            'module_name' => self::MODULE_NAME,
            'deletable' => 0,
            'child_count' => 0,
            'level' => 1,
            'path' => '|' . PlatformMenuItemDefinition::SALES_REPRESENTATIVE_MENU_ID . '|',
            'icon_type' => 'icon',
            'icon_name' => 'regular-content',
            'after_platform_menu_item_id' => null,
            'translations' => [
                [
                    'language_id' => $englishLanguageId,
                    'internal_link' => 'experiences',
                    'name' => 'Content',
                ],
                [
                    'language_id' => $germanLanguageId,
                    'internal_link' => 'experiences',
                    'name' => 'Inhalte',
                ],
            ],
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function menuItemExists(Connection $connection, string $moduleName): bool
    {
        return (bool) $connection->fetchOne(
            'SELECT id FROM `b2bsellers_platform_menu_item` WHERE `module_name` = :module_name OR `technical_name` = :module_name LIMIT 1',
            ['module_name' => $moduleName]
        );
    }

    private function updateTechnicalName(Connection $connection, string $technicalName, string $moduleName): void
    {
        $connection->update(
            'b2bsellers_platform_menu_item',
            ['technical_name' => $technicalName, 'module_name' => $moduleName],
            ['technical_name' => $technicalName]
        );
    }

    private function getLanguageIdByCode(Connection $connection, string $code): ?string
    {
        /** @var string|null $langId */
        $langId = $connection->fetchOne(
            'SELECT `language`.`id`
             FROM `language`
             INNER JOIN `locale` ON `language`.`translation_code_id` = `locale`.`id`
             WHERE `code` = :code
             LIMIT 1',
            ['code' => $code]
        );

        return $langId ?: null;
    }

    private function insertPlatformMenuItem(Connection $connection, array $data): void
    {
        $translations = $data['translations'];
        unset($data['translations']);

        $connection->insert('b2bsellers_platform_menu_item', $data);

        foreach ($translations as $translation) {
            $translation['b2bsellers_platform_menu_item_id'] = $data['id'];
            $translation['created_at'] = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);

            $connection->insert('b2bsellers_platform_menu_item_translation', $translation);
        }
    }
}
