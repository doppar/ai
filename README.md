## Introduction to the transformer package

Use AI directly in PHP, self host model ! using TransformersPHP : https://transformers.codewithkyrian.com/
And Symfony AI Agent : https://github.com/symfony/ai-agent

### Installation

```shell
composer require doppar/transformer
```
Make sure FFI is enabled in your php.ini file. 
Do not let it in `preload` mode.

OR : start php server with -d ffi.enable=1

```shell
php -d ffi.enable=1 -S localhost:8000 -t public server.php 
```

### How to use :

#### Quick test with package command : 

```shell
php pool transformer:run "Hello, how are you ?"
```

#### Use it in controller : 

```php
use Doppar\AI\Pipeline;
use Doppar\AI\Enum\TaskEnum;

$messages = [
    ['role' => 'user', 'content' => 'Resolve 5 * 4 ?'],
];
$output = Pipeline::execute(
    task: TaskEnum::TEXT_GENERATION,
    model: 'HuggingFaceTB/SmolLM2-360M-Instruct',
    messages : $messages
);

// "generated_text" => "5 * 4 = 20"

```

First time, model will be downloaded, then it will be cached in storage/app/transformers.