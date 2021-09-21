<?php
namespace beaver;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeBlocks extends Command
{
    protected $commandName = 'make:block';
    protected $commandDescription = "Automatic generate class controller.";

    protected $commandArgumentName = "repositoriey";
    protected $commandArgumentDescription = "Type your name used to controller class.";

    protected $commandOptionName = "clear"; // should be specified like "app:greet John --cap"
    protected $commandOptionDescription = 'If set, it will generate in uppercase letters';
    protected $pathControllerFirst = 'example/blocks.php';
    // protected $pathControllerSecond = 'example/clearSample.php';

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
            fopen('app/Blocks/' . $nameController . '.php', 'w');
        }
        $string = $this->readFlow($nameController);
        $open = fopen('app/Blocks/' . $nameController . '.php', 'w+');
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
        $lines = $this->prepareControllerFlow($this->pathControllerFirst);

        $data = '';

        foreach ($lines as $lineder) {
            $open = implode('|', array_map('trim', explode('|', $lineder)));
            $findout = strstr($open, 'DefaultBlock');
            $changed = str_replace($findout, $nameController, $lineder);
            $data .= $changed;
        }
        return $data;
    }

    protected function readCleanFlow($nameController)
    {
        $lines = $this->prepareControllerFlow($this->pathControllerSecond);

        $data = '';
        foreach ($lines as $lineder) {
            $open = implode('|', array_map('trim', explode('|', $lineder)));
            $findout = strstr($open, 'DefaultBlock');
            $changed = str_replace($findout, $nameController, $lineder);
            $data .= $changed;
        }
        return $data;

    }
}
