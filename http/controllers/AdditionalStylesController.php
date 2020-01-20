<?php namespace Samuell\ContentEditor\Http\Controllers;

use Response;
use Illuminate\Routing\Controller;
use Samuell\ContentEditor\Models\Settings;

class AdditionalStylesController extends Controller
{
    public function render()
    {
        return Response::make(Settings::renderCss(), 200)
            ->header('Content-Type', 'text/css')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
