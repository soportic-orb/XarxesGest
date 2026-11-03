<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_reports', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained()->cascadeOnDelete();
            $table->date('month');
            $table->string('timezone');
            $table->json('data');
            $table->json('summary')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->unique(['workspace_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_reports');
    }
};
