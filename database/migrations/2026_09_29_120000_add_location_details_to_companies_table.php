<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationDetailsToCompaniesTable extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('subdistrict')->nullable()->after('address');
            $table->string('district')->nullable()->after('subdistrict');
            $table->string('province')->nullable()->after('district');
            $table->string('zipcode', 20)->nullable()->after('province');
            $table->string('google_maps_url', 2048)->nullable()->after('zipcode');
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['subdistrict', 'district', 'province', 'zipcode', 'google_maps_url']);
        });
    }
}
