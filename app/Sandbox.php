<?php

namespace Judgement;

use Illuminate\Database\Eloquent\Model;

class Sandbox extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'is_available'
    ];

    public static function getAvailableSandbox()
    {
        $sandbox = static::where('is_available', '=', 1)->first();
        $sandbox->is_available = 0;
        $sandbox->save();
        return $sandbox;
    }

    public function path()
    {
        return '/tmp/box/' . $this->id . '/box/';
    }

    public function init()
    {
        //isolate --b [--cg] --init 2>&1
        $command =
            'isolate' .
            ' -b' . $this->id .
            ' --cg' .
            ' --init' .
            ' 2>&1';

        exec($command, $output, $status);
        return $status;
    }

    public function run($cg, $mem, $cgmem, $time, $meta, $stdin, $stdout, $processes, $command)
    {
        //isolate --cg -b [-m] [-t] [-M] [-i] [-o] [-p] --run --
        $command =
            'isolate' .
            ' -b' . $this->id .
            ($cg ? ' --cg' : '') .
            ($mem ? ' -m' . $mem : '') .
            ($cgmem ? ' --cg-mem=' . $cgmem : '') .
            ($time ? ' -t' . $time : '') .
            ($meta ? ' -M' . $meta : '') .
            ($stdin ? ' -i' . $stdin : '') .
            ($stdout ? ' -o' . $stdout : '') .
            ($processes ? ' -p' . $processes : '') .
            ' --run' .
            ' -- ' . $command .
            ' 2>&1';

        exec($command, $output, $status);
        dump($command, $output, $status);
        return $status;
    }

    public function clean()
    {
        //isolate -bID [--cg] --cleanup 2>&1
        $command =
            'isolate' .
            ' -b' . $this->id .
            ' --cg' .
            ' --cleanup' .
            ' 2>&1';

        exec($command, $output, $status);

        $this->is_available = 1;
        $this->save();
        return $status;
    }
}
