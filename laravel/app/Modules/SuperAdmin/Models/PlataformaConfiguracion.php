<?php

namespace App\Modules\SuperAdmin\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registro único (id_configuracion = 1), mismo patrón que
 * ColegioConfiguracion::singleton(). No incluye secretos de integración
 * (WhatsApp Cloud API vive en config('services.whatsapp_cloud'), ver
 * migración create_plataforma_configuracion_table).
 */
class PlataformaConfiguracion extends Model
{
    protected $table = 'plataforma_configuracion';

    protected $primaryKey = 'id_configuracion';

    protected $fillable = [
        'limite_usuarios_default',
        'limite_instituciones',
        'plantilla_comunicado_default',
        'soporte_email_contacto',
    ];

    public static function singleton(): self
    {
        return static::query()->findOrFail(1);
    }
}
