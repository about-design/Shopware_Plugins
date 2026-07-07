<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Subscriber;

use Meta\B2bExperienceHub\Service\ExperiencePlatformMenuSyncService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Plugin\Event\PluginPostActivateEvent;
use Shopware\Core\Framework\Plugin\Event\PluginPostUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PluginLifecycleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ExperiencePlatformMenuSyncService $menuSyncService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PluginPostActivateEvent::class => 'syncPlatformMenu',
            PluginPostUpdateEvent::class => 'syncPlatformMenu',
        ];
    }

    public function syncPlatformMenu(PluginPostActivateEvent|PluginPostUpdateEvent $event): void
    {
        if ($event->getPlugin()->getName() !== 'MetaB2bExperienceHub') {
            return;
        }

        $this->menuSyncService->syncAll(Context::createDefaultContext());
    }
}
