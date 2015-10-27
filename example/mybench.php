<?php

use Fruit\BenchKit\Benchmark;

function myfunc($i)
{
    return $i;
}

function BenchmarkCUFA(Benchmark $b)
{
    for ($i = 0; $i < $b->n; $i++) {
        call_user_func_array('myfunc', array($i));
    }
}

function BenchmarkCUF(Benchmark $b)
{
    for ($i = 0; $i < $b->n; $i++) {
        call_user_func('myfunc', $i);
    }
}

function BenchmarkDynamic(Benchmark $b)
{
    $a = "myfunc";
    for ($i = 0; $i < $b->n; $i++) {
        $a($i);
    }
}

function BenchmarkStatic(Benchmark $b)
{
    for ($i = 0; $i < $b->n; $i++) {
        myfunc($i);
    }
}

class MyBench
{
    public function BenchmarkCUFA(Benchmark $b)
    {
        for ($i = 0; $i < $b->n; $i++) {
            call_user_func_array('myfunc', array($i));
        }
    }

    public function BenchmarkCUF(Benchmark $b)
    {
        for ($i = 0; $i < $b->n; $i++) {
            call_user_func('myfunc', $i);
        }
    }

    public function BenchmarkDynamic(Benchmark $b)
    {
        $a = "myfunc";
        for ($i = 0; $i < $b->n; $i++) {
            $a($i);
        }
    }

    public function BenchmarkStatic(Benchmark $b)
    {
        for ($i = 0; $i < $b->n; $i++) {
            myfunc($i);
        }
    }
}
