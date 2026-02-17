<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sesions', function (Blueprint $table) {
            $table->id();
            
            // Campos principales del formulario
            $table->string('titulo'); // Título*
            $table->string('presentador')->nullable(); // Presentador
            $table->date('fecha_inicio'); // Fecha de inicio
            $table->time('hora_inicio'); // Hora de inicio
            
            // Campos técnicos adicionales
            $table->string('cuenta')->default('Eventos (a836b)'); // Cuenta
            $table->string('duracion')->nullable(); // Duración
            $table->string('idioma')->default('Español (LatAm)'); // Idioma
            $table->string('glosario')->nullable(); // Glosario
            $table->string('transcripcion')->nullable(); // Transcripción
            
            // Campos de la parte inferior del formulario
            $table->string('acceder')->default('Abierto'); // Acceder
            $table->string('zoom_link')->nullable(); // Zoom
            $table->string('paquete_voces')->default('Voz femenina'); // Paquete de voces
            $table->string('etiqueta')->nullable(); // Etiqueta
            
            $table->timestamps(); // Created_at y Updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesions');
    }
};