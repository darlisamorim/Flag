<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('addr')->nullable()->after('location');
            $table->string('district')->nullable()->after('addr');
            $table->string('zip')->nullable()->after('district');
            $table->string('country')->nullable()->after('zip');
            $table->string('cnpj')->nullable()->after('country');
            $table->string('ie')->nullable()->after('cnpj');
            $table->string('links')->nullable()->after('ie');
            $table->string('tiktok')->nullable()->after('links');
            $table->string('facebook')->nullable()->after('tiktok');
            $table->string('medium')->nullable()->after('facebook');
            $table->string('devto')->nullable()->after('medium');
            $table->string('codepen')->nullable()->after('devto');
            $table->string('behance')->nullable()->after('codepen');
            $table->string('dribbble')->nullable()->after('behance');
            $table->string('deviantart')->nullable()->after('dribbble');
            $table->string('pinterest')->nullable()->after('deviantart');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'addr', 'district', 'zip', 'country', 'cnpj', 'ie',
                'links', 'tiktok', 'facebook', 'medium', 'devto',
                'codepen', 'behance', 'dribbble', 'deviantart', 'pinterest',
            ]);
        });
    }
};
