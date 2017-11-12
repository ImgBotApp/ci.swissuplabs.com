<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('repository_id');
            $table->string('ref', 64)->index();
            $table->char('sha', 40)->index();
            $table->string('tag', 20)->nullable()->index();
            $table->text('data');
            $table->timestamps();

            $table->foreign('repository_id')
                ->references('id')->on('repositories')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commits');
    }
}
