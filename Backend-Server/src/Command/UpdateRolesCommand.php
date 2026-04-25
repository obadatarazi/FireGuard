<?php

namespace App\Command;

use App\Service\RolesGroupService;
use App\Service\RolesService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-admin-roles',
    description: 'Add a short description for your command',
)]
class UpdateRolesCommand extends Command
{
    private RolesService $rolesService;
    private RolesGroupService $rolesGroupService;
    public function __construct(RolesService $rolesService,
        RolesGroupService $rolesGroupService,
        string $name = null) {
        parent::__construct($name);
        $this->rolesService = $rolesService;
        $this->rolesGroupService = $rolesGroupService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $roles = $this->rolesService->getAll();

        $roleGroup = $this->rolesGroupService->getSuperAdminRoleGroup();

        $roleGroup->setRoles($roles);

        $this->rolesGroupService->updateStandard($roleGroup);

        $io->success('Roles Updated Successfully');

        return Command::SUCCESS;
    }
}
