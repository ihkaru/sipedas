<?php

namespace App\Http\Controllers;

use App\Models\CustomPage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomPageController extends Controller
{
    /**
     * Display the specified custom page.
     */
    public function show(string $slug): Response
    {
        $page = CustomPage::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response($page->content)
            ->header('Content-Type', 'text/html');
    }
}
