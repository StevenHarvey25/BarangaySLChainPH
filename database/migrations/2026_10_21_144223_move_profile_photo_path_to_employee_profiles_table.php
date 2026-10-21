<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->string('photo_path')->nullable();
        });

        DB::table('users')
            ->join('employee_profiles', 'employee_profiles.user_id', '=', 'users.id')
            ->whereNotNull('users.profile_photo_path')
            ->select('users.id', 'users.profile_photo_path')
            ->orderBy('users.id')
            ->get()
            ->each(function (object $user): void {
                DB::table('employee_profiles')
                    ->where('user_id', $user->id)
                    ->update(['photo_path' => $user->profile_photo_path]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo_path')->nullable();
        });

        DB::table('employee_profiles')
            ->whereNotNull('photo_path')
            ->select('user_id', 'photo_path')
            ->orderBy('user_id')
            ->get()
            ->each(function (object $profile): void {
                DB::table('users')
                    ->where('id', $profile->user_id)
                    ->update(['profile_photo_path' => $profile->photo_path]);
            });

        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};
