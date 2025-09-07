<?php declare(strict_types=1);

namespace Codematic\Inci\Core\Content\Inci;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<InciEntity>
 */
class InciCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return InciEntity::class;
    }
}