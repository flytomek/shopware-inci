<?php declare(strict_types=1);

namespace Codematic\Inci\Command;

use Codematic\Inci\Core\Content\Inci\InciEntity;
use Codematic\Inci\Service\OpenAIService;
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
    name: 'codematic:inci:generate',
    description: 'Generate content for INCI ingredient using ChatGPT'
)]
class InciGenerateCommand extends Command
{
    public function __construct(
        private readonly EntityRepository $inciRepository,
        private readonly OpenAIService $openAIService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Ingredient name')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing content');
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
            $io->error("Ingredient '{$name}' not found. Add it first using codematic:inci:add command");
            return Command::FAILURE;
        }

        $io->title("Generating content for: {$ingredient->getName()}");

        // Check if content already exists
        if (!$force && $this->hasGeneratedContent($ingredient)) {
            if (!$io->confirm('This ingredient already has generated content. Overwrite?', false)) {
                return Command::SUCCESS;
            }
        }

        // Generate content fields
        $updates = ['id' => $ingredient->getId()];
        $generationSteps = [
            'description' => '📝 Generating description...',
            'mainFunctions' => '🔧 Generating main functions...',
            'safetyInformation' => '⚠️ Generating safety information...',
            'rating' => '⭐ Determining rating...',
        ];

        foreach ($generationSteps as $field => $message) {
            $io->text($message);
            
            $content = match($field) {
                'description' => $this->openAIService->generateDescription($ingredient->getName()),
                'mainFunctions' => $this->openAIService->generateMainFunctions($ingredient->getName()),
                'safetyInformation' => $this->openAIService->generateSafetyInformation($ingredient->getName()),
                'rating' => $this->openAIService->generateRating($ingredient->getName()),
            };

            if ($content !== null) {
                $updates[$field] = $content;
                $io->writeln(" ✅ Generated {$field}");
            } else {
                $io->writeln(" ❌ Failed to generate {$field}");
            }
        }


        // Generate general fields (alternative names, CAS, etc.)
        $io->text('🧪 Generating technical fields...');
        $generalFields = $this->openAIService->generateGeneralFields($ingredient->getName());
        
        if ($generalFields && is_array($generalFields)) {
            $fieldMapping = [
                'alternativeNames' => 'alternative_names',
                'casNumber' => 'cas_number', 
                'polishName' => 'polish_name',
                'resources' => 'resources',
                'natural' => 'natural'
            ];

            foreach ($fieldMapping as $entityField => $jsonKey) {
                if (isset($generalFields[$jsonKey])) {
                    $updates[$entityField] = $generalFields[$jsonKey];
                }
            }
            
            $io->writeln(" ✅ Generated technical fields");
        } else {
            $io->writeln(" ❌ Failed to generate technical fields");
        }

        // Step 4: Save updates
        if (count($updates) > 1) { // More than just ID
            $this->inciRepository->update([$updates], $context);
            $io->success('Content generation completed successfully!');
            
            // Show summary
            $this->showGenerationSummary($io, $updates);
        } else {
            $io->error('No content was generated');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function findIngredient(string $name, Context $context): ?InciEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));
        
        $result = $this->inciRepository->search($criteria, $context);
        
        return $result->first();
    }

    private function hasGeneratedContent(InciEntity $ingredient): bool
    {
        return !empty($ingredient->getDescription()) || 
               !empty($ingredient->getSafetyInformation()) ||
               $ingredient->getRating() !== null;
    }

    private function showGenerationSummary(SymfonyStyle $io, array $updates): void
    {
        $summary = [];
        
        $fieldLabels = [
            'description' => 'Description',
            'mainFunctions' => 'Main Functions', 
            'safetyInformation' => 'Safety Information',
            'rating' => 'Rating',
            'alternativeNames' => 'Alternative Names',
            'casNumber' => 'CAS Number',
            'polishName' => 'Polish Name',
            'resources' => 'Resources',
            'natural' => 'Natural'
        ];

        foreach ($fieldLabels as $field => $label) {
            if (isset($updates[$field])) {
                $value = $updates[$field];
                if (is_bool($value)) {
                    $value = $value ? 'Yes' : 'No';
                } elseif (is_string($value) && strlen($value) > 100) {
                    $value = substr($value, 0, 100) . '...';
                }
                $summary[] = [$label, $value];
            }
        }

        if (!empty($summary)) {
            $io->table(['Field', 'Generated Content'], $summary);
        }
    }
}