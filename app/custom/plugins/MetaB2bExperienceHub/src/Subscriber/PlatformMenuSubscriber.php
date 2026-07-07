<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Subscriber;

use B2bSellersCore\Components\B2bPlatform\PlatformMenu\Event\PlatformMenuRebuildEvent;
use Meta\B2bExperienceHub\Setup\PlatformMenuInstaller;
use Meta\B2bExperienceHub\Service\ExperiencePlatformMenuSyncService;
use Shopware\Core\Framework\Context;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlatformMenuSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ExperiencePlatformMenuSyncService $menuSyncService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlatformMenuRebuildEvent::class => 'rebuildPlatformMenu',
        ];
    }

    public function rebuildPlatformMenu(PlatformMenuRebuildEvent $event): void
    {
        (new PlatformMenuInstaller())->install($event->getConnection());
        $this->menuSyncService->syncAll(Context::createDefaultContext());
    }
}
