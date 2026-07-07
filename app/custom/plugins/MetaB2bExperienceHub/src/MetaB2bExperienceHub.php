<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub;

use Doctrine\DBAL\Connection;
use Meta\B2bExperienceHub\Setup\EmployeePermissionSetup;
use Meta\B2bExperienceHub\Setup\PlatformMenuInstaller;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

class MetaB2bExperienceHub extends Plugin
{
    public function install(InstallContext $installContext): void
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        (new PlatformMenuInstaller())->install($connection);
        (new EmployeePermissionSetup())->install($connection);
    }

    public function update(UpdateContext $updateContext): void
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        (new PlatformMenuInstaller())->install($connection);
        (new EmployeePermissionSetup())->install($connection);
    }

    public function activate(ActivateContext $activateContext): void
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        (new PlatformMenuInstaller())->install($connection);
        (new EmployeePermissionSetup())->install($connection);
    }

    public function enrichPrivileges(): array
    {
        return [
            'cms.viewer' => [
                'meta_b2b_experience:read',
                'b2bsellers_platform_menu_item:read',
                'cms_page:read',
                'rule:read',
                'sales_channel:read',
            ],
            'cms.editor' => [
                'meta_b2b_experience:update',
                'rule:create',
                'rule:update',
            ],
            'cms.creator' => [
                'meta_b2b_experience:create',
                'rule:create',
            ],
            'cms.deleter' => [
                'meta_b2b_experience:delete',
            ],
        ];
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        (new PlatformMenuInstaller())->uninstall($connection);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        (new PlatformMenuInstaller())->uninstall($connection);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $connection->executeStatement('DROP TABLE IF EXISTS `meta_b2b_experience_translation`');
        $connection->executeStatement('DROP TABLE IF EXISTS `meta_b2b_experience`');
    }
}
