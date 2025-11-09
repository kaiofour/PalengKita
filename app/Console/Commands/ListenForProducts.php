<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Ratchet\Client\connect;
use React\EventLoop\Loop;

class ListenForProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Connects to the WebSocket server to listen for product updates.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Connecting to WebSocket server...');

        $loop = Loop::get();

        $connector = new \Ratchet\Client\Connector($loop);

        $connector('ws://127.0.0.1:8080')->then(function($conn) {
            $this->info('Successfully connected to WebSocket server!');

            $conn->on('message', function($msg) use ($conn) {
                $this->info("Received: {$msg}");
                $products = json_decode($msg, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    Cache::put('products.latest', $products, now()->addMinutes(60)); // Cache for 1 hour
                    $this->info('Product cache updated.');
                } else {
                    $this->warn('Received invalid JSON.');
                }
            });

            $conn->on('close', function($code = null, $reason = null) {
                $this->error("Connection closed ({$code} - {$reason})");
                // You might want to add reconnection logic here
            });

        }, function($e) {
            $this->error("Could not connect: {$e->getMessage()}");
        });
    }
}
