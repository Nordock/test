<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHIncomecalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('h_incomecal', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_customer')->nullable();
            $table->integer('id_user');
            $table->string('driver_name');
            $table->string('driver_id_card');
            $table->integer('id_applicator')->nullable();
            $table->date('date_of_transaction')->nullable();
            $table->string('time_of_transaction')->nullable();
            $table->integer('work_days')->nullable();
            $table->integer('amount')->nullable()->default('0');
            $table->string('trans_type')->nullable();
            $table->string('trans_value')->nullable()->default('0');
            $table->string('trans_cost_value')->nullable()->default('0');
            $table->string('incentive')->nullable()->default('0');
            $table->string('other_income')->nullable()->default('0');
            $table->string('commission')->nullable()->default('0');
            $table->string('rental_cost')->nullable()->default('0');
            $table->string('adjustment')->nullable()->default('0');
            $table->tinyInteger('is_delete')->default(0);
            $table->date('submit_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('h_incomecal');
    }
}
