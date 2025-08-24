<?php


/**
 * Envío de formulario con PHPMailer + botón regresar
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cargamos Composer autoload para usar PHPMailer
require 'vendor/autoload.php';

// Validamos que los datos llegaron vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tomamos los datos del formulario enviados por POST
    // htmlspecialchars convierte caracteres especiales en entidades HTML, evitando scripts maliciosos
    $name    = htmlspecialchars($_POST['name'] ?? '');      // Si 'name' no viene, usamos cadena vacía
    $email   = htmlspecialchars($_POST['email'] ?? '');     // Igual para el correo
    $message = htmlspecialchars($_POST['message'] ?? '');   // Igual para el mensaje

    /*
    Explicación:

    $_POST['campo'] → obtiene el valor enviado por el formulario.
    ?? '' → si no existe el campo (por ejemplo, alguien modifica el HTML), devuelve cadena vacía.
    htmlspecialchars() → convierte caracteres peligrosos:
        <  se vuelve &lt;
        >  se vuelve &gt;
        &  se vuelve &amp;
        "  se vuelve &quot;
        '  se vuelve &#039;
    Esto evita que un usuario malicioso pueda inyectar código HTML o JavaScript (XSS).

    Ejemplo de protección:
    - Usuario pone: <script>alert('hack')</script>
    - Con htmlspecialchars → se envía como texto plano y nunca se ejecuta.
    */

    // Verificar que los campos no estén vacíos
    if (empty($name) || empty($email) || empty($message)) {
        exit('Todos los campos son requeridos. <br><a href="index.php">⬅ Regresar</a>');
    }

    // ==============================
    // Configuración SMTP
    // ==============================
    $mailHost     = 'smtp.gmail.com';
    $mailUsername = '';   // Usuario
    $mailPassword = '';         // Pass de aplicación
    $mailPort     = 587;

    $mail = new PHPMailer(true);

    try {
        // -------------------------------
        // Configuración del servidor SMTP
        // -------------------------------
        $mail->isSMTP();
        $mail->CharSet    = 'utf-8';
        $mail->Host       = $mailHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailUsername;
        $mail->Password   = $mailPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $mailPort;

        // -------------------------------
        // Remitente y destinatario
        // -------------------------------
        // Usamos nuestro correo como remitente para evitar spam
        $mail->setFrom($mailUsername, 'Formulario de contacto');

        // Destinatario (tú mismo)
        $mail->addAddress($mailUsername, 'Admin');

        // Reply-To: el correo del visitante
        // Así si respondes el correo, va directo al usuario
        $mail->addReplyTo($email, $name);

        // -------------------------------
        // Contenido del correo
        // -------------------------------
        $mail->isHTML(true); // Permitimos HTML
        $mail->Subject = 'Nuevo mensaje desde el formulario';

        // Cuerpo en HTML
        $mail->Body    = "
            <h3>Has recibido un nuevo mensaje:</h3>
            <p><strong>Nombre:</strong> {$name}</p>
            <p><strong>Correo:</strong> {$email}</p>
            <p><strong>Mensaje:</strong><br>{$message}</p>
        ";

        // Cuerpo en texto plano (para clientes que no soportan HTML)
        $mail->AltBody = "Nombre: {$name}\nCorreo: {$email}\nMensaje: {$message}";

        // -------------------------------
        // Enviar correo
        // -------------------------------
        $mail->send();

        echo '
        <div style="max-width:600px;margin:50px auto;text-align:center;font-family:sans-serif;">
            <h3>✅ Mensaje enviado con éxito</h3>
            <a href="index.php">⬅ Regresar</a>
        </div>';
    } catch (Exception $e) {
        echo '
        <div style="max-width:600px;margin:50px auto;text-align:center;font-family:sans-serif;">
            <h3>❌ Error al enviar el mensaje</h3>
            <p>' . $mail->ErrorInfo . '</p>
            <a href="index.php">⬅ Regresar</a>
        </div>';
    }
} else {
    // Si alguien intenta acceder directamente por GET
    echo 'Método no permitido.';
}
