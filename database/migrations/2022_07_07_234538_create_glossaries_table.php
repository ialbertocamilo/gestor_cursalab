<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('glossaries', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('external_id')->nullable()->index();

            $table->string('name')->nullable();

            $table->boolean('active')->nullable()->default(true);

            $table->foreignId('categoria_id')->nullable()->constrained('taxonomies');
            $table->foreignId('laboratorio_id')->nullable()->constrained('taxonomies');
            $table->foreignId('condicion_de_venta_id')->nullable()->constrained('taxonomies');
            $table->foreignId('via_de_administracion_id')->nullable()->constrained('taxonomies');
            $table->foreignId('jerarquia_id')->nullable()->constrained('taxonomies');
            $table->foreignId('grupo_farmacologico_id')->nullable()->constrained('taxonomies');
            $table->foreignId('forma_farmaceutica_id')->nullable()->constrained('taxonomies');
            $table->foreignId('dosis_adulto_id')->nullable()->constrained('taxonomies');
            $table->foreignId('dosis_nino_id')->nullable()->constrained('taxonomies');
            $table->foreignId('recomendacion_de_administracion_id')->nullable()->constrained('taxonomies');
            $table->foreignId('advertencias_id')->nullable()->constrained('taxonomies');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('glossaries');
    }
};
