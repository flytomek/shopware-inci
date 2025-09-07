<?php

declare(strict_types=1);

namespace Codematic\Inci;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

final class CodematicInci extends Plugin
{
    public function install(InstallContext $installContext): void
    {
        // Installation handled by migrations
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        // Data removal handled by migration rollback
    }

    public function activate(ActivateContext $activateContext): void
    {
        // Plugin activation logic
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        // Plugin deactivation logic
    }

    public function update(UpdateContext $updateContext): void
    {
        // Update logic
    }
}