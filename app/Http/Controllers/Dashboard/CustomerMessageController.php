<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Models\CustomerMessage;
use App\Http\Controllers\Controller;

class CustomerMessageController extends Controller
{
    public function index()
    {
        $data = CustomerMessage::latest()->get();
        return view('dashboard.pages.customer-message.index', compact('data'));
    }
}
