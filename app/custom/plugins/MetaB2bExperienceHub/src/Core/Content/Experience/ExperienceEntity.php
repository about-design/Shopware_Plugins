<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Core\Content\Experience;

use Meta\B2bExperienceHub\Core\Content\Experience\Aggregate\ExperienceTranslation\ExperienceTranslationCollection;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Rule\RuleEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class ExperienceEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $title = null;

    protected string $cmsPageId;

    protected ?string $ruleId = null;

    protected ?string $salesChannelId = null;

    protected int $priority = 0;

    protected int $position = 0;

    protected bool $active = true;

    protected bool $visibleForCustomer = true;

    protected bool $visibleForSalesRepresentative = false;

    protected bool $showInPlatformMenu = false;

    protected bool $includeInContentHub = true;

    protected ?string $platformMenuParentId = null;

    protected ?string $menuIconName = null;

    protected ?CmsPageEntity $cmsPage = null;

    protected ?RuleEntity $rule = null;

    protected ?SalesChannelEntity $salesChannel = null;

    protected ?ExperienceTranslationCollection $translations = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getCmsPageId(): string
    {
        return $this->cmsPageId;
    }

    public function setCmsPageId(string $cmsPageId): void
    {
        $this->cmsPageId = $cmsPageId;
    }

    public function getRuleId(): ?string
    {
        return $this->ruleId;
    }

    public function setRuleId(?string $ruleId): void
    {
        $this->ruleId = $ruleId;
    }

    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(?string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function isVisibleForCustomer(): bool
    {
        return $this->visibleForCustomer;
    }

    public function setVisibleForCustomer(bool $visibleForCustomer): void
    {
        $this->visibleForCustomer = $visibleForCustomer;
    }

    public function isVisibleForSalesRepresentative(): bool
    {
        return $this->visibleForSalesRepresentative;
    }

    public function setVisibleForSalesRepresentative(bool $visibleForSalesRepresentative): void
    {
        $this->visibleForSalesRepresentative = $visibleForSalesRepresentative;
    }

    public function isShowInPlatformMenu(): bool
    {
        return $this->showInPlatformMenu;
    }

    public function setShowInPlatformMenu(bool $showInPlatformMenu): void
    {
        $this->showInPlatformMenu = $showInPlatformMenu;
    }

    public function isIncludeInContentHub(): bool
    {
        return $this->includeInContentHub;
    }

    public function setIncludeInContentHub(bool $includeInContentHub): void
    {
        $this->includeInContentHub = $includeInContentHub;
    }

    public function getPlatformMenuParentId(): ?string
    {
        return $this->platformMenuParentId;
    }

    public function setPlatformMenuParentId(?string $platformMenuParentId): void
    {
        $this->platformMenuParentId = $platformMenuParentId;
    }

    public function getMenuIconName(): ?string
    {
        return $this->menuIconName;
    }

    public function setMenuIconName(?string $menuIconName): void
    {
        $this->menuIconName = $menuIconName;
    }

    public function getCmsPage(): ?CmsPageEntity
    {
        return $this->cmsPage;
    }

    public function setCmsPage(?CmsPageEntity $cmsPage): void
    {
        $this->cmsPage = $cmsPage;
    }

    public function getRule(): ?RuleEntity
    {
        return $this->rule;
    }

    public function setRule(?RuleEntity $rule): void
    {
        $this->rule = $rule;
    }

    public function getSalesChannel(): ?SalesChannelEntity
    {
        return $this->salesChannel;
    }

    public function setSalesChannel(?SalesChannelEntity $salesChannel): void
    {
        $this->salesChannel = $salesChannel;
    }

    public function getTranslations(): ?ExperienceTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(?ExperienceTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }
}
