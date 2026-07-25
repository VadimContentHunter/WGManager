<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class CommandService
{
    /**
     * Выполняет системную команду.
     *
     * @param string $command Команда.
     * @param bool $useBash Выполнить через Bash.
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function run(
        string $command,
        bool $useBash = false
    ): string {

        if ($useBash) {
            $command = sprintf(
                'bash -c %s',
                escapeshellarg($command)
            );
        }

        $output = [];
        $exitCode = 0;

        exec(
            $command . ' 2>&1',
            $output,
            $exitCode
        );

        if ($exitCode !== 0) {
            throw new RuntimeException(
                implode(PHP_EOL, $output)
            );
        }

        return trim(
            implode(PHP_EOL, $output)
        );
    }

    /**
     * Проверяет существование команды.
     *
     * @param string $command Имя команды.
     *
     * @return bool
     * true  - Команда существует.
     * false - Команда отсутствует.
     */
    public function exists(string $command): bool
    {
        try {

            $this->run(
                "command -v {$command}",
                true
            );

            return true;
        } catch (RuntimeException) {

            return false;
        }
    }
}
