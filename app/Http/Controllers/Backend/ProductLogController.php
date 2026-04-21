<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ProductLog;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductLogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getData($request);
        }

        return view('backend.pages.product-logs.index');
    }

    public function getData(Request $request)
    {
        $query = ProductLog::with('user')
            ->select('product_logs.*');

        // Apply filters
        if ($request->product_id) {
            $query->where('product_number', 'like', '%' . $request->product_id . '%');
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->start_date) {
            $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $query->where('created_at', '>=', $startDate);
        }

        if ($request->end_date) {
            $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
            $query->where('created_at', '<=', $endDate);
        }

        return DataTables::of($query)
            ->addColumn('user_name', function ($log) {
                return $log->user->name ?? __('product_logs.unknown_user');
            })
            ->addColumn('formatted_date', function ($log) {
                return $log->created_at->format('Y-m-d H:i:s');
            })
            ->addColumn('action', function ($log) {
                return '<a href="' . route('product-logs.show', $log->id) . '" class="btn btn-success text-white">' . __('product_logs.view') . '</a>';
            })
            ->rawColumns(['action'])
            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('created_at', $order);
            })
            ->filterColumn('user_name', function($query, $keyword) {
                $query->whereHas('user', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->make(true);
    }

    public function show($id)
    {
        $log = ProductLog::with('user')->findOrFail($id);
        return view('backend.pages.product-logs.show', compact('log'));
    }
}
