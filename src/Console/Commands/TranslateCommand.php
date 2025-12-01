<?php

namespace Doppar\AI\Console\Commands;

use Doppar\AI\Agent;
use Doppar\AI\AgentFactory\Agent\Gemini;
use Doppar\AI\AgentFactory\Agent\OpenAI;
use Phaseolies\Console\Schedule\Command;

class TranslateCommand extends Command
{
    protected $name = 'ai:translate {agent} {langFrom} {langTo} {model?}';

    protected function handle(): int
    {
        return $this->withTiming(function () {

            $agent = $this->argument('agent');
            $langFrom = $this->argument('langFrom');
            $langTo = $this->argument('langTo');

            switch ($agent) {
                case 'gemini':
                    $agent = Gemini::class;
                    $model = $this->argument('model') ?? 'gemini-2.0-flash';
                    break;
                case 'openai':
                    $agent = OpenAI::class;
                    $model = $this->argument('model') ?? 'gpt-3.5-turbo';
                    break;
                default:
                    throw new \Exception('Agent not found for translation, use "gemini" or "openai"');
            };            

            //ask question
            $apiKey = $this->secret('What is your api key?');

            $response = Agent::using($agent)
                ->withKey($apiKey)
                ->model($model)
                ->translateLocalization($langFrom, $langTo);
                
            $this->newLine();
            $this->info(count($response) . ' files translated');
            $this->newLine();

            return Command::SUCCESS;
        });
    }
}
