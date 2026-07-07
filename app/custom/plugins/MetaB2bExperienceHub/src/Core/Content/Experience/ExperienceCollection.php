<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Core\Content\Experience;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                     add(ExperienceEntity $entity)
 * @method void                     set(string $key, ExperienceEntity $entity)
 * @method ExperienceEntity[]       getIterator()
 * @method ExperienceEntity|null    get(string $key)
 * @method ExperienceEntity|null    first()
 * @method ExperienceEntity|null    last()
 */
class ExperienceCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ExperienceEntity::class;
    }
}
