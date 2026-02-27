<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TutorialController extends Controller
{
    /**
     * Menampilkan halaman tutorial / panduan aplikasi.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('tutorial.index');
    }
}
