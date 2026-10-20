<!DOCTYPE html>
<html lang="es">
<body style="margin:0;padding:24px;background:#f3f6f6;font-family:Arial,Helvetica,sans-serif;color:#14263a;">
    <div style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #d2dbdc;border-radius:8px;padding:24px;">
        <p style="margin:0 0 16px;font-size:20px;font-weight:bold;color:#0e5f63;">Kendex</p>
        <p style="margin:0 0 16px;font-size:16px;line-height:1.5;">{{ $body }}</p>
        @if ($link)
            <p style="margin:24px 0 0;"><a href="{{ $link }}" style="display:inline-block;background:#0e5f63;color:#ffffff;text-decoration:none;padding:10px 18px;border-radius:6px;font-weight:bold;">Abrir en Kendex</a></p>
        @endif
        <p style="margin:24px 0 0;font-size:12px;color:#4b5d66;">Recibes este correo porque tienes una cuenta en Kendex.</p>
    </div>
</body>
</html>
