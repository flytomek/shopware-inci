<?php declare(strict_types=1);

namespace Codematic\Inci\Storefront\Page\Inci;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\PageLoadedEvent;
use Symfony\Component\HttpFoundation\Request;

class InciDetailPageLoadedEvent extends PageLoadedEvent
{
    protected InciDetailPage $page;

    public function __construct(InciDetailPage $page, SalesChannelContext $salesChannelContext, Request $request)
    {
        $this->page = $page;
        parent::__construct($salesChannelContext, $request);
    }

    public function getPage(): InciDetailPage
    {
        return $this->page;
    }
}