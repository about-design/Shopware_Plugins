<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Service;

use B2bSellersCore\Components\CustomerActivity\Service\ActivityServiceInterface;
use Meta\B2bExperienceHub\Core\Content\Experience\ExperienceDefinition;
use Meta\B2bExperienceHub\Core\Content\Experience\ExperienceEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ExperienceActivityTracker
{
    private const ACTIVITY_TYPE = 'experience_view';

    private const ACTIVITY_SNIPPET = 'metaB2bExperienceHub.activity.viewed';

    public function __construct(
        private readonly ActivityServiceInterface $activityService,
        private readonly ExperienceResolver $experienceResolver,
    ) {
    }

    public function trackView(ExperienceEntity $experience, SalesChannelContext $context): void
    {
        if ($this->experienceResolver->isSalesRepresentativeAudience($context)) {
            return;
        }

        try {
            $this->activityService->addActivity(
                self::ACTIVITY_TYPE,
                (string) ($experience->getTitle() ?? ''),
                self::ACTIVITY_SNIPPET,
                ExperienceDefinition::ENTITY_NAME,
                $experience->getId(),
                $context,
                [
                    'cmsPageId' => $experience->getCmsPageId(),
                    'title' => $experience->getTitle(),
                ]
            );
        } catch (\Throwable) {
        }
    }
}
