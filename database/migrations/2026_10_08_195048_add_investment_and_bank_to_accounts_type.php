<?php

use App\Enums\AccountTypeEnum;
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
        Schema::table('accounts', function (Blueprint $table) {
            $table->enum('type', AccountTypeEnum::values())->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->enum('type', [
                AccountTypeEnum::CASH->value,
                AccountTypeEnum::DEBIT_CARD->value,
                AccountTypeEnum::CREDIT_CARD->value,
            ])->change();
        });
    }
};
