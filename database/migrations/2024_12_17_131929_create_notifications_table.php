<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $tableNames = config('notification.table_names');
        $columnNames = config('notification.column_names');


        if (empty($tableNames)) {
            throw new \Exception('Error: config/notification.php not loaded. Run [php artisan config:clear] and try again.');
        }

        if (empty($columnNames['translation_foreign_key'] ?? null)) {
            throw new \Exception('Error: translation_foreign_key on config/notification.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create($tableNames['notifications'], function (Blueprint $table){
            $table->id();
            $table->morphs('model');
            $table->nullableMorphs('related');
            $table->timestamp('seen_at')->nullable();
            $table->json('extra_fields')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create($tableNames['notification_translations'], function (Blueprint $table) use($tableNames ,$columnNames) {
            $foreignKey = $columnNames['translation_foreign_key'];
            $table->id();
            $table->foreignId($foreignKey)
                ->references('id')
                ->on($tableNames['notifications'])
                ->onDelete('cascade');

            $table->string('locale')->index();
            $table->string('title');
            $table->text('body');

            $table->unique([$foreignKey, 'locale']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('notification.table_names');
        Schema::dropIfExists($tableNames['notifications']);
        Schema::dropIfExists($tableNames['notification_translations']);
    }
};
