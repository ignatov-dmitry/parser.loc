<?php

namespace App\Http\Controllers;

use App\TestTable;
use Illuminate\Http\Request;

class PhoneController extends Controller
{
    public function index(){
        TestTable::insert([
            'text' => json_encode($_SERVER)
        ]);
    }
}
