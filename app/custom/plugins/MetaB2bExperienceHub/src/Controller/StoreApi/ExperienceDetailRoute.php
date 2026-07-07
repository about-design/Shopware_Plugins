<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Controller\StoreApi;

use Meta\B2bExperienceHub\Core\Content\Experience\ExperienceDefinition;
use Meta\B2bExperienceHub\Core\ExperiencePermissions;
use Meta\B2bExperienceHub\Service\ExperienceActivityTracker;
use Meta\B2bExperienceHub\Service\ExperienceResolver;
use Meta\B2bExperienceHub\StoreApi\Response\ExperienceDetailResponse;
use Shopware\Core\Framework\Api\Exception\ResourceNotFoundException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
class ExperienceDetailRoute
{
    public function __construct(
        private readonly ExperienceResolver $experienceResolver,
        private readonly ExperienceActivityTracker $activityTracker,
    ) {
    }

    #[Route(
        path: '/store-api/meta-b2b-experiences/{id}',
        name: 'store-api.meta-b2b-experiences.detail',
        defaults: [
            '_loginRequired' => true,
            '_b2bPlatformContextRequired' => true,
            '_b2bPlatformPermission' => [ExperiencePermissions::VIEW],
            '_entity' => 'meta_b2b_experience',
        ],
        methods: ['GET']
    )]
    public function load(string $id, SalesChannelContext $context): ExperienceDetailResponse
    {
        $collection = $this->experienceResolver->resolveVisibleExperience($id, $context);

        if ($collection === null) {
            throw new ResourceNotFoundException(ExperienceDefinition::ENTITY_NAME, ['id' => $id]);
        }

        $experience = $collection->first();
        $this->activityTracker->trackView($experience, $context);

        return new ExperienceDetailResponse($experience);
    }
}
