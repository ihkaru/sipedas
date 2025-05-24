<?php

namespace App\Services;

use Error;
use Illuminate\Support\Facades\Http;

class WhatsappNotifier
{

    public static function send($targetNumber, $message)
    {
        $token = env('FONNTE_TOKEN');
        // dd($token, env('FONNTE_TOKEN'), $targetNumber, $message);
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post('https://api.fonnte.com/send', [
            'target' => $targetNumber, // contoh: '6281234567890'
            'message' => $message,
            'countryCode' => '62',
        ]);
        dd($response, $response->body());
        return $response;
        // if ($token == "") dd($token, env('FONNTE_TOKEN'), $targetNumber, $message);
        // try {
        //     $response = Http::withHeaders([
        //         'Authorization' => $token,
        //         'Content-Type' => 'application/x-www-form-urlencoded',
        //     ])->post('https://api.fonnte.com/send', [
        //         'target' => $targetNumber, // contoh: '6281234567890'
        //         'message' => $message,
        //         'countryCode' => '62',
        //     ]);
        //     return $response;
        // } catch (Error $e) {
        //     dd($e, $token, env('FONNTE_TOKEN'), $targetNumber, $message);
        // }
    }
}
