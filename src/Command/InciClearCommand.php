<?php declare(strict_types=1);

namespace Codematic\Inci\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'codematic:inci:clear',
    description: 'Remove all INCI ingredients from database'
)]
class InciClearCommand extends Command
{
    public function __construct(
        private readonly EntityRepository $inciRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Skip confirmation prompt');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $context = Context::createDefaultContext();
        $force = $input->getOption('force');

        // Get count of all ingredients
        $criteria = new Criteria();
        $result = $this->inciRepository->search($criteria, $context);
        $totalCount = $result->getTotal();

        if ($totalCount === 0) {
            $io->success('No ingredients found in database. Nothing to remove.');
            return Command::SUCCESS;
        }

        // Show what will be removed
        $io->warning("This will permanently delete ALL {$totalCount} ingredients from the database!");
        
        // List all ingredients that will be removed
        if ($totalCount <= 20) {
            $io->writeln('Ingredients to be removed:');
            foreach ($result->getEntities() as $ingredient) {
                $io->writeln("• {$ingredient->getName()} ({$ingredient->getSlug()})");
            }
        } else {
            $io->writeln("Too many ingredients to list ({$totalCount} total)");
        }

        // Confirmation
        if (!$force) {
            $io->newLine();
            if (!$io->confirm('Are you absolutely sure you want to remove ALL ingredients?', false)) {
                $io->writeln('Operation cancelled');
                return Command::SUCCESS;
            }

            // Double confirmation for safety
            if (!$io->confirm('This action cannot be undone. Continue?', false)) {
                $io->writeln('Operation cancelled');
                return Command::SUCCESS;
            }
        }

        // Remove all ingredients
        $io->writeln('Removing all ingredients...');
        
        $ingredientIds = [];
        foreach ($result->getEntities() as $ingredient) {
            $ingredientIds[] = ['id' => $ingredient->getId()];
        }

        if (!empty($ingredientIds)) {
            $this->inciRepository->delete($ingredientIds, $context);
        }

        $io->success("Successfully removed {$totalCount} ingredients from the database!");

        return Command::SUCCESS;
    }
}