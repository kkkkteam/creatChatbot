<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_details', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();

            //----------------------------------------------------------------------------------------
			//  Columns for this class
            $table->string('intention', 64);
            $table->datetime('start_at')->nullable();
            $table->datetime('end_at')->nullable();
            $table->string('tag', 128)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon_details');
    }
}
