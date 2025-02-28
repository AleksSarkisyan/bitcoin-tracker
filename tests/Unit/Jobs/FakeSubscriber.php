<?php

namespace Tests\Unit\Jobs;

class FakeSubscriber extends \ArrayObject
{
    public $updatedData = null;
    public $job;
    public $subscriber;

    public function update(array $data)
    {
        $this->updatedData = $data;
    }
}
