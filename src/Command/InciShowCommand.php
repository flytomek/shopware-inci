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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'codematic:inci:show',
    description: 'Show detailed information about an INCI ingredient'
)]
class InciShowCommand extends Command
{
    public function __construct(
        private readonly EntityRepository $inciRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Ingredient name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $context = Context::createDefaultContext();

        $name = trim($input->getArgument('name'));

        if (empty($name)) {
            $io->error('Ingredient name cannot be empty');
            return Command::FAILURE;
        }

        // Find ingredient
        $ingredient = $this->findIngredient($name, $context);
        if (!$ingredient) {
            $io->error("Ingredient '{$name}' not found.");
            return Command::FAILURE;
        }

        // Display ingredient details
        $io->title("📋 Ingredient Details: {$ingredient->getName()}");

        // Basic Information
        $basicInfo = [];
        $basicInfo[] = ['Name', $ingredient->getName()];
        
        if ($ingredient->getPolishName()) {
            $basicInfo[] = ['Polish Name', $ingredient->getPolishName()];
        }
        
        if ($ingredient->getSlug()) {
            $basicInfo[] = ['Slug', $ingredient->getSlug()];
        }
        
        if ($ingredient->getAlternativeNames()) {
            $basicInfo[] = ['Alternative Names', $ingredient->getAlternativeNames()];
        }
        
        if ($ingredient->getCasNumber()) {
            $basicInfo[] = ['CAS Number', $ingredient->getCasNumber()];
        }
        
        if ($ingredient->isNatural() !== null) {
            $basicInfo[] = ['Origin', $ingredient->isNatural() ? 'Natural' : 'Synthetic'];
        }
        
        if ($ingredient->getMainFunctions()) {
            $basicInfo[] = ['Main Functions', $ingredient->getMainFunctions()];
        }
        
        if ($ingredient->getRating() !== null) {
            $ratingText = match($ingredient->getRating()) {
                1 => 'Good (1)',
                2 => 'Average (2)',
                3 => 'Bad (3)',
                default => 'Unknown'
            };
            $basicInfo[] = ['Safety Rating', $ratingText];
        }
        
        $basicInfo[] = ['Active', $ingredient->isActive() ? 'Yes' : 'No'];

        $io->table(['Field', 'Value'], $basicInfo);

        // Description
        if ($ingredient->getDescription()) {
            $io->section('📝 Description');
            $io->writeln($this->formatText($ingredient->getDescription()));
            
            $io->section('🔍 Description (Raw HTML)');
            $io->writeln('<comment>Raw content stored in database:</comment>');
            $io->writeln($ingredient->getDescription());
        }

        // Safety Information
        if ($ingredient->getSafetyInformation()) {
            $io->section('⚠️ Safety Information');
            $io->writeln($this->formatText($ingredient->getSafetyInformation()));
            
            $io->section('🔍 Safety Information (Raw HTML)');
            $io->writeln('<comment>Raw content stored in database:</comment>');
            $io->writeln($ingredient->getSafetyInformation());
        }

        // Resources
        if ($ingredient->getResources()) {
            $io->section('🔗 Additional Resources');
            $resources = explode(',', $ingredient->getResources());
            foreach ($resources as $resource) {
                $resource = trim($resource);
                if (!empty($resource)) {
                    $io->writeln("• {$resource}");
                }
            }
        }

        // URL
        $io->section('🌐 URLs');
        $io->writeln("Detail page: /inci/{$ingredient->getSlug()}");

        $io->success('Ingredient information displayed successfully!');

        return Command::SUCCESS;
    }

    private function findIngredient(string $name, Context $context): ?InciEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));
        
        $result = $this->inciRepository->search($criteria, $context);
        
        return $result->first();
    }

    private function formatText(string $text): string
    {
        // Strip HTML tags for CLI display and wrap long lines
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Simple word wrapping for better CLI readability
        return wordwrap($text, 80, "\n", true);
    }
}