<?php declare(strict_types=1);

namespace Codematic\Inci\Command;

use Codematic\Inci\Core\Content\Inci\InciEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'codematic:inci:add',
    description: 'Add new INCI ingredient'
)]
class InciAddCommand extends Command
{
    public function __construct(
        private readonly EntityRepository $inciRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Ingredient name (e.g. "Pentylene Glycol")')
            ->addOption('inactive', null, InputOption::VALUE_NONE, 'Create as inactive');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $context = Context::createDefaultContext();

        $name = trim($input->getArgument('name'));
        $active = !$input->getOption('inactive');

        if (empty($name)) {
            $io->error('Ingredient name cannot be empty');
            return Command::FAILURE;
        }

        // Check if ingredient already exists
        $slug = $this->createSlug($name);
        if ($this->ingredientExists($slug, $context)) {
            $io->error("Ingredient with slug '{$slug}' already exists");
            return Command::FAILURE;
        }

        // Create ingredient entry
        $inciId = Uuid::randomHex();
        $this->inciRepository->create([
            [
                'id' => $inciId,
                'name' => $name,
                'slug' => $slug,
                'active' => $active,
            ]
        ], $context);

        $io->success("INCI ingredient added successfully!");
        $io->table(['Field', 'Value'], [
            ['ID', $inciId],
            ['Name', $name],
            ['Slug', $slug],
            ['Active', $active ? 'Yes' : 'No'],
            ['Status', 'Content not generated - use codematic:inci:generate command']
        ]);

        $io->note("Next step: Run 'bin/console codematic:inci:generate \"{$name}\"' to generate content using ChatGPT");

        return Command::SUCCESS;
    }

    private function createSlug(string $name): string
    {
        // Convert to lowercase and replace spaces/special chars with hyphens
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return $slug;
    }

    private function ingredientExists(string $slug, Context $context): bool
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('slug', $slug));
        
        $result = $this->inciRepository->search($criteria, $context);
        
        return $result->getTotal() > 0;
    }
}