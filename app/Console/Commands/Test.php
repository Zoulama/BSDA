<?php

namespace Provisioning\Console\Commands;

use Illuminate\Console\Command;
use Provisioning\Centile\SoapDriver;
use Provisioning\Centile\Centile;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function createRanges($pstns)
    {
        $ranges = [];

        sort($pstns);
        for ($i = 0; $i < count($pstns); ++$i) {
            $start = $pstns[$i];
            for ($j = $i; $j < count($pstns); ++$j) {
                if ($j + 1 >= count($pstns)) {
                    $ranges[] = ['start' => $start, 'end' => $pstns[$j]];

                    return $ranges;
                }
                $delta = $pstns[$j + 1] - $pstns[$j];
                if ($delta !== 1) {
                    $ranges[] = ['start' => $start, 'end' => $pstns[$j]];
                    $i = $j;
                    break;
                }
            }
        }
    }

    public function processDialPlan($dialplan, $input = null)
    {
        $plans = explode(';', $dialplan);
        $extensions = [];
        $ret = [];

        foreach ($plans as $plan) {
            $split = explode(':', $plan);
            if (count($split) > 1) {
                $n = $split[0];
                $prefixes = explode(',', $split[1]);
                foreach ($prefixes as $prefix) {
                    $pow = pow(10, $n - strlen($prefix));
                    $times = $n - strlen($prefix);
                    $base = $prefix * $pow;
                    for ($i = 0; $i < $pow; ++$i) {
                        $extensions[] = $base + $i;
                    }
                }
            }
        }
        if ($input) {
            foreach ($extensions as $extension) {
                if (substr($extension, 0, strlen($input)) === $input) {
                    $ret[] = $extension;
                }
            }

            return count($ret) > 0 ? $ret : null;
        }

        return count($extensions) > 0 ? $ret : null;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    }
}
