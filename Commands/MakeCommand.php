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
            $this->controllerFlow($name);
        } else {
            $text = 'Controller is missing.';
        }

        if ($input->getOption($this->commandOptionName)) {
            $text = 'Controller with name ' . $name . ' has been created with clear code.';
            $this->controllerCleanFlow($name);
        }

        $output->writeln($text);
    }

    protected function controllerFlow($nameController)
    {
        if (!file_exists($nameController . '.php')) {
            fopen('app/Controllers/' . $nameController . '.php', 'w');
        }
        $string = $this->readFlow($nameController);
        $open = fopen('app/Controllers/' . $nameController . '.php', 'w+');
        fwrite($open, $string);
    }

    protected function controllerCleanFlow($nameController)
    {
        if (!file_exists($nameController . '.php')) {
            fopen('app/Controllers' . $nameController . '.php', 'w');
        }
        $string = $this->readCleanFlow($nameController);
        $open = fopen('app/Controllers' . $nameController . '.php', 'w+');
        fwrite($open, $string);
    }

    protected function prepareControllerFlow($path)
    {
        $auto = ini_get('auto_detect_line_endings');
        $lines = file($path);
        ini_set('auto_detect_line_endings', $auto);

        return $lines;
    }

    protected function readFlow($nameController)
    {
        // $lines = $this->prepareControllerFlow($this->pathControllerFirst);

        // $data = '';
        // $extends = '';
        // $extends .= $nameController;
        // $extends .= " extends Controller";
        // foreach ($lines as $lineder) {
        //     $open = implode('|', array_map('trim', explode('|', $lineder)));
        //     $findout = strstr($open, 'defaultController');
        //     $changed = str_replace($findout, $extends, $lineder);
        //     $data .= $changed;
        // }
        // return $data;
        $controller_render = self::OPEN_ROW . $this->twig->render('/sample.twig', [
            // this array defines the variables passed to the template,
            // where the key is the variable name and the value is the variable value
            // (Twig recommends using snake_case variable names: 'foo_bar' instead of 'fooBar')
            'name_controller' => $nameController
        ]);

        return $controller_render;
    }

    protected function readCleanFlow($nameController)
    {
        $lines = $this->prepareControllerFlow($this->pathControllerSecond);

        $data = '';
        foreach ($lines as $lineder) {
            $open = implode('|', array_map('trim', explode('|', $lineder)));
            $findout = strstr($open, 'defaultController');
            $changed = str_replace($findout, $nameController, $lineder);
            $data .= $changed;
        }
        return $data;

    }
}
