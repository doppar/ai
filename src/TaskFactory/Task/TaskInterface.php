<?php

namespace Doppar\AI\TaskFactory\Task;

interface TaskInterface
{
    /**
     * Execute the task with the provided input data.
     *
     * @param mixed $datas Input parameters for the task.
     * @return mixed
     */
    public function execute(mixed $datas): mixed;
}
