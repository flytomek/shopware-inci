<?php declare(strict_types=1);

namespace Codematic\Inci\Storefront\Page\Inci;

use Codematic\Inci\Core\Content\Inci\InciCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class InciListPageLoader
{
    public function __construct(
        private readonly GenericPageLoaderInterface $genericPageLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityRepository $inciRepository,
        private readonly SystemConfigService $systemConfigService
    ) {
    }

    public function load(Request $request, SalesChannelContext $context): InciListPage
    {
        $page = $this->genericPageLoader->load($request, $context);
        $page = InciListPage::createFrom($page);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addSorting(new FieldSorting('name', FieldSorting::ASCENDING));

        $result = $this->inciRepository->search($criteria, $context->getContext());

        /** @var InciCollection $ingredients */
        $ingredients = $result->getEntities();
        
        $page->setIngredients($ingredients);
        $page->setTotal($result->getTotal());

        // Set configurable content
        $page->setPageTitle(
            $this->systemConfigService->get('CodematicInci.config.listPageTitle', $context->getSalesChannelId()) 
            ?: 'Baza Składników INCI'
        );
        
        $page->setPageDescription(
            $this->systemConfigService->get('CodematicInci.config.listPageDescription', $context->getSalesChannelId()) 
            ?: 'Odkryj wszystko o składnikach kosmetycznych. Kompleksowa baza wiedzy o bezpieczeństwie i właściwościach składników INCI.'
        );
        
        $page->setEmptyListMessage(
            $this->systemConfigService->get('CodematicInci.config.emptyListMessage', $context->getSalesChannelId()) 
            ?: 'W bazie nie ma jeszcze żadnych składników INCI.'
        );

        // Set meta information for listing page
        $metaTitle = $this->systemConfigService->get('CodematicInci.config.listPageMetaTitle', $context->getSalesChannelId()) 
            ?: 'Baza Składników INCI - kompletny przewodnik po kosmetykach';
        $page->getMetaInformation()->setMetaTitle($metaTitle);

        $metaDescription = $this->systemConfigService->get('CodematicInci.config.listPageMetaDescription', $context->getSalesChannelId()) 
            ?: 'Odkryj wszystko o składnikach kosmetycznych. Kompleksowa baza wiedzy o bezpieczeństwie i właściwościach składników INCI. Sprawdź oceny bezpieczeństwa i zastosowanie.';
        $page->getMetaInformation()->setMetaDescription($metaDescription);

        $this->eventDispatcher->dispatch(
            new InciListPageLoadedEvent($page, $context, $request)
        );

        return $page;
    }
}