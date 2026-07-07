<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Subscriber;

use Meta\B2bExperienceHub\Controller\StoreApi\ExperienceListRoute;
use Meta\B2bExperienceHub\Core\Content\Experience\ExperienceDefinition;
use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExperienceCacheInvalidationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CacheInvalidator $cacheInvalidator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntityWrittenEvent::class => 'invalidateCache',
        ];
    }

    public function invalidateCache(EntityWrittenEvent $event): void
    {
        $relevantEntities = [
            ExperienceDefinition::ENTITY_NAME,
            CmsPageDefinition::ENTITY_NAME,
            RuleDefinition::ENTITY_NAME,
        ];

        foreach ($event->getWriteResults() as $writeResult) {
            if (!\in_array($writeResult->getEntityName(), $relevantEntities, true)) {
                continue;
            }

            $this->cacheInvalidator->invalidate([
                ExperienceListRoute::buildCacheTag(),
            ]);

            return;
        }
    }
}
