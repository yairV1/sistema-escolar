<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef2ef; padding:24px 12px;">
  <tr>
    <td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px; background-color:#ffffff; border-radius:8px; overflow:hidden; font-family: Arial, Helvetica, sans-serif;">

        {{-- HEADER --}}
        <tr>
          <td style="background-color:#2d7a4f; padding:28px 32px; text-align:center;">
            @if (! empty($logoUrl))
              <img src="{{ $logoUrl }}" alt="{{ $colegioNombre }}" width="56" height="56" style="display:block; margin:0 auto 12px; border:0; border-radius:8px;">
            @endif
            <span style="display:block; font-family: Arial, Helvetica, sans-serif; font-size:20px; line-height:26px; font-weight:bold; color:#ffffff;">
              {{ $colegioNombre }}
            </span>
          </td>
        </tr>

        {{-- BODY --}}
        <tr>
          <td style="padding:32px 32px 8px 32px;">

            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="background-color:#e8f5ee; color:#2d7a4f; font-family: Arial, Helvetica, sans-serif; font-size:11px; font-weight:bold; letter-spacing:.06em; text-transform:uppercase; padding:6px 14px; border-radius:20px;">
                  {{ $tipoNotificacionLabel }}
                </td>
              </tr>
            </table>

            <h1 style="margin:18px 0 16px 0; font-family: Arial, Helvetica, sans-serif; font-size:24px; line-height:30px; color:#1a2e20; font-weight:bold;">
              {{ $titulo }}
            </h1>

            <p style="margin:0 0 12px 0; font-family: Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; color:#33403a; white-space:pre-line;">{{ $mensaje }}</p>

          </td>
        </tr>

        <tr>
          <td style="padding:8px 32px 0 32px;">
            <hr style="border:none; border-top:1px solid #e2e8e4; margin:16px 0 0 0;">
          </td>
        </tr>

        {{-- FOOTER --}}
        <tr>
          <td style="padding:20px 32px 28px 32px; font-family: Arial, Helvetica, sans-serif;">
            <p style="margin:0 0 8px 0; font-size:12px; line-height:18px; color:#6b7a70;">
              Este es un comunicado oficial del <strong>{{ $colegioNombre }}</strong>. No respondas a este correo.
            </p>
            @if (! empty($direccion) || ! empty($telefono) || ! empty($web))
              <p style="margin:0; font-size:12px; line-height:20px; color:#8a978e;">
                @if (! empty($direccion)) {{ $direccion }} @endif
                @if (! empty($telefono)) &nbsp;&middot;&nbsp; Tel. {{ $telefono }} @endif
                @if (! empty($web)) &nbsp;&middot;&nbsp; <a href="{{ $web }}" style="color:#2d7a4f; text-decoration:none;">{{ $web }}</a> @endif
              </p>
            @endif
          </td>
        </tr>

      </table>

      <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px;">
        <tr>
          <td style="padding:16px 32px; text-align:center; font-family: Arial, Helvetica, sans-serif; font-size:11px; color:#9aab9f;">
            &copy; {{ now()->year }} {{ $colegioNombre }}. Todos los derechos reservados.
          </td>
        </tr>
      </table>

    </td>
  </tr>
</table>
