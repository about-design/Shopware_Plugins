<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\StoreApi\Response;

use Meta\B2bExperienceHub\Core\Content\Experience\ExperienceEntity;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

/**
 * @extends StoreApiResponse<ExperienceEntity>
 */
class ExperienceDetailResponse extends StoreApiResponse
{
    public function __construct(ExperienceEntity $experience)
    {
        parent::__construct($experience);
    }

    public function getExperience(): ExperienceEntity
    {
        return $this->object;
    }
}
