<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

class ConsoleService
{
    /**
     * Зарегистрированные команды.
     *
     * @var array<string, callable>
     */
    private array $commands = [];

    /**
     * Регистрирует консольную команду.
     */
    public function command(
        string $name,
        callable $handler
    ): self {
        $this->commands[$name] = $handler;

        return $this;
    }

    /**
     * Запускает обработку аргументов.
     */
    public function run(array $argv): void
    {
        array_shift($argv);

        if (empty($argv)) {
            $this->help();
            return;
        }

        $command = implode(' ', $argv);

        if (!isset($this->commands[$command])) {
            throw new InvalidArgumentException(
                "Неизвестная команда: {$command}"
            );
        }

        ($this->commands[$command])();
    }

    /**
     * Выводит сообщение в консоль.
     */
    public function line(string $message): void
    {
        echo $message . PHP_EOL;
    }

    /**
     * Выводит список доступных команд.
     */
    public function help(): void
    {
        $this->line('Доступные команды:');

        foreach (array_keys($this->commands) as $command) {
            $this->line("  {$command}");
        }
    }
}
