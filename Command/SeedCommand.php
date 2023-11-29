<?php

namespace Alms\Bundle\DatabaseSeederBundle\Command;

use Alms\Bundle\DatabaseSeederBundle\Database\Cleaner;
use Alms\Bundle\DatabaseSeederBundle\Seeder\ExecutorInterface;
use Countable;
use Cycle\Database\DatabaseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SeedCommand extends Command
{

    public function __construct(
        protected ExecutorInterface $executor,
        protected DatabaseInterface $database,
        protected Cleaner           $cleaner,
        protected iterable          $seeders
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('cycle:seed')
            ->setDescription('Seed the database with records')
            ->addArgument('database', InputArgument::OPTIONAL, 'The database to seed', $this->database->getName())
            ->addArgument('except', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Seed all except given', [])
            ->addOption('append', null, InputOption::VALUE_NONE, 'Append the seeders instead of refreshing the database');
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('append')) {
            if (!$io->confirm(sprintf('Careful, database <comment>"%s"</comment> will be cleared. Do you want to continue?', $this->database->getName()), !$input->isInteractive())) {
                return Command::SUCCESS;
            }
        }

        if ($this->seeders instanceof Countable and count($this->seeders) === 0) {
            $io->error('Could not find any seeders to run.');
            return Command::FAILURE;
        }

        if (!$input->getOption('append')) {
            $io->text(' <comment>></comment> <info>Cleaning database...</info>');

            $this->cleaner->refreshDb(
                $input->getArgument('database'),
                $input->getArgument('except')
            );
        }

        $this->executor->afterSeed(
            fn($seeder) => $io->text(sprintf(' <comment>></comment> <info>Seeding</info> [%s] <info>completed successfully.</info>', $seeder::class))
        );

        $this->executor->execute($this->seeders);

        $io->newLine();
        $io->success('Database seeding completed successfully.');

        return Command::SUCCESS;
    }
}