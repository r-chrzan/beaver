<?php
namespace beaver;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

class MakeCommand extends Command
{

    protected $commandName = 'make:controller';
    protected $commandDescription = "Automatic generate class controller.";

    protected $commandArgumentName = "repositoriey";
    protected $commandArgumentDescription = "Type your name used to controller class.";

    protected $commandOptionName = "clear"; // should be specified like "app:greet John --cap"
    protected $commandOptionDescription = 'If set, it will generate in uppercase letters';

    protected const OPEN_ROW = '<' . '?php' . "\n\n";
    
    // Create a private variable to store the twig environment
    private $twig;

    public function __construct(Environment $twig)
    {
        // Inject it in the constructor and update the value on the class
        $this->twig = $twig;
        parent::__construct();
    }

    protected function configure()
    {

        $this
            ->setName($this->commandName)
            ->setDescription($this->commandDescription)
            ->addArgument(
                $this->commandArgumentName,
                InputArgument::OPTIONAL,
                $this->commandArgumentDescription
            )
            ->addOption(
                $this->commandOptionName,
                null,
                InputOption::VALUE_NONE,
                $this->commandOptionDescription
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument($this->commandArgumentName);

        if ($name) {
            $text = 'Controller with name ' . $name . ' has been created.';
            $this->createController($name);
        } else {
            $text = 'Controller is missing.';
        }

        $output->writeln($text);
    }

    protected function createController($nameController)
    {
        if (!file_exists($nameController . '.php')) {
            fopen('app/Controllers/' . $nameController . '.php', 'w');
        }
        $string = $this->renderController($nameController);
        $open = fopen('app/Controllers/' . $nameController . '.php', 'w+');
        fwrite($open, $string);
    }

    protected function renderController($nameController)
    {
        $controller_render = self::OPEN_ROW . $this->twig->render('/controller.twig', [
            'name_controller' => $nameController
        ]);
        return $controller_render;
    }
}
