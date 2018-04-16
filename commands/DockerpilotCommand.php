<?php

namespace Dockerpilot\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DockerpilotCommand extends Command
{
    /**
     * Get Docker secret ID by name.
     *
     * @param $name
     * @return string
     * @throws Exception
     */
    protected function getDockerSecretID($name)
    {
        $process = new Process("docker secret ls -f name=$name -q");

        try {
            $process->mustRun();
            return $process->getOutput();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get Docker service ID by name.
     *
     * @param $name
     * @return string
     * @throws Exception
     */
    protected function getDockerServiceID($name)
    {
        $process = new Process("docker service ls --filter name=$name -q");

        try {
            $process->mustRun();
            return $process->getOutput();
        } catch (ProcessFailedException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
