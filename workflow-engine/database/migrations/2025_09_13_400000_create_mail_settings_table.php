<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('custom'); // m365|gmail|custom
            $table->string('host');
            $table->unsignedInteger('port');
            $table->string('encryption')->nullable(); // tls|ssl|null
            $table->string('username')->nullable();
            $table->text('password_encrypted')->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_settings');
    }
};

