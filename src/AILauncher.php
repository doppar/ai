<?php

namespace Doppar\AI;

use Phaseolies\Launchers\ServiceLauncher;
use Doppar\AI\Console\Commands\RunAICommand;
use Codewithkyrian\Transformers\Transformers;
use Doppar\AI\Console\Commands\TranslateCommand;

class AILauncher extends ServiceLauncher
{
    /**
     * Register services and bindings into the container.
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function launch()
    {
        Transformers::setup()
            ->setCacheDir(storage_path('app/transformers'))
            ->apply();

        $this->commands(RunAICommand::class);
        $this->commands(TranslateCommand::class);
    }
}
