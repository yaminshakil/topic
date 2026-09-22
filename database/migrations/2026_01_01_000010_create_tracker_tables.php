<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 40)->unique();
            $t->string('name', 100);
            $t->string('icon', 16)->default('');
            $t->string('badge', 32)->default('');
            $t->integer('sort_order')->default(0);
        });

        Schema::create('employees', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('username', 60)->unique();
            $t->string('password');
            $t->boolean('is_active')->default(true);
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('topics', function (Blueprint $t) {
            $t->id();
            $t->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $t->string('title', 255);
            $t->string('category', 100)->default('Other');
            $t->string('link', 500)->default('');
            $t->foreignId('assigned_to')->nullable()->constrained('employees')->nullOnDelete();
            $t->integer('sort_order')->default(0);
            $t->boolean('is_done')->default(false);
            $t->dateTime('completed_at')->nullable();
            $t->timestamps();

            $t->index(['channel_id', 'is_done']);
        });

        Schema::create('rates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $t->decimal('amount', 10, 2)->default(0);

            $t->unique(['channel_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rates');
        Schema::dropIfExists('topics');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('channels');
    }
};
