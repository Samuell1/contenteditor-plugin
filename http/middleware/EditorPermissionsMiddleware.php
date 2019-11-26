<?php namespace Samuell\ContentEditor\Http\Middleware;

use Closure;
use Backend\Facades\BackendAuth;

class EditorPermissionsMiddleware
{
    public function handle($request, Closure $next)
    {
        $backendUser = BackendAuth::getUser();
        if ($backendUser && $backendUser->hasAccess('samuell.contenteditor.editor')) {
            return $next($request);
        }

        return abort(404);
    }
}
