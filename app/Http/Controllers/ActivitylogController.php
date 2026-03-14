<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')
            ->latest('created_at');

        if ($q = $request->get('q')) {
            $query->where('description', 'like', "%$q%");
        }
        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }
        if ($module = $request->get('module')) {
            $query->where('module', $module);
        }
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(30)->withQueryString();
        $users = User::where('pharmacy_id', auth()->user()->pharmacy_id)
            ->orderBy('name')->get(['id', 'name']);
        $actions = ['create', 'update', 'delete', 'login', 'logout', 'export', 'print', 'approve', 'cancel', 'receive'];
        $modules = ['invoice', 'purchase', 'medicine', 'batch', 'customer', 'supplier', 'user', 'inventory', 'return_invoice'];

        // Stats hôm nay
        $todayStats = ActivityLog::whereDate('created_at', today())
            ->selectRaw('action, count(*) as cnt')
            ->groupBy('action')
            ->pluck('cnt', 'action');

        return view('activity.index', compact('logs', 'users', 'actions', 'modules', 'todayStats'));
    }
}