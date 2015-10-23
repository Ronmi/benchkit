<?php

namespace Fruit\BenchKit\Bin;

use CLIFramework\Command;
use Fruit\PathKit\Path;
use Fruit\BenchKit\Benchmarker;
use Fruit\BenchKit\Formatter\DefaultSummaryLogger;
use Fruit\BenchKit\Formatter\DefaultProgressLogger;
use ReflectionClass;

class RunCommand extends Command
{
    public function options($opt)
    {
        $i = 'run this file to initialize the environment. (setting up db, autoload etc.)';
        $opt->add('i|init?', $i)->isa('file');

        $b = 'running benchmark for at least this amount of time(seconds), default to 1';
        $opt->add('b|base:', $b)->isa('number')->defaultValue(1);

        $p = 'full class name (including namespace) of progress formatter, default to Fruit\Benchkit\Formatter\DefaultProgressLogger';
        $opt->add('p|progress:', $p)->isa('string')
            ->defaultValue('Fruit\BenchKit\Formatter\DefaultProgressLogger');

        $s = 'full class name (including namespace) of summary formatter, default to Fruit\Benchkit\Formatter\DefaultSummaryLogger';
        $opt->add('s|summary:', $s)->isa('string')
            ->defaultValue('Fruit\BenchKit\Formatter\DefaultSummaryLogger');
    }

    public function arguments($args)
    {
        $args->add('dir')->isa('dir')->multiple();
    }

    public function execute($dir)
    {
        $entry = "";
        @$entry = $this->options->init;
        $ttl = $this->options->base;
        if ($ttl <= 0) {
            $ttl = 1;
        }
        if (is_file($entry)) {
            require_once($entry);
        }
        $p = $this->options->progress;
        if (!class_exists($p)) {
            echo "$p does not exists.\n";
            return;
        }
        $ref = new ReflectionClass($p);
        if (!$ref->implementsInterface('Fruit\BenchKit\Formatter\Progress')) {
            echo "$p is not a correct progress formatter.\n";
            return;
        }
        $progress = $ref->newInstance();
        $s = $this->options->summary;
        if (!class_exists($s)) {
            echo "$s does not exist.\n";
            return;
        }
        $ref = new ReflectionClass($s);
        if (!$ref->implementsInterface('Fruit\BenchKit\Formatter\Summary')) {
            echo "$s is not a correct summary formatter.\n";
            return;
        }
        $summary = $ref->newInstance();

        $oldClasses = get_declared_classes();
        $oldFunctions = get_defined_functions();
        $pendingDirs = array((new Path($dir))->normalize());

        while (count($pendingDirs) > 0) {
            $subDirs = $this->requirePHPFiles(array_pop($pendingDirs));
            $pendingDirs = array_merge($pendingDirs, $subDirs);
        }

        $newClasses = get_declared_classes();
        $newFunctions = get_defined_functions();
        $funcs = array_diff($newFunctions['user'], $oldFunctions['user']);
        $classes = array_diff($newClasses, $oldClasses);

        $b = new Benchmarker($ttl);
        foreach ($funcs as $f) {
            $b->register($f);
        }

        foreach ($classes as $c) {
            $cls = new ReflectionClass($c);
            $methods = $cls->getMethods();
            foreach ($methods as $m) {
                $fn = $m->getName();
                if ($m->isConstructor() or
                    $m->isDestructor() or
                    $m->isAbstract() or
                    !$m->isPublic()) {

                    continue;
                }
                $b->register(array($c, $m->getName()));
            }
        }

        $b->run($summary, $progress);
    }

    private function requirePHPFiles($dir)
    {
        $ret = array();

        $children = glob((new Path("*", $dir))->expand());
        foreach ($children as $child) {
            if (is_dir($child)) {
                array_push($ret, $child);
                continue;
            }

            if (substr($child, strlen($child)-4) == '.php') {
                require_once($child);
            }
        }
        return $ret;
    }

}
