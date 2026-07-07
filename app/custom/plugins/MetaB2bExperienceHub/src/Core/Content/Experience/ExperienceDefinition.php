<?php declare(strict_types=1);

namespace Meta\B2bExperienceHub\Core\Content\Experience;

use B2bSellersCore\Components\B2bPlatform\PlatformMenu\PlatformMenuItemDefinition;
use Meta\B2bExperienceHub\Core\Content\Experience\Aggregate\ExperienceTranslation\ExperienceTranslationDefinition;
use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class ExperienceDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'meta_b2b_experience';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ExperienceEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ExperienceCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey(), new ApiAware()),
            (new FkField('cms_page_id', 'cmsPageId', CmsPageDefinition::class))->addFlags(new Required(), new ApiAware()),
            (new TranslatedField('title'))->addFlags(new ApiAware(), new Required()),
            (new FkField('rule_id', 'ruleId', RuleDefinition::class))->addFlags(new ApiAware()),
            (new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class))->addFlags(new ApiAware()),
            (new IntField('priority', 'priority'))->addFlags(new ApiAware(), new Required()),
            (new IntField('position', 'position'))->addFlags(new ApiAware(), new Required()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware(), new Required()),
            (new BoolField('visible_for_customer', 'visibleForCustomer'))->addFlags(new ApiAware(), new Required()),
            (new BoolField('visible_for_sales_representative', 'visibleForSalesRepresentative'))->addFlags(new ApiAware(), new Required()),
            (new BoolField('show_in_platform_menu', 'showInPlatformMenu'))->addFlags(new ApiAware(), new Required()),
            (new BoolField('include_in_content_hub', 'includeInContentHub'))->addFlags(new ApiAware(), new Required()),
            (new FkField('platform_menu_parent_id', 'platformMenuParentId', PlatformMenuItemDefinition::class))->addFlags(new ApiAware()),
            (new StringField('menu_icon_name', 'menuIconName'))->addFlags(new ApiAware()),
            (new CreatedAtField())->addFlags(new ApiAware()),
            (new UpdatedAtField())->addFlags(new ApiAware()),
            (new TranslationsAssociationField(
                ExperienceTranslationDefinition::class,
                'meta_b2b_experience_id'
            ))->addFlags(new Required(), new ApiAware()),
            new ManyToOneAssociationField('cmsPage', 'cms_page_id', CmsPageDefinition::class, 'id', false),
            new ManyToOneAssociationField('rule', 'rule_id', RuleDefinition::class, 'id', false),
            new ManyToOneAssociationField('salesChannel', 'sales_channel_id', SalesChannelDefinition::class, 'id', false),
            new ManyToOneAssociationField('platformMenuParent', 'platform_menu_parent_id', PlatformMenuItemDefinition::class, 'id', false),
        ]);
    }
}
