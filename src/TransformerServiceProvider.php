<?php

namespace Doppar\Transformer;

use Phaseolies\Providers\ServiceProvider;
use Codewithkyrian\Transformers\Transformers;
use Doppar\Transformer\Console\Commands\RunTransformerCommand;

class TransformerServiceProvider extends ServiceProvider
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

        $this->commands(RunTransformerCommand::class);
    }
}
