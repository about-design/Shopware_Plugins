<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Setup;

use Doctrine\DBAL\Connection;
use Meta\B2bExperienceHub\Core\ExperiencePermissions;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;

class EmployeePermissionSetup
{
    public function install(Connection $connection): void
    {
        if ($this->permissionExists($connection, ExperiencePermissions::VIEW)) {
            $this->grantPermissionToAllRoles($connection, ExperiencePermissions::VIEW);

            return;
        }

        $groupId = $this->createPermissionGroup($connection);
        $this->createPermission($connection, $groupId);
        $this->grantPermissionToAllRoles($connection, ExperiencePermissions::VIEW);
    }

    private function permissionExists(Connection $connection, string $key): bool
    {
        return (bool) $connection->fetchOne(
            'SELECT 1 FROM `b2bsellers_employee_permission` WHERE `key` = :key LIMIT 1',
            ['key' => $key]
        );
    }

    private function createPermissionGroup(Connection $connection): string
    {
        $existingGroupId = $connection->fetchOne(
            'SELECT `b2bsellers_employee_permission_group_id`
             FROM `b2bsellers_employee_permission_group_translation`
             WHERE `name` IN (:nameEn, :nameDe)
             LIMIT 1',
            [
                'nameEn' => 'B2B Content',
                'nameDe' => 'B2B Inhalte',
            ]
        );

        if (\is_string($existingGroupId) && $existingGroupId !== '') {
            return $existingGroupId;
        }

        $groupId = Uuid::randomBytes();
        $createdAt = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);

        $connection->insert('b2bsellers_employee_permission_group', [
            'id' => $groupId,
            'created_at' => $createdAt,
        ]);

        foreach ([
            'de-DE' => 'B2B Inhalte',
            'en-GB' => 'B2B Content',
        ] as $localeCode => $name) {
            $languageId = $this->getLanguageIdByCode($connection, $localeCode);

            if ($languageId === null) {
                continue;
            }

            $connection->insert('b2bsellers_employee_permission_group_translation', [
                'b2bsellers_employee_permission_group_id' => $groupId,
                'language_id' => $languageId,
                'name' => $name,
                'created_at' => $createdAt,
            ]);
        }

        return $groupId;
    }

    private function createPermission(Connection $connection, string $groupId): void
    {
        $permissionId = Uuid::randomBytes();
        $createdAt = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);

        $connection->insert('b2bsellers_employee_permission', [
            'id' => $permissionId,
            'group_id' => $groupId,
            '`key`' => ExperiencePermissions::VIEW,
            'addon_name' => 'MetaB2bExperienceHub',
            'created_at' => $createdAt,
        ]);

        foreach ([
            'de-DE' => 'Darf B2B-Inhalte einsehen',
            'en-GB' => 'Can view B2B content',
        ] as $localeCode => $name) {
            $languageId = $this->getLanguageIdByCode($connection, $localeCode);

            if ($languageId === null) {
                continue;
            }

            $connection->insert('b2bsellers_employee_permission_translation', [
                'b2bsellers_employee_permission_id' => $permissionId,
                'language_id' => $languageId,
                'name' => $name,
                'created_at' => $createdAt,
            ]);
        }
    }

    private function grantPermissionToAllRoles(Connection $connection, string $permissionKey): void
    {
        /** @var list<array{id: string, privileges: string}> $roles */
        $roles = $connection->fetchAllAssociative('SELECT `id`, `privileges` FROM `b2bsellers_employee_role`');

        foreach ($roles as $role) {
            /** @var list<string> $privileges */
            $privileges = json_decode($role['privileges'], true) ?? [];

            if (\in_array($permissionKey, $privileges, true)) {
                continue;
            }

            $privileges[] = $permissionKey;

            $connection->update(
                'b2bsellers_employee_role',
                [
                    'privileges' => json_encode(array_values($privileges)),
                    'updated_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ],
                ['id' => $role['id']]
            );
        }
    }

    private function getLanguageIdByCode(Connection $connection, string $code): ?string
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
