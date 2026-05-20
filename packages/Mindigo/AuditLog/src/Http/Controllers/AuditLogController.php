<?php

namespace Mindigo\AuditLog\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\AuditLog\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = '%' . trim($request->string('keyword')) . '%';

                $query->where(function ($query) use ($keyword) {
                    $query->where('user_name', 'like', $keyword)
                        ->orWhere('user_email', 'like', $keyword)
                        ->orWhere('module', 'like', $keyword)
                        ->orWhere('action', 'like', $keyword)
                        ->orWhere('route_name', 'like', $keyword);
                });
            })
            ->when($request->filled('module'), fn ($query) => $query->where('module', (string) $request->string('module')))
            ->when($request->filled('action'), fn ($query) => $query->where('action', (string) $request->string('action')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('Mindigo-audit-log::index', [
            'logs' => $logs,
            'modules' => AuditLog::query()->select('module')->distinct()->orderBy('module')->pluck('module'),
            'actions' => AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
        ]);
    }

    public function show(AuditLog $auditLog): View
    {
        return view('Mindigo-audit-log::show', [
            'log' => $auditLog,
        ]);
    }
}
