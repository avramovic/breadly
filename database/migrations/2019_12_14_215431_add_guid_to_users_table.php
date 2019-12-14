<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use TCG\Voyager\Models\Setting;

class AddGuidToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->string('guid')->unique()->nullable()->after('id')->index();
        });

        $setting = Setting::firstOrNew(['key' => 'site.enable_guid_login']);
        if (!$setting->exists) {
            $setting->fill([
                'display_name' => 'Enable GUID login',
                'value'        => '0',
                'details'      => 'Enable simple login with device GUID',
                'type'         => 'checkbox',
                'order'        => 4,
                'group'        => 'Site',
            ])->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['guid']);
        });
    }
}
