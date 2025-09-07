<?php declare(strict_types=1);

namespace Codematic\Inci\Storefront\Page\Inci;

use Codematic\Inci\Core\Content\Inci\InciCollection;
use Shopware\Storefront\Page\Page;

class InciListPage extends Page
{
    protected InciCollection $ingredients;
    protected int $total;
    protected string $pageTitle;
    protected string $pageDescription;
    protected string $emptyListMessage;

    public function getIngredients(): InciCollection
    {
        return $this->ingredients;
    }

    public function setIngredients(InciCollection $ingredients): void
    {
        $this->ingredients = $ingredients;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function getPageTitle(): string
    {
        return $this->pageTitle;
    }

    public function setPageTitle(string $pageTitle): void
    {
        $this->pageTitle = $pageTitle;
    }

    public function getPageDescription(): string
    {
        return $this->pageDescription;
    }

    public function setPageDescription(string $pageDescription): void
    {
        $this->pageDescription = $pageDescription;
    }

    public function getEmptyListMessage(): string
    {
        return $this->emptyListMessage;
    }

    public function setEmptyListMessage(string $emptyListMessage): void
    {
        $this->emptyListMessage = $emptyListMessage;
    }
}