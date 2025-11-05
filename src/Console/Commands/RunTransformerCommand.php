<?php

namespace Doppar\Transformer\Console\Commands;

use Doppar\Transformer\Pipeline;
use Doppar\Transformer\Enum\TaskEnum;
use Phaseolies\Console\Schedule\Command;

class RunTransformerCommand extends Command
{
    protected $name = 'transformer:run {task}';

    protected function handle(): int
    {
        return $this->withTiming(function () {

            $task = $this->argument('task');

            $messages = [
                ['role' => 'user', 'content' => $task],
            ];
            $output = Pipeline::execute(
                task: TaskEnum::TEXT_GENERATION,
                model: 'HuggingFaceTB/SmolLM2-360M-Instruct',
                messages: $messages);

            $this->info($output[0]['generated_text']);
            $this->newLine();
            return Command::SUCCESS;
        });
    }
}
