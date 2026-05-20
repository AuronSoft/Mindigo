<?php

namespace Mindigo\SystemSetting\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\SystemSetting\Http\Requests\UpdateSystemSettingRequest;
use Mindigo\SystemSetting\Services\SystemSettingService;

class SystemSettingController extends Controller
{
    public function __construct(
        private readonly SystemSettingService $service
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
                ->with('info', 'Chưa có trường dữ liệu nào thay đổi.');
        }

        return redirect()
            ->route('system-settings.index')
            ->with('success', 'Cấu hình hệ thống đã được cập nhật.');
    }
}
