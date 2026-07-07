<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\StoreApi\Response;

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

/**
 * @extends StoreApiResponse<EntitySearchResult>
 */
class ExperienceListResponse extends StoreApiResponse
{
    public function __construct(EntitySearchResult $result)
    {
        parent::__construct($result);
    }

    public function getExperiences(): EntitySearchResult
    {
        return $this->object;
    }
}
