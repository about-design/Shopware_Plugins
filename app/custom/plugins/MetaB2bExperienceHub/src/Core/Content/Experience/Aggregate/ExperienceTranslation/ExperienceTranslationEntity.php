<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Core\Content\Experience\Aggregate\ExperienceTranslation;

use Meta\B2bExperienceHub\Core\Content\Experience\ExperienceEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class ExperienceTranslationEntity extends TranslationEntity
{
    protected string $title = '';

    protected ?ExperienceEntity $metaB2bExperience = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getMetaB2bExperience(): ?ExperienceEntity
    {
        return $this->metaB2bExperience;
    }

    public function setMetaB2bExperience(?ExperienceEntity $metaB2bExperience): void
    {
        $this->metaB2bExperience = $metaB2bExperience;
    }
}
