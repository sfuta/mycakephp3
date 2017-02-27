<?php
namespace App\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Log\Log;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise as P;

/**
 * Simple console wrapper around Psy\Shell.
 */
class TmpShell extends Shell
{
    protected function _welcome() {}

    /**
     * Start the shell and interactive console.
     *
     * @return int|null
     */
    public function main()
    {
        foreach (range(1, 5) as $index) {
            $promise = new Promise();
            $promise->then(function($data) {
                echo $data . PHP_EOL;
                foreach (range(1,5) as $num) {
                    sleep(1);
                    echo "$data:$num" . PHP_EOL;
                }
                return;
            });
            $promises[] = $promise;
        }
        $promises[2]->resolve("");

        // foreach ($promises as $index => $promise) {
        //     $promise->resolve("Hello $index");
        // }

        while(true) {
            foreach ($promises as $promise) {
                if ($promise->getState() === Promise::FULFILLED) {
                    var_dump($promise->wait());
                    break 2;
                }
            }
        }
    }
    public static function _test_func()
    {
        return '!!!';
    }
}
