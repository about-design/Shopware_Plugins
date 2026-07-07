<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Service;

use B2bSellersCore\Components\B2bPlatform\PlatformMenu\PlatformMenuItemDefinition;
use Doctrine\DBAL\Connection;
use Meta\B2bExperienceHub\Core\Content\Experience\ExperienceDefinition;
use Meta\B2bExperienceHub\Core\Content\Experience\ExperienceEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;

class ExperiencePlatformMenuSyncService
{
    public const MODULE_NAME = 'meta_b2b_experiences';

    public function __construct(
        private readonly EntityRepository $experienceRepository,
        private readonly Connection $connection,
    ) {
    }

    public function syncAll(Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addAssociation('translations');

        $result = $this->experienceRepository->search($criteria, $context);

        foreach ($result->getEntities() as $experience) {
            if (!$experience instanceof ExperienceEntity) {
                continue;
            }

            $this->syncExperience($experience);
        }
    }

    public function syncExperienceById(string $experienceId, Context $context): void
    {
        $criteria = new Criteria([$experienceId]);
        $criteria->addAssociation('translations');

        /** @var ExperienceEntity|null $experience */
        $experience = $this->experienceRepository->search($criteria, $context)->first();

        if ($experience === null) {
            $this->deleteMenuItemsForExperience($experienceId);

            return;
        }

        $this->syncExperience($experience);
    }

    public function deleteMenuItemsForExperience(string $experienceId): void
    {
        $this->connection->delete('b2bsellers_platform_menu_item', [
            'technical_name' => $this->getCustomerTechnicalName($experienceId),
        ]);
        $this->connection->delete('b2bsellers_platform_menu_item', [
            'technical_name' => $this->getSalesRepTechnicalName($experienceId),
        ]);
    }

    private function syncExperience(ExperienceEntity $experience): void
    {
        $this->syncAudienceMenuItem(
            $experience,
            PlatformMenuItemDefinition::CUSTOMER_MENU_ID,
            $experience->isVisibleForCustomer(),
            $this->getCustomerTechnicalName($experience->getId()),
            $experience->getPlatformMenuParentId()
        );

        $this->syncAudienceMenuItem(
            $experience,
            PlatformMenuItemDefinition::SALES_REPRESENTATIVE_MENU_ID,
            $experience->isVisibleForSalesRepresentative(),
            $this->getSalesRepTechnicalName($experience->getId()),
            null
        );
    }

    private function syncAudienceMenuItem(
        ExperienceEntity $experience,
        string $defaultRootId,
        bool $audienceEnabled,
        string $technicalName,
        ?string $customParentId,
    ): void {
        if (!$experience->isActive() || !$experience->isShowInPlatformMenu() || !$audienceEnabled) {
            $this->connection->delete('b2bsellers_platform_menu_item', ['technical_name' => $technicalName]);

            return;
        }

        $parentId = $customParentId ?? $defaultRootId;
        $parentMeta = $this->resolveParentMeta($parentId, $defaultRootId);
        $translations = $this->buildMenuTranslations($experience);
        $existingId = $this->connection->fetchOne(
            'SELECT `id` FROM `b2bsellers_platform_menu_item` WHERE `technical_name` = :technicalName LIMIT 1',
            ['technicalName' => $technicalName]
        );

        $payload = [
            'parent_id' => Uuid::fromHexToBytes($parentId),
            'active' => 1,
            'type' => 'platform',
            'technical_name' => $technicalName,
            'module_name' => self::MODULE_NAME,
            'deletable' => 0,
            'child_count' => 0,
            'level' => $parentMeta['level'],
            'path' => $parentMeta['path'],
            'icon_type' => 'icon',
            'icon_name' => $experience->getMenuIconName() ?: 'regular-content',
            'rule_id' => $experience->getRuleId() ? Uuid::fromHexToBytes($experience->getRuleId()) : null,
            'updated_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];

        if ($existingId) {
            $this->connection->update(
                'b2bsellers_platform_menu_item',
                $payload,
                ['technical_name' => $technicalName]
            );
            $menuItemId = $existingId;
        } else {
            $menuItemId = Uuid::randomBytes();
            $payload['id'] = $menuItemId;
            $payload['created_at'] = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);
            $payload['after_platform_menu_item_id'] = null;
            $this->connection->insert('b2bsellers_platform_menu_item', $payload);
        }

        $this->syncMenuItemTranslations($menuItemId, $translations);
    }

    /**
     * @return array<string, string>
     */
    private function buildMenuTranslations(ExperienceEntity $experience): array
    {
        $internalLink = 'experiences/' . $experience->getId();
        $translations = [];

        foreach ($experience->getTranslations() ?? [] as $translation) {
            $languageId = $translation->getLanguageId();
            if ($languageId === null) {
                continue;
            }

            $title = $translation->getTitle();
            if ($title === null || $title === '') {
                continue;
            }

            $translations[$languageId] = [
                'name' => $title,
                'internal_link' => $internalLink,
            ];
        }

        if ($translations !== []) {
            return $translations;
        }

        $fallbackTitle = $experience->getTitle() ?? 'Content';

        foreach ($this->getDefaultLanguageIds() as $languageId) {
            $translations[$languageId] = [
                'name' => $fallbackTitle,
                'internal_link' => $internalLink,
            ];
        }

        return $translations;
    }

    /**
     * @param array<string, array{name: string, internal_link: string}> $translations
     */
    private function syncMenuItemTranslations(string $menuItemId, array $translations): void
    {
        foreach ($translations as $languageId => $translation) {
            $existing = $this->connection->fetchOne(
                'SELECT 1 FROM `b2bsellers_platform_menu_item_translation`
                 WHERE `b2bsellers_platform_menu_item_id` = :menuItemId AND `language_id` = :languageId',
                [
                    'menuItemId' => $menuItemId,
                    'languageId' => Uuid::fromHexToBytes($languageId),
                ]
            );

            $payload = [
                'name' => $translation['name'],
                'internal_link' => $translation['internal_link'],
                'updated_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ];

            if ($existing) {
                $this->connection->update(
                    'b2bsellers_platform_menu_item_translation',
                    $payload,
                    [
                        'b2bsellers_platform_menu_item_id' => $menuItemId,
                        'language_id' => Uuid::fromHexToBytes($languageId),
                    ]
                );

                continue;
            }

            $this->connection->insert('b2bsellers_platform_menu_item_translation', array_merge($payload, [
                'b2bsellers_platform_menu_item_id' => $menuItemId,
                'language_id' => Uuid::fromHexToBytes($languageId),
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]));
        }
    }

    /**
     * @return array{level: int, path: string}
     */
    private function resolveParentMeta(string $parentId, string $defaultRootId): array
    {
        if ($parentId === $defaultRootId) {
            return [
                'level' => 1,
                'path' => '|' . $defaultRootId . '|',
            ];
        }

        /** @var array{level?: int, path?: string}|false $parent */
        $parent = $this->connection->fetchAssociative(
            'SELECT `level`, `path` FROM `b2bsellers_platform_menu_item` WHERE `id` = :id',
            ['id' => Uuid::fromHexToBytes($parentId)]
        );

        if ($parent === false) {
            return [
                'level' => 1,
                'path' => '|' . $defaultRootId . '|',
            ];
        }

        return [
            'level' => ((int) ($parent['level'] ?? 0)) + 1,
            'path' => ($parent['path'] ?? '|' . $defaultRootId . '|') . $parentId . '|',
        ];
    }

    /**
     * @return list<string>
     */
    private function getDefaultLanguageIds(): array
    {
        $languageIds = $this->connection->fetchFirstColumn(
            'SELECT LOWER(HEX(`language`.`id`))
             FROM `language`
             INNER JOIN `locale` ON `language`.`translation_code_id` = `locale`.`id`
             WHERE `locale`.`code` IN (:de, :en)',
            [
                'de' => 'de-DE',
                'en' => 'en-GB',
            ]
        );

        return array_map(static fn (string $id): string => $id, $languageIds);
    }

    private function getCustomerTechnicalName(string $experienceId): string
    {
        return 'meta_b2b_experience_' . $experienceId;
    }

    private function getSalesRepTechnicalName(string $experienceId): string
    {
        return 'meta_b2b_experience_' . $experienceId . '_sales_rep';
    }
}
