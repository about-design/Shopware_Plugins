<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Core\Content\Experience\Aggregate\ExperienceTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                              add(ExperienceTranslationEntity $entity)
 * @method void                              set(string $key, ExperienceTranslationEntity $entity)
 * @method ExperienceTranslationEntity[]     getIterator()
 * @method ExperienceTranslationEntity|null  get(string $key)
 * @method ExperienceTranslationEntity|null  first()
 * @method ExperienceTranslationEntity|null  last()
 */
class ExperienceTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ExperienceTranslationEntity::class;
    }
}
