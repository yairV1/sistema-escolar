<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_contenido', function (Blueprint $table) {
            $table->id('id_contenido');
            $table->string('clave', 100)->unique();
            $table->text('valor')->nullable();
            $table->timestamp('actualizado_en')->useCurrent();
        });

        $ahora = now();
        $defaults = [
            'hero_badge' => '🌿 Desde 1985 · Bogotá, Colombia',
            'hero_titulo' => 'Educamos con pasión y propósito',
            'hero_descripcion' => 'En el Colegio San Cristóbal formamos personas íntegras, creativas y comprometidas con su entorno. Un ambiente seguro, moderno y lleno de oportunidades.',
            'hero_stat1_valor' => '1,200+',
            'hero_stat1_label' => 'Estudiantes',
            'hero_stat2_valor' => '85+',
            'hero_stat2_label' => 'Docentes',
            'hero_stat3_valor' => '38',
            'hero_stat3_label' => 'Años de historia',
            'nosotros_texto1' => 'El Colegio San Cristóbal nació en 1985 con la misión de ofrecer una educación de calidad accesible para toda la comunidad. Hoy somos una institución reconocida por nuestra excelencia académica, valores sólidos y enfoque en el desarrollo humano integral.',
            'nosotros_texto2' => 'Contamos con instalaciones modernas, laboratorios equipados, biblioteca especializada y espacios deportivos de primer nivel para garantizar el mejor ambiente de aprendizaje.',
            'nosotros_mision' => 'Formar ciudadanos íntegros con pensamiento crítico, valores éticos y habilidades para transformar su entorno positivamente.',
            'nosotros_vision' => 'Ser en 2030 la institución educativa líder en innovación pedagógica y formación humana de Bogotá.',
            'contacto_direccion' => 'Calle 45 #12-34, Barrio San Luis, Bogotá D.C., Colombia',
            'contacto_telefono' => '(601) 234-5678',
            'contacto_celular' => '310 000 0000',
            'contacto_correo' => 'info@sancristobal.edu.co',
            'contacto_correo_admisiones' => 'admisiones@sancristobal.edu.co',
            'contacto_horario' => 'Lun – Vie: 6:30 am – 4:00 pm, Sáb: 8:00 am – 12:00 pm',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_youtube' => '',
            'social_whatsapp' => '',
        ];

        $filas = collect($defaults)->map(fn ($valor, $clave) => [
            'clave' => $clave,
            'valor' => $valor,
            'actualizado_en' => $ahora,
        ])->values()->all();

        \Illuminate\Support\Facades\DB::table('landing_contenido')->insert($filas);
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_contenido');
    }
};
