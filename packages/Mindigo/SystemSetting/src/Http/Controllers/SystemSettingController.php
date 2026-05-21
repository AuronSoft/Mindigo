<?php

namespace Mindigo\SystemSetting\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\SystemSetting\Http\Requests\UpdateSystemSettingRequest;
use Mindigo\SystemSetting\Services\SystemSettingService;

class SystemSettingController extends Controller
{
    public function __construct(
        private readonly SystemSettingService $service,
        private readonly AuditLogService $auditLog
    ) {}

    public function index(): View
    {
        return view('Mindigo-system-setting::index', [
            'groups' => $this->service->groupedSettings(),
        ]);
    }

    public function update(UpdateSystemSettingRequest $request): RedirectResponse
    {
        $changed = $this->service->update($request->validated('settings'));

        if ($changed === 0) {
            return redirect()
                ->route('system-settings.index')
                ->with('info', __('Mindigo-system-setting::app.messages.no_changes'));
        }

        $changes = $this->service->changes();

        $this->auditLog->record(
            action: 'update',
            module: 'system_settings',
            oldValues: collect($changes)->map(fn (array $change) => $change['old'])->all(),
            newValues: collect($changes)->map(fn (array $change) => $change['new'])->all(),
            metadata: [
                'changed_count' => $changed,
                'changes' => $changes,
            ],
        );

        return redirect()
            ->route('system-settings.index')
            ->with('success', __('Mindigo-system-setting::app.messages.updated'));
    }
}
