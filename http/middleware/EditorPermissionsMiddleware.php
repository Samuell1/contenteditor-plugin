<?php namespace Samuell\ContentEditor\Http\Middleware;

use Backend\Facades\BackendAuth;
use Closure;

/**
 * EditorPermissionsMiddleware
 *
 * Allow only backend user with editor permission
 */
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
