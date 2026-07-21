<div style="font-family: Arial, Helvetica, sans-serif; max-width: 480px; margin: 0 auto; color: #1a2e20;">
    <h2 style="color: #2d7a4f; margin-bottom: 4px;">Recupera tu contraseña</h2>
    <p>Hola {{ $nombre }},</p>
    <p>Recibimos una solicitud para restablecer tu contraseña en el Portal Académico del {{ $colegioNombre }}.</p>
    <p style="margin: 24px 0;">
        <a href="{{ $enlace }}"
           style="background: #2d7a4f; color: #fff; padding: 12px 24px; border-radius: 30px; text-decoration: none; display: inline-block; font-weight: bold;">
            Restablecer contraseña
        </a>
    </p>
    <p>Este enlace vence en 30 minutos. Si no solicitaste este cambio, puedes ignorar este correo con tranquilidad.</p>
</div>
