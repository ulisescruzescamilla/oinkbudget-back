<?php

use App\Enums\PeriodEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn(['period', 'is_active', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->enum('period', PeriodEnum::array())->after('max_limit')->default('monthly');
            $table->boolean('is_active')->after('is_recurrent')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable()->after('start_date');
        });
    }
};
