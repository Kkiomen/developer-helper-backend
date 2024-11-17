<?php

namespace App\Api;

use Pusher\Pusher;

class PusherEvent
{
    /**
     * Send event to Pusher
     * @param $channels
     * @param string $event
     * @param $data
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Pusher\ApiErrorException
     * @throws \Pusher\PusherException
     */
    public static function sendEvent($channels, string $event, $data): mixed
    {
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true
            ]
        );

        return $pusher->trigger($channels, $event, $data);
    }
}
