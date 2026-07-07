<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1781000100ExtendMetaB2bExperiencePhase2 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1781000100;
    }

    public function update(Connection $connection): void
    {
        if (!$this->columnExists($connection, 'meta_b2b_experience', 'visible_for_customer')) {
            $connection->executeStatement(<<<'SQL'
ALTER TABLE `meta_b2b_experience`
    ADD COLUMN `visible_for_customer` TINYINT(1) NOT NULL DEFAULT 1 AFTER `active`,
    ADD COLUMN `visible_for_sales_representative` TINYINT(1) NOT NULL DEFAULT 0 AFTER `visible_for_customer`;
SQL);
        }

        $connection->executeStatement(<<<'SQL'
CREATE TABLE IF NOT EXISTS `meta_b2b_experience_translation` (
    `meta_b2b_experience_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`meta_b2b_experience_id`, `language_id`),
    CONSTRAINT `fk.meta_b2b_experience_translation.experience_id`
        FOREIGN KEY (`meta_b2b_experience_id`) REFERENCES `meta_b2b_experience` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.meta_b2b_experience_translation.language_id`
        FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
SQL);

        if ($this->columnExists($connection, 'meta_b2b_experience', 'title')) {
            $this->migrateTitlesToTranslations($connection);
            $connection->executeStatement('ALTER TABLE `meta_b2b_experience` DROP COLUMN `title`;');
        }

        $this->createActivityType($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function migrateTitlesToTranslations(Connection $connection): void
    {
        $languageIds = $connection->fetchFirstColumn(
            'SELECT LOWER(HEX(`language`.`id`))
             FROM `language`
             INNER JOIN `locale` ON `language`.`translation_code_id` = `locale`.`id`
             WHERE `locale`.`code` IN (:de, :en)',
            [
                'de' => 'de-DE',
                'en' => 'en-GB',
            ]
        );

        if ($languageIds === []) {
            return;
        }

        $rows = $connection->fetchAllAssociative('SELECT LOWER(HEX(`id`)) AS id, `title`, `created_at`, `updated_at` FROM `meta_b2b_experience`');

        foreach ($rows as $row) {
            foreach ($languageIds as $languageId) {
                $exists = $connection->fetchOne(
                    'SELECT 1 FROM `meta_b2b_experience_translation`
                     WHERE `meta_b2b_experience_id` = :experienceId AND `language_id` = :languageId',
                    [
                        'experienceId' => Uuid::fromHexToBytes($row['id']),
                        'languageId' => Uuid::fromHexToBytes($languageId),
                    ]
                );

                if ($exists) {
                    continue;
                }

                $connection->insert('meta_b2b_experience_translation', [
                    'meta_b2b_experience_id' => Uuid::fromHexToBytes($row['id']),
                    'language_id' => Uuid::fromHexToBytes($languageId),
                    'title' => $row['title'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]);
            }
        }
    }

    private function createActivityType(Connection $connection): void
    {
        $existing = $connection->fetchOne(
            'SELECT `id` FROM `b2bsellers_customer_activity_type` WHERE `technical_name` = :technicalName LIMIT 1',
            ['technicalName' => 'experience_view']
        );

        if ($existing) {
            return;
        }

        $englishLanguageId = $this->fetchLanguageId($connection, 'en-GB');
        $germanLanguageId = $this->fetchLanguageId($connection, 'de-DE');

        if ($englishLanguageId === null || $germanLanguageId === null) {
            return;
        }

        $id = Uuid::randomBytes();
        $createdAt = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);

        $connection->insert('b2bsellers_customer_activity_type', [
            'id' => $id,
            'technical_name' => 'experience_view',
            'icon_name' => 'regular-content',
            'created_at' => $createdAt,
        ]);

        $connection->insert('b2bsellers_customer_activity_type_translation', [
            'b2bsellers_customer_activity_type_id' => $id,
            'language_id' => $englishLanguageId,
            'name' => 'Experience viewed',
            'created_at' => $createdAt,
        ]);

        $connection->insert('b2bsellers_customer_activity_type_translation', [
            'b2bsellers_customer_activity_type_id' => $id,
            'language_id' => $germanLanguageId,
            'name' => 'Inhalt angesehen',
            'created_at' => $createdAt,
        ]);
    }

    private function fetchLanguageId(Connection $connection, string $code): ?string
    {
        /** @var string|null $languageId */
        $languageId = $connection->fetchOne(
            'SELECT `language`.`id`
             FROM `language`
             INNER JOIN `locale` ON `language`.`translation_code_id` = `locale`.`id`
             WHERE `locale`.`code` = :code
             LIMIT 1',
            ['code' => $code]
        );

        return $languageId ?: null;
    }
}
