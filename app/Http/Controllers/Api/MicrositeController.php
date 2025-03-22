<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Microsite;
use Illuminate\Http\JsonResponse;

class MicrositeController extends Controller
{
    public function show($slug): JsonResponse
    {
        $microsite = Microsite::where('slug', $slug)
            ->where('is_active', true)
            ->with(['links' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('order');
            }])
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $microsite
        ]);
    }
}
