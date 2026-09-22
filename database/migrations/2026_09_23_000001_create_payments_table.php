<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $t->string('period', 7);            // the month being paid for, "YYYY-MM"
            $t->decimal('amount', 10, 2);
            $t->date('paid_on');                // the day the money was actually handed over
            $t->string('note', 255)->nullable();
            $t->timestamps();

            $t->index(['employee_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
