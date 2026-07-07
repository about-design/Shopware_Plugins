<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Migration;

use Doctrine\DBAL\Connection;
use Meta\B2bExperienceHub\Setup\EmployeePermissionSetup;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1781000300AddExperienceEmployeePermission extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1781000300;
    }

    public function update(Connection $connection): void
    {
        (new EmployeePermissionSetup())->install($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
