<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

#[AsCommand(
    name: 'app:init',
    description: 'Add a short description for your command',
)]
class AppInitCommand extends Command
{
    public function __construct(
        // private PdoSessionHandler $sessionHandlerService
    ) {
        parent::__construct('app:init');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // try {
        //     $this->sessionHandlerService->createTable();
        // } catch (\PDOException $exception) {
        //     // the table could not be created for some reason
        // }

        return Command::SUCCESS;
    }
}
