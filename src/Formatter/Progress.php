<?php

namespace Fruit\BenchKit\Formatter;

use Fruit\BenchKit\Benchmark;

interface Progress
{
    public function format($group, Benchmark $b);
}
