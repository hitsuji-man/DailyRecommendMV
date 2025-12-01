<?php

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
        Schema::table('users', function (Blueprint $table) {
            // デフォルト値を'ゲスト'に設定する
            $table->string('name')->default('ゲスト')->change();

            // emailカラムをnull許可に変更する
            $table->string('email')->nullable()->change();

            // passwordカラムをnull許可に変更する
            $table->string('password')->nullable()->change();

            // countryカラム追加
            $table->char('country', 2)->default('JP');

            // is_adminカラム追加
            $table->boolean('is_admin')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // デフォルトを削除
            $table->string('name')->change();

            // nullを許可しないに変更
            $table->string('email')->nullable(false)->change();

            // nullを許可しないに変更
            $table->string('password')->nullable(false)->change();

            // カラムを削除
            $table->dropColumn(['country', 'is_admin']);
        });
    }
};
