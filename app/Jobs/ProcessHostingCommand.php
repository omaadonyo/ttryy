<?php

namespace App\Jobs;

use App\Models\HostingCommand;
use App\Services\Hosting\ServiceCommander;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessHostingCommand implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $backoff = [30, 120, 600];

    public function __construct(public int $commandId) {}

    public function handle(): void
    {
        $command = HostingCommand::find($this->commandId);

        if (! $command || $command->isTerminal()) {
            return;
        }

        $command = ServiceCommander::deliver($command);

        if ($command->state === HostingCommand::STATE_RETRY) {
            $this->release($this->backoff[min($command->attempts - 1, 2)] ?? 600);
        }
    }
}
