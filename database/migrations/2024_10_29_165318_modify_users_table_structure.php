<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUsersTableStructure extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 既存のusersテーブルが存在する場合は削除する
        Schema::dropIfExists('users');

        // 新しい構造でテーブルを作成
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('extension', 20)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('department', 50)->nullable();
            $table->string('position', 50)->nullable();
            $table->string('email', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}