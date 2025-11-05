## Introduction to the transformer package

Use AI directly in PHP, self host model ! using TransformersPHP : https://transformers.codewithkyrian.com/

### How to use :

#### Quick test with package command : 

```shell
php pool transformer:run "Hello, how are you ?"
```

#### Quick test with package command : 

```php
use Doppar\Transformer\Pipeline;
use Doppar\Transformer\Enum\TaskEnum;

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