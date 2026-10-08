<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        return view('operational.pos');
    }
}
