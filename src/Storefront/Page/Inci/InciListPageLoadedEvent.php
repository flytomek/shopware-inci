<?php declare(strict_types=1);

namespace Codematic\Inci\Storefront\Page\Inci;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\PageLoadedEvent;
use Symfony\Component\HttpFoundation\Request;

class InciListPageLoadedEvent extends PageLoadedEvent
{
    protected InciListPage $page;

    public function __construct(InciListPage $page, SalesChannelContext $salesChannelContext, Request $request)
    {
        $this->page = $page;
        parent::__construct($salesChannelContext, $request);
    }

    public function getPage(): InciListPage
    {
        return $this->page;
    }
}