<?php
namespace beaver;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

class MakeBlock extends Command
{

    protected $commandName = 'make:block';
    protected $commandDescription = "Automatic generate file block gutenberg.";

    protected $commandArgumentName = "repositoriey";
    protected $commandArgumentDescription = "Type your name used to block gutenberg.";

    protected $commandOptionName = "clear"; // should be specified like "app:greet John --cap"
    protected $commandOptionDescription = 'If set, it will generate in uppercase letters';

    protected const OPEN_ROW = '<' . '?php' . "\n\n";
    

    protected const OPEN_ROW_BLOCK = '{{--';
    // protected const CLOSE_ROW_BLOCK = '--}}' . "\n";
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

    protected function camelToDashed($className)
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $className));
    }
    
    protected function kebabToCamel($str)
    {
        // Remove underscores, capitalize words, squash, lowercase first.
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $str)));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument($this->commandArgumentName);

        if ($name) {
            $controller = 'Controller with name ' . $name . ' has been created.';
            $this->createControllerBlock($name);
        } else {
            $controller = 'Controller is missing.';
        }

        if ($name) {
            $view = 'View block with name ' . $this->camelToDashed($name) . ' has been created.';
            $this->createViewBlock($name);
        } else {
            $view = 'View block is missing.';
        }

        if ($name) {
            $sass = 'Sass file with name ' . $this->camelToDashed($name) . ' has been created.';
            $this->createScssBlock($this->camelToDashed($name));
        } else {
            $sass = 'Sass file is missing.';
        }

        $output->writeln($controller);
        $output->writeln($view);
        $output->writeln($sass);
    }

    protected function createControllerBlock($nameController)
    {
        if (!file_exists($nameController . '.php')) {
            fopen('app/Blocks/' . $nameController . '.php', 'w');
        }
        $string = $this->renderController($nameController);
        $open = fopen('app/Blocks/' . $nameController . '.php', 'w+');
        fwrite($open, $string);
    }

    protected function createViewBlock($nameController)
    {
        if (!file_exists($this->camelToDashed($nameController) . '.blade.php')) {
            fopen('resources/views/blocks/' . $this->camelToDashed($nameController) . '.blade.php', 'w');
        }
        $string = $this->renderViewBlock($this->camelToDashed($nameController));
        $open = fopen('resources/views/blocks/' . $this->camelToDashed($nameController) . '.blade.php', 'w+');
        fwrite($open, $string);
    }


    protected function createScssBlock($nameController)
    {
        if (!file_exists($nameController . '.blade.php')) {
            fopen('resources/assets/styles/blocks/' . $this->camelToDashed($nameController) . '.scss', 'w');
        }
        $string = $this->renderScss($nameController);
        $open = fopen('resources/assets/styles/blocks/' . $this->camelToDashed($nameController) . '.scss', 'w+');
        fwrite($open, $string);
    }

    protected function renderScss($nameController)
    {
        $controller_render = $this->twig->render('/scss-block.twig', [
            'scss_class' => $nameController
        ]);
        return $controller_render;
    }

    protected function renderController($nameController)
    {
        $controller_render = self::OPEN_ROW . $this->twig->render('/block-controller.twig', [
            'name_controller' => $nameController
        ]);
        return $controller_render;
    }

    protected function renderViewBlock($nameController)
    {
        $block_view_render = self::OPEN_ROW_BLOCK . $this->twig->render('/block-view.twig', [
            'class_name' => $nameController,
            'block_name' => ucwords(str_replace('-', ' ', $nameController)),
            'block_name_camel' => $this->kebabToCamel($nameController),

        ]);
        return $block_view_render;
    }
}
