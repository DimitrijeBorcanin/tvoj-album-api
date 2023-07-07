<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function show(Template $template){
        $template->load('positions');
        $positions = Position::where('template_id', $template->id)->orderBy('page')->get();
        $pages = $positions->groupBy('page')->map(function($value, $key) {
            return ["page_no" => $key, "positions" => $value];
        });
        $tempaltePages = [];
        foreach($pages as $page){
            array_push($tempaltePages, $page);
        }
        $template->pages = $tempaltePages;
        return response()->json([
            'status' => true,
            'messages' => 'Uspešno.',
            'data' => $template
        ], 200);
    }
}
