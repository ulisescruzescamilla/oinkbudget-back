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
            $table->enum('period', PeriodEnum::array())->after('max_limit')->default('monthly');
            $table->boolean('is_recurrent')->after('period')->default(false);
            $table->boolean('is_active')->after('is_recurrent')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn('period');
            $table->dropColumn('is_recurrent');
            $table->dropColumn('is_active');
        });
    }
};
