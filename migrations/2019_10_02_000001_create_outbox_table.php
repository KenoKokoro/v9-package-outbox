<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutboxTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('outbox', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('channel');
            $table->text('content');
            $table->timestamp('send_at');
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('pending');
            $table->string('subject_id');
            $table->string('subject_type');
            $table->string('receiver_id');
            $table->string('receiver_type');
            $table->integer('try')->default(0);
            $table->text('error')->nullable();

            $table->timestamps();
            $table->index(['subject_id', 'subject_type']);
            $table->index(['receiver_id', 'receiver_type']);
            $table->index(['send_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outbox');
    }
}
