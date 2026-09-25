<?php

namespace Doppar\AI\Tests\Fixtures;

use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool('get_weather', 'Get the current weather for a city')]
class WeatherTool
{
    /**
     * @var array<int, string>
     */
    public array $calledWith = [];

    public function __invoke(string $city): string
    {
        $this->calledWith[] = $city;

        return "Sunny in {$city}";
    }
}
