<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->after('title');
            $table->string('subname')->nullable()->after('role');
            $table->date('birthdate')->nullable()->after('subname');
            $table->string('rs')->nullable()->after('cnpj');
            $table->string('fb_page')->nullable()->after('facebook');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'subname', 'birthdate', 'rs', 'fb_page',
            ]);
        });
    }
};
