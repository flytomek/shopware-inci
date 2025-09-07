<?php declare(strict_types=1);

namespace Codematic\Inci\Command;

use Codematic\Inci\Core\Content\Inci\InciEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'codematic:inci:remove',
    description: 'Remove INCI ingredient'
)]
class InciRemoveCommand extends Command
{
    public function __construct(
        private readonly EntityRepository $inciRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Ingredient name')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Skip confirmation');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $context = Context::createDefaultContext();

        $name = trim($input->getArgument('name'));
        $force = $input->getOption('force');

        if (empty($name)) {
            $io->error('Ingredient name cannot be empty');
            return Command::FAILURE;
        }

        // Find ingredient
        $ingredient = $this->findIngredient($name, $context);
        if (!$ingredient) {
            $io->error("Ingredient '{$name}' not found");
            return Command::FAILURE;
        }

        // Show ingredient details
        $this->showIngredientDetails($io, $ingredient);

        // Confirmation
        if (!$force) {
            if (!$io->confirm("Are you sure you want to remove this ingredient?", false)) {
                $io->writeln('Operation cancelled');
                return Command::SUCCESS;
            }
        }

        // Remove ingredient
        $this->inciRepository->delete([
            ['id' => $ingredient->getId()]
        ], $context);

        $io->success("Ingredient '{$ingredient->getName()}' has been removed successfully");

        return Command::SUCCESS;
    }

    private function findIngredient(string $name, Context $context): ?InciEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));
        
        $result = $this->inciRepository->search($criteria, $context);
        
        return $result->first();
    }

    private function showIngredientDetails(SymfonyStyle $io, InciEntity $ingredient): void
    {
        $details = [
            ['ID', $ingredient->getId()],
            ['Name', $ingredient->getName()],
            ['Slug', $ingredient->getSlug()],
            ['Polish Name', $ingredient->getPolishName() ?: 'Not set'],
            ['CAS Number', $ingredient->getCasNumber() ?: 'Not set'],
            ['Rating', $ingredient->getRating() ? $ingredient->getRating() . '/3' : 'Not set'],
            ['Natural', $ingredient->isNatural() !== null ? ($ingredient->isNatural() ? 'Yes' : 'No') : 'Not set'],
            ['Active', $ingredient->isActive() !== null ? ($ingredient->isActive() ? 'Yes' : 'No') : 'Not set'],
        ];

        $io->table(['Field', 'Value'], $details);
    }
}