<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke()
    {
        $content = implode(PHP_EOL, [
            'User-agent: *',
            'Allow: /',
            'Allow: /sitemap.xml',
            'Disallow: /admin/*',
            'Disallow: /user/*', // Based on UserPanelProvider path
            'Disallow: /app/*',  // Covering the requested path just in case
        ]);

        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }
}
