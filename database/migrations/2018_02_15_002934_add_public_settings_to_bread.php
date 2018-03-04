<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPublicSettingsToBread extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_types', function (Blueprint $table) {
            $table->boolean('public_browse')->default(false)->after('server_side');
            $table->boolean('public_read')->default(false)->after('public_browse');
            $table->boolean('public_add')->default(false)->after('public_read');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_types', function (Blueprint $table) {
            $table->dropColumn(['public_browse', 'public_read', 'public_add']);
        });
    }
}
