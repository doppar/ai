<?php

namespace Doppar\AI;

use Phaseolies\Providers\ServiceProvider;
use Codewithkyrian\Transformers\Transformers;
use Doppar\AI\Console\Commands\RunAICommand;

class AIServiceProvider extends ServiceProvider
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
    public function boot()
    {
        Transformers::setup()
            ->setCacheDir(storage_path('app/transformers'))
            ->apply();

        $this->commands(RunAICommand::class);
    }
}
