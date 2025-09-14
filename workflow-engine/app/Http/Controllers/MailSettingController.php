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
        try {
            $data = $request->validate([
                'provider' => 'required|in:m365,gmail,custom',
                'host' => 'required|string|max:255',
                'port' => 'required|integer|min:1|max:65535',
                'encryption' => 'nullable|in:tls,ssl',
                'username' => 'nullable|string|max:255',
                'password' => 'nullable|string|max:255',
                'from_address' => 'nullable|email|max:255',
                'from_name' => 'nullable|string|max:255',
                'is_active' => 'boolean',
            ]);

            $setting = MailSetting::create([
                ...$data,
                'password_encrypted' => isset($data['password']) && !empty($data['password']) ? encrypt($data['password']) : null,
            ]);

            if (! empty($data['is_active'])) {
                MailConfigService::applyActive();
            }

            return response()->json($setting, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create SMTP setting'], 500);
        }
    }

    public function show(MailSetting $mailSetting)
    {
        return response()->json($mailSetting);
    }

    public function update(Request $request, MailSetting $mailSetting)
    {
        try {
            $data = $request->validate([
                'provider' => 'required|in:m365,gmail,custom',
                'host' => 'required|string|max:255',
                'port' => 'required|integer|min:1|max:65535',
                'encryption' => 'nullable|in:tls,ssl',
                'username' => 'nullable|string|max:255',
                'password' => 'nullable|string|max:255',
                'from_address' => 'nullable|email|max:255',
                'from_name' => 'nullable|string|max:255',
                'is_active' => 'boolean',
            ]);

            $updateData = $data;
            
            // Only update password if provided
            if (isset($data['password']) && !empty($data['password'])) {
                $updateData['password_encrypted'] = encrypt($data['password']);
            }
            
            // Remove password from update data if not provided
            unset($updateData['password']);

            $mailSetting->update($updateData);

            // If this setting is being activated, apply it
            if (!empty($data['is_active'])) {
                MailConfigService::applyActive();
            }

            return response()->json($mailSetting);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update SMTP setting'], 500);
        }
    }

    public function destroy(MailSetting $mailSetting)
    {
        try {
            $mailSetting->delete();
            return response()->json(['message' => 'SMTP setting deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete SMTP setting'], 500);
        }
    }

    public function test(Request $request)
    {
        try {
            $data = $request->validate(['to' => 'required|email']);
            $to = $data['to'];
            
            MailConfigService::applyActive();
            
            Mail::raw('SMTP test message from Workflow Engine', function ($m) use ($to) {
                $m->to($to)->subject('SMTP Test - Workflow Engine');
            });
            
            return response()->json(['status' => 'sent', 'message' => 'Test email sent successfully']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Invalid email address', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send test email: ' . $e->getMessage()], 500);
        }
    }
}

