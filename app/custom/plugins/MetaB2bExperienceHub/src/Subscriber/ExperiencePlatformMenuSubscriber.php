<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Subscriber;

use Meta\B2bExperienceHub\Core\Content\Experience\ExperienceDefinition;
use Meta\B2bExperienceHub\Service\ExperiencePlatformMenuSyncService;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityDeletedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExperiencePlatformMenuSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ExperiencePlatformMenuSyncService $menuSyncService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntityWrittenContainerEvent::class => 'onExperienceWritten',
            EntityDeletedEvent::class => 'onExperienceDeleted',
        ];
    }

    public function onExperienceWritten(EntityWrittenContainerEvent $event): void
    {
        $entityEvent = $event->getEventByEntityName(ExperienceDefinition::ENTITY_NAME);

        if (!$entityEvent instanceof EntityWrittenEvent) {
            return;
        }

        foreach ($entityEvent->getIds() as $id) {
            $experienceId = $this->normalizeExperienceId($id);

            if ($experienceId === null) {
                continue;
            }

            $this->menuSyncService->syncExperienceById($experienceId, $event->getContext());
        }
    }

    public function onExperienceDeleted(EntityDeletedEvent $event): void
    {
        if ($event->getEntityName() !== ExperienceDefinition::ENTITY_NAME) {
            return;
        }

        foreach ($event->getIds() as $id) {
            $experienceId = $this->normalizeExperienceId($id);

            if ($experienceId === null) {
                continue;
            }

            $this->menuSyncService->deleteMenuItemsForExperience($experienceId);
        }
    }

    /**
     * @param string|array<string, string> $id
     */
    private function normalizeExperienceId(string|array $id): ?string
    {
        if (is_string($id)) {
            return $id;
        }

        $experienceId = $id['id'] ?? null;

        return is_string($experienceId) ? $experienceId : null;
    }
}
