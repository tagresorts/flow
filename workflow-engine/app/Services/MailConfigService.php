<?php

namespace App\Services;

use App\Models\MailSetting;

class MailConfigService
{
    public static function applyActive(): void
    {
        $setting = MailSetting::where('is_active', true)->latest()->first();
        if (! $setting) return;

        config([
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $setting->host,
            'mail.mailers.smtp.port' => $setting->port,
            'mail.mailers.smtp.encryption' => $setting->encryption ?: null,
            'mail.mailers.smtp.username' => $setting->username,
            'mail.mailers.smtp.password' => decrypt($setting->password_encrypted ?? ''),
            'mail.from.address' => $setting->from_address,
            'mail.from.name' => $setting->from_name,
        ]);
    }
}

