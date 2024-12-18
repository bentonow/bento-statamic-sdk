<?php

namespace Bento\BentoStatamic\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BentoJsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only process HTML responses
        if (!$this->isHtmlResponse($response)) {
            return $response;
        }

        // Check if Bento JS should be injected
        if (config('bento.enabled') && config('bento.inject_js')) {
            $content = $response->getContent();

            // Get the site UUID from config
            $site_uuid = config('bento.site_uuid');

            // Create the script tag
            $script = PHP_EOL . '<script src="https://fast.bentonow.com?site_uuid=' . $site_uuid . '" async defer></script>' . PHP_EOL;

            // Insert before closing </head> tag
            if (str_contains($content, '</head>')) {
                $content = str_replace('</head>', $script . '</head>', $content);
            } else {
                // If no </head>, try to insert before </body>
                $content = str_replace('</body>', $script . '</body>', $content);
            }

            $response->setContent($content);
        }

        return $response;
    }

    protected function isHtmlResponse($response): bool
    {
        return $response instanceof Response &&
            str_contains($response->headers->get('Content-Type', ''), 'text/html');
    }
}
