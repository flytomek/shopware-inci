<?php declare(strict_types=1);

namespace Codematic\Inci\Storefront\Page\Inci;

use Codematic\Inci\Core\Content\Inci\InciEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class InciDetailPageLoader
{
    public function __construct(
        private readonly GenericPageLoaderInterface $genericPageLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityRepository $inciRepository,
        private readonly SystemConfigService $systemConfigService
    ) {
    }

    public function load(string $slug, Request $request, SalesChannelContext $context): InciDetailPage
    {
        $page = $this->genericPageLoader->load($request, $context);
        $page = InciDetailPage::createFrom($page);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('slug', $slug));
        $criteria->addFilter(new EqualsFilter('active', true));

        $result = $this->inciRepository->search($criteria, $context->getContext());

        /** @var InciEntity|null $inci */
        $inci = $result->first();
        
        $page->setInci($inci);

        // Set meta information
        if ($inci) {
            // Get meta title template from config
            $metaTitleTemplate = $this->systemConfigService->get(
                'CodematicInci.config.metaTitleTemplate',
                $context->getSalesChannelId()
            ) ?: '{name}{polishNameBrackets} - opis i działanie';
            
            $polishNameBrackets = $inci->getPolishName() ? ' (' . $inci->getPolishName() . ')' : '';
            $metaTitle = str_replace(
                ['{name}', '{polishName}', '{polishNameBrackets}'],
                [$inci->getName(), $inci->getPolishName() ?: '', $polishNameBrackets],
                $metaTitleTemplate
            );
            
            $page->getMetaInformation()->setMetaTitle($metaTitle);
            
            if ($inci->getDescription()) {
                $metaDescription = strip_tags($inci->getDescription());
                if (strlen($metaDescription) > 160) {
                    $metaDescription = substr($metaDescription, 0, 157) . '...';
                }
                $page->getMetaInformation()->setMetaDescription($metaDescription);
            } else {
                // Get default meta description template from config
                $defaultMetaDesc = $this->systemConfigService->get(
                    'CodematicInci.config.metaDescriptionDefault',
                    $context->getSalesChannelId()
                ) ?: 'Szczegółowe informacje o składniku kosmetycznym {name} - bezpieczeństwo, właściwości i zastosowanie.';
                
                $metaDescription = str_replace('{name}', $inci->getName(), $defaultMetaDesc);
                $page->getMetaInformation()->setMetaDescription($metaDescription);
            }
        }

        $this->eventDispatcher->dispatch(
            new InciDetailPageLoadedEvent($page, $context, $request)
        );

        return $page;
    }
}