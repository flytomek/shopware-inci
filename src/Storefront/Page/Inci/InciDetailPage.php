<?php declare(strict_types=1);

namespace Codematic\Inci\Storefront\Page\Inci;

use Codematic\Inci\Core\Content\Inci\InciEntity;
use Shopware\Storefront\Page\Page;

class InciDetailPage extends Page
{
    protected ?InciEntity $inci = null;

    public function getInci(): ?InciEntity
    {
        return $this->inci;
    }

    public function setInci(?InciEntity $inci): void
    {
        $this->inci = $inci;
    }
}