<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

// use App\Entity\hash;

class HashCommand extends Command
{
    public function __construct(EntityManagerInterface $entityManager = null, LoggerInterface $logger = null)
    {
        // Set log output path in config/packages/{environment}/monolog.yaml
        $this->logger = $logger;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:hash')
            ->setDescription('Test console tool. Gnerates a hash for a file.')
            ->addOption(
                'settings',
                null,
                InputOption::VALUE_REQUIRED,
                'Absolute or relative path to YAML configuration settings file.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $settings_path = $input->getOption('settings');
        $this->settings = Yaml::parseFile($settings_path);
        $this->target_file_path= $this->settings['target_file_path'];

        $external_digest_program_command = $this->settings['digest_command'] . ' ' . $this->target_file_path;
        try {
            $external_digest_program_command = escapeshellcmd($external_digest_program_command);
            $external_digest_command_output = exec(
                $external_digest_program_command,
                $external_digest_program_command_output,
                $return
            );
        } catch (Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
        }

        // Success.
        if ($return == 0) {
            list($digest, $path) = preg_split('/\s/', $external_digest_program_command_output[0]);
            $output->writeln($this->settings['digest_command'] . ' hash for ' . $this->target_file_path . ' is ' . $digest);
        } else {
            $output->writeln('Error: ' . $e->getMessage());
        }
    }

}
