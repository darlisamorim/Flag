<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('title')->nullable()->after('avatar');
            $table->text('bio')->nullable()->after('title');
            $table->string('phone')->nullable()->after('bio');
            $table->string('location')->nullable()->after('phone');
            $table->string('website')->nullable()->after('location');
            $table->string('github')->nullable()->after('website');
            $table->string('linkedin')->nullable()->after('github');
            $table->string('twitter')->nullable()->after('linkedin');
            $table->string('instagram')->nullable()->after('twitter');
            $table->string('youtube')->nullable()->after('instagram');
            $table->string('locale')->default('pt_BR')->after('youtube');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar', 'title', 'bio', 'phone', 'location',
                'website', 'github', 'linkedin', 'twitter',
                'instagram', 'youtube', 'locale',
            ]);
        });
    }
};
