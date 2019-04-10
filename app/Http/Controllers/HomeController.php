<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Gallery;
use App\Banner;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $foodGalleries = Gallery::where('type', 'FOOD')->get();
        $serviceGalleries = Gallery::where('type', 'SERVICE')->get();
        $bgImage = Banner::first();


        return view('index', compact('foodGalleries', 'serviceGalleries', 'bgImage'));
    }
}
