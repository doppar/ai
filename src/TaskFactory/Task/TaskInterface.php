<?php

namespace Doppar\Transformer\TaskFactory\Task;

interface TaskInterface
{
    public function execute(mixed $datas): mixed;
}
