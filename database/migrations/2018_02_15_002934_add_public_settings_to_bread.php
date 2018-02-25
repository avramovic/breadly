<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->boolean('public_browse_read')->default(false)->after('server_side');
            $table->boolean('public_add')->default(false)->after('public_browse_read');
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
            $table->dropColumn(['public_browse_read', 'public_add']);
        });
    }
}
