<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Core\Content\Experience\Aggregate\ExperienceTranslation;

use Meta\B2bExperienceHub\Core\Content\Experience\ExperienceDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ExperienceTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'meta_b2b_experience_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ExperienceTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ExperienceTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return ExperienceDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('title', 'title', 255))->addFlags(new Required(), new ApiAware()),
        ]);
    }
}
