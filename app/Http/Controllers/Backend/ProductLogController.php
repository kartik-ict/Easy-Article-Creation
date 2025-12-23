<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ProductLog;
use Illuminate\Http\Request;

class ProductLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductLog::with('user')
            ->orderBy('created_at', 'desc');

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->action) {
            $query->where('action', $request->action);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->paginate(50);

        return view('backend.pages.product-logs.index', compact('logs'));
    }

    public function show($id)
    {
        $log = ProductLog::with('user')->findOrFail($id);
        return view('backend.pages.product-logs.show', compact('log'));
    }
}
