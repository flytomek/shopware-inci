<?php declare(strict_types=1);

namespace Codematic\Inci\Storefront\Controller;

use Codematic\Inci\Storefront\Page\Inci\InciDetailPageLoader;
use Codematic\Inci\Storefront\Page\Inci\InciListPageLoader;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
class InciController extends StorefrontController
{
    public function __construct(
        private readonly InciListPageLoader $listPageLoader,
        private readonly InciDetailPageLoader $detailPageLoader
    ) {
    }

    #[Route(path: '/inci', name: 'frontend.inci.list', methods: ['GET'])]
    public function listPage(Request $request, SalesChannelContext $context): Response
    {
        $page = $this->listPageLoader->load($request, $context);

        return $this->renderStorefront('@CodematicInci/storefront/page/inci/index.html.twig', [
            'page' => $page
        ]);
    }

    #[Route(path: '/inci/{slug}', name: 'frontend.inci.detail', methods: ['GET'])]
    public function detailPage(string $slug, Request $request, SalesChannelContext $context): Response
    {
        $page = $this->detailPageLoader->load($slug, $request, $context);

        if (!$page->getInci()) {
            throw $this->createNotFoundException();
        }

        return $this->renderStorefront('@CodematicInci/storefront/page/inci/detail.html.twig', [
            'page' => $page
        ]);
    }
}