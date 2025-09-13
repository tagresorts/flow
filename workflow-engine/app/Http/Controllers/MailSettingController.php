<?php

namespace App\Http\Controllers;

use App\Models\MailSetting;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailSettingController extends Controller
{
    public function index()
    {
        return MailSetting::latest()->paginate(10);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'provider' => 'required|in:m365,gmail,custom',
            'host' => 'required|string',
            'port' => 'required|integer',
            'encryption' => 'nullable|in:tls,ssl',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'from_address' => 'nullable|email',
            'from_name' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $setting = MailSetting::create([
            ...$data,
            'password_encrypted' => isset($data['password']) ? encrypt($data['password']) : null,
        ]);

        if (! empty($data['is_active'])) {
            MailConfigService::applyActive();
        }

        return response()->json($setting, 201);
    }

    public function test(Request $request)
    {
        MailConfigService::applyActive();
        $to = $request->validate(['to' => 'required|email'])['to'];
        Mail::raw('SMTP test message', function ($m) use ($to) {
            $m->to($to)->subject('SMTP Test');
        });
        return ['status' => 'sent'];
    }
}

