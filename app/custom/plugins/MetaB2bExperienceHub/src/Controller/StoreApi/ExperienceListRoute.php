<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Controller\StoreApi;

use Meta\B2bExperienceHub\Service\ExperienceResolver;
use Meta\B2bExperienceHub\StoreApi\Response\ExperienceListResponse;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Adapter\Cache\CacheCompressor;
use Shopware\Core\Framework\Util\Json;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
class ExperienceListRoute
{
    public function __construct(
        private readonly ExperienceResolver $experienceResolver,
        private readonly TagAwareAdapterInterface $cache,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route(
        path: '/store-api/meta-b2b-experiences',
        name: 'store-api.meta-b2b-experiences.list',
        defaults: [
            '_loginRequired' => true,
            '_b2bPlatformContextRequired' => true,
            '_entity' => 'meta_b2b_experience',
        ],
        methods: ['GET']
    )]
    public function load(SalesChannelContext $context): ExperienceListResponse
    {
        $cacheKey = $this->generateCacheKey($context);
        $item = $this->cache->getItem($cacheKey);

        try {
            if ($item->isHit() && $item->get()) {
                /** @var ExperienceListResponse $response */
                $response = CacheCompressor::uncompress($item);

                return $response;
            }
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }

        $result = $this->experienceResolver->resolveVisibleExperiences($context);
        $response = new ExperienceListResponse($result);

        $item = CacheCompressor::compress($item, $response);
        $item->tag([self::buildCacheTag()]);

        $this->cache->save($item);

        return $response;
    }

    public static function buildCacheTag(): string
    {
        return 'meta-b2b-experience-list-route';
    }

    private function generateCacheKey(SalesChannelContext $context): string
    {
        $customerId = $context->getCustomer()?->getId() ?? 'guest';
        $audience = $this->experienceResolver->isSalesRepresentativeAudience($context) ? 'sales-rep' : 'customer';

        return md5(Json::encode([
            self::buildCacheTag(),
            $context->getSalesChannelId(),
            $context->getLanguageIdChain(),
            $customerId,
            $context->getRuleIds(),
            $audience,
        ]));
    }
}
