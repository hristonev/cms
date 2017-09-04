<?php

namespace AppBundle\Command;


use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateUserCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('app:create-user')
            ->setDescription('Creates a new user.')
            ->setHelp('This command allows you to create a user. Use --super-admin flag for new root user.')
            ->addOption(
                'role',
                null,
                InputOption::VALUE_OPTIONAL,
                'Which role will be granted.',
                null
            );
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $output->writeln([
            'User Creator',
            '============',
            '',
        ]);

        $roles = explode(',', $input->getOption('role'));

        $question = new Question('Please enter email address: ');
        $email = $helper->ask($input, $output, $question);

        $question = new Question('Please enter password: ');
        $plainPassword = $helper->ask($input, $output, $question);

        $em = $this->getContainer()->get('doctrine')->getManager();
        $user = new User();
        $user->setEmail($email);
        $user->setPlainPassword($plainPassword);
        $user->setRoles($roles);
        $em->persist($user);
        $em->flush();

        $output->writeln("User $email successfully created.");
    }
}