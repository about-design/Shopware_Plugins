<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Command;

use Meta\B2bExperienceHub\Service\ExperiencePlatformMenuSyncService;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'meta-b2b-experience:sync-platform-menu',
    description: 'Synchronises B2B platform menu items from experience assignments',
)]
class SyncPlatformMenuCommand extends Command
{
    public function __construct(
        private readonly ExperiencePlatformMenuSyncService $menuSyncService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->menuSyncService->syncAll(Context::createDefaultContext());

        $output->writeln('<info>B2B platform menu items synchronised.</info>');

        return Command::SUCCESS;
    }
}
