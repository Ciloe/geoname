<?php

namespace App\Command;

use App\Indexation\LocalizationBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IndexLocalizationCommand extends Command
{
    protected static $defaultName = 'indexation:index:localization';

    /**
     * @var LocalizationBuilder
     */
    private $builder;

    /**
     * @param LocalizationBuilder $builder
     */
    public function __construct(LocalizationBuilder $builder)
    {
        parent::__construct(self::$defaultName);

        $this->builder = $builder;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Index all localization.')
            ->setHelp('This command allows you to index in elasticsearch server all localizations.')
        ;
    }

    /**
     * @inheritDoc
     * @throws \PommProject\ModelManager\Exception\ModelException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->builder->create();

        $this->builder->indexAllDocuments($io);

        $io->success('All localizations indexed.');
    }
}
