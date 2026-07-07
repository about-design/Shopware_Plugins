<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Service;

use B2bSellersCore\Components\B2bPlatform\B2bPlatformContext;
use Meta\B2bExperienceHub\Core\Content\Experience\ExperienceCollection;
use Meta\B2bExperienceHub\Core\Content\Experience\ExperienceDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ExperienceResolver
{
    public function __construct(
        private readonly EntityRepository $experienceRepository,
    ) {
    }

    public function resolveVisibleExperiences(
        SalesChannelContext $context,
        ?Criteria $criteria = null,
        bool $contentHubOnly = true,
    ): EntitySearchResult {
        $criteria ??= new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(new OrFilter([
            new EqualsAnyFilter('ruleId', $context->getRuleIds()),
            new EqualsFilter('ruleId', null),
        ]));
        $criteria->addFilter(new OrFilter([
            new EqualsFilter('salesChannelId', $context->getSalesChannelId()),
            new EqualsFilter('salesChannelId', null),
        ]));

        $this->applyAudienceFilter($criteria, $context);

        if ($contentHubOnly) {
            $criteria->addFilter(new EqualsFilter('includeInContentHub', true));
        }

        $criteria->addAssociation('cmsPage');
        $criteria->addAssociation('rule');
        $criteria->addAssociation('translations');
        $criteria->addSorting(new FieldSorting('priority', FieldSorting::DESCENDING));
        $criteria->addSorting(new FieldSorting('position', FieldSorting::ASCENDING));
        $criteria->addSorting(new FieldSorting('title', FieldSorting::ASCENDING));

        return $this->experienceRepository->search($criteria, $context->getContext());
    }

    public function resolveVisibleExperience(string $id, SalesChannelContext $context): ?ExperienceCollection
    {
        $criteria = new Criteria([$id]);
        $result = $this->resolveVisibleExperiences($context, $criteria, false);

        if ($result->count() === 0) {
            return null;
        }

        /** @var ExperienceCollection $collection */
        $collection = $result->getEntities();

        return $collection;
    }

    public function isSalesRepresentativeAudience(SalesChannelContext $context): bool
    {
        $extension = $context->getExtension(B2bPlatformContext::EXTENSION_KEY);

        if (!$extension instanceof B2bPlatformContext || !$extension->isSalesRepresentative()) {
            return false;
        }

        $customer = $context->getCustomer();
        $salesRepresentative = $extension->getSalesRepresentative();

        if ($customer === null || $salesRepresentative === null) {
            return false;
        }

        return $salesRepresentative->getId() !== $customer->getId();
    }

    private function applyAudienceFilter(Criteria $criteria, SalesChannelContext $context): void
    {
        if ($this->isSalesRepresentativeAudience($context)) {
            $criteria->addFilter(new EqualsFilter('visibleForSalesRepresentative', true));

            return;
        }

        $criteria->addFilter(new EqualsFilter('visibleForCustomer', true));
    }
}
