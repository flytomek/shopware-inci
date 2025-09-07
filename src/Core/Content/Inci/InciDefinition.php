<?php declare(strict_types=1);

namespace Codematic\Inci\Core\Content\Inci;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;

class InciDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'codematic_inci';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return InciEntity::class;
    }

    public function getCollectionClass(): string
    {
        return InciCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('name', 'name'))->addFlags(new Required()),
            (new StringField('slug', 'slug'))->addFlags(new Required()),
            (new LongTextField('alternative_names', 'alternativeNames')),
            (new StringField('cas_number', 'casNumber')),
            (new StringField('polish_name', 'polishName')),
            (new LongTextField('description', 'description')),
            (new LongTextField('main_functions', 'mainFunctions')),
            (new LongTextField('safety_information', 'safetyInformation')),
            (new IntField('rating', 'rating')),
            (new LongTextField('resources', 'resources')),
            (new BoolField('natural', 'natural')),
            (new BoolField('active', 'active')),
        ]);
    }
}