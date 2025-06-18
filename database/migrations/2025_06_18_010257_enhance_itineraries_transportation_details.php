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
        Schema::table('itineraries', function (Blueprint $table) {
            // 移動手段の種類に電車を追加
            $table->enum('transportation_type', ['walking', 'bike', 'car', 'ferry', 'bus', 'train', 'airplane'])->nullable()->change();
            
            // 電車関連フィールド
            $table->string('train_line')->nullable()->comment('路線名');
            $table->string('departure_station')->nullable()->comment('出発駅');
            $table->string('arrival_station')->nullable()->comment('到着駅');
            $table->string('train_type')->nullable()->comment('列車種別（新幹線、特急等）');
            
            // バス・フェリー関連フィールド
            $table->string('departure_terminal')->nullable()->comment('出発ターミナル・港');
            $table->string('arrival_terminal')->nullable()->comment('到着ターミナル・港');
            $table->string('company')->nullable()->comment('運営会社');
            
            // 飛行機関連フィールド（空港情報の拡張）
            $table->string('departure_airport')->nullable()->comment('出発空港');
            $table->string('arrival_airport')->nullable()->comment('到着空港');
            
            // 一般的な場所・目的地情報
            $table->string('location')->nullable()->comment('場所・目的地');
            
            // 既存のdeparture_location, arrival_locationフィールドをより具体的に使用
            // これらは住所や詳細な位置情報用として保持
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itineraries', function (Blueprint $table) {
            // 移動手段の種類を元に戻す
            $table->enum('transportation_type', ['walking', 'bike', 'car', 'ferry', 'bus', 'airplane'])->nullable()->change();
            
            // 追加したフィールドを削除
            $table->dropColumn([
                'train_line',
                'departure_station', 
                'arrival_station',
                'train_type',
                'departure_terminal',
                'arrival_terminal',
                'company',
                'departure_airport',
                'arrival_airport',
                'location'
            ]);
        });
    }
};
