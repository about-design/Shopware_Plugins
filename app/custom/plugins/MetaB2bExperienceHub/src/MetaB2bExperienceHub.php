<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub;

use Doctrine\DBAL\Connection;
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
    }

    public function update(UpdateContext $updateContext): void
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        (new PlatformMenuInstaller())->install($connection);
    }

    public function activate(ActivateContext $activateContext): void
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        (new PlatformMenuInstaller())->install($connection);
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
