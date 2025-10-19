<?php
session_start();

// --- Preguntas del quiz (puedes cambiar las preguntas/respuesas)
$questions = [
    1 => [
        'q' => '¿Dónde nació Joan Sebastián?',
        'options' => ['Aguascalientes','Zitácuaro, Michoacán','Baja California','Guadalajara, Jalisco'],
        'answer' => 2
    ],
    2 => [
        'q' => '¿Cuál era el nombre real de Joan Sebastián?',
        'options' => ['José Manuel Figueroa','Benito Juárez','José Manuel Figueroa González','Juan Carlos Sebastián'],
        'answer' => 3
    ],
    3 => [
        'q' => 'Joan Sebastián fue conocido principalmente como:',
        'options' => ['Pintor','Cantautor y compositor','Actor de cine','Poeta'],
        'answer' => 2
    ],
    4 => [
        'q' => 'Una de sus canciones más famosas es:',
        'options' => ['Secreto de Amor','Burbujas de amor','La Incondicional','Amor Eterno'],
        'answer' => 1
    ],
    5 => [
        'q' => 'Joan Sebastián ganó reconocimientos en:',
        'options' => ['Grammy y Latin Grammy','Oscars','Nobels','Emmys'],
        'answer' => 1
    ],
];

// --- Manejo de envío del quiz
$quizResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_quiz') {
    $score = 0;
    $answers = [];
    foreach ($questions as $id => $q) {
        // tomar la respuesta (si no existe se considera 0)
        $u = isset($_POST['q'.$id]) ? intval($_POST['q'.$id]) : 0;
        $answers[$id] = $u;
        if ($u === $q['answer']) $score++;
    }
    $quizResult = [
        'score' => $score,
        'total' => count($questions),
        'answers' => $answers
    ];
    // Guardar resultado en sesión para mostrar luego si quieres
    $_SESSION['last_quiz'] = $quizResult;
}

// --- Manejo del formulario de contacto
$contactMessage = '';
$contactErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_contact') {
    // sanitizar
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));

    if ($name === '') $contactErrors[] = 'El nombre es obligatorio.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $contactErrors[] = 'Email inválido.';
    if ($message === '') $contactErrors[] = 'El mensaje no puede estar vacío.';

    if (empty($contactErrors)) {
        $time = date('Y-m-d H:i:s');
        $entry = "-----\nFecha: $time\nNombre: $name\nEmail: $email\nMensaje:\n$message\n\n";
        // Guardar en archivo contacts.txt (asegúrate de permisos de escritura)
        $saved = file_put_contents(__DIR__ . '/contacts.txt', $entry, FILE_APPEND | LOCK_EX);
        if ($saved === false) {
            $contactMessage = 'Error al guardar el mensaje. Revisa permisos de carpeta.';
        } else {
            $contactMessage = 'Gracias, tu mensaje ha sido enviado correctamente.';

            // Intento de enviar correo (opcional). En servidores locales esto puede fallar.
            $to = 'tu-correo@ejemplo.com'; // <- cambia por tu correo real si deseas recibir mails
            $subject = "Contacto desde sitio Joan Sebastián: $name";
            $body = "Nombre: $name\nEmail: $email\n\nMensaje:\n$message\n";
            $headers = "From: $email\r\nReply-To: $email\r\n";
            // @ para evitar warnings si mail no está configurado
            @mail($to, $subject, $body, $headers);
        }

        // limpiar POST para evitar reenvío
        $_POST = [];
    }
}

// --- Navegación simple por ?p=home|quiz|contact
$page = isset($_GET['p']) ? $_GET['p'] : 'home';

function esc($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Joan Sebastián - Sitio</title>
<style>
    body{font-family: Arial, Helvetica, sans-serif; margin:0; padding:0; background:#f6f6f6; color:#222;}
    header{background:#3b3b3b;color:#fff;padding:16px 24px}
    nav a{color:#fff;margin-right:12px;text-decoration:none}
    .container{max-width:900px;margin:24px auto;padding:16px;background:#fff;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,.08)}
    h1{margin-top:0}
    .bio img{max-width:200px; float:right; margin-left:12px; border-radius:8px}
    .question{margin-bottom:14px;padding:8px;border:1px solid #eee;border-radius:6px}
    .btn{display:inline-block;padding:8px 12px;background:#3b3b3b;color:#fff;border-radius:6px;text-decoration:none}
    .success{background:#e6ffed;border-left:4px solid #2ecc71;padding:10px;margin-bottom:10px}
    .error{background:#ffe6e6;border-left:4px solid #e74c3c;padding:10px;margin-bottom:10px}
    footer{text-align:center;padding:12px;color:#666;font-size:14px}
    label{display:block;margin-bottom:6px}
    input[type="text"],input[type="email"],textarea{width:100%;padding:8px;border:1px solid #ddd;border-radius:4px}
</style>
</head>
<body>
<header>
    <nav>
        <a href="?p=home">Biografía</a>
        <a href="?p=quiz">Juego (Quiz)</a>
        <a href="?p=contact">Contacto</a>
    </nav>
</header>

<div class="container">
<?php if ($page === 'home'): ?>
    <h1>Joan Sebastián</h1>
    <div class="bio">
        <img src="https://upload.wikimedia.org/wikipedia/commons/6/66/Joan_Sebastian.jpg" alt="Joan Sebastián" onerror="this.style.display='none'">
        <p><strong>Joan Sebastián</strong> fue un cantautor mexicano muy reconocido por sus contribuciones a la música regional mexicana. Nació en Zitácuaro, Michoacán, y durante su carrera compuso decenas de éxitos, además de producir y grabar numerosos álbumes. Fue conocido por su estilo romántico y ranchero, y obtuvo premios a lo largo de su trayectoria, incluidos Grammys y Latin Grammys.</p>
        <p>En este sitio puedes jugar un pequeño quiz sobre su vida y carrera, o contactarme si quieres más información.</p>
        <p><em>Nota: la biografía aquí es resumida y para ejemplo didáctico.</em></p>
    </div>

<?php elseif ($page === 'quiz'): ?>

    <h1>Juego: Quiz sobre Joan Sebastián</h1>

    <?php if ($quizResult === null): ?>
        <p>Contesta las 5 preguntas. Al enviar recibirás tu puntaje y verás cuáles respuestas fueron correctas.</p>
        <form method="post" action="?p=quiz">
            <?php foreach ($questions as $id => $q): ?>
                <div class="question">
                    <strong>Pregunta <?= $id ?>:</strong>
                    <p><?= esc($q['q']) ?></p>
                    <?php foreach ($q['options'] as $optIndex => $optText): ?>
                        <?php $optValue = $optIndex + 1; ?>
                        <label>
                            <input type="radio" name="q<?= $id ?>" value="<?= $optValue ?>"> <?= esc($optText) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <input type="hidden" name="action" value="submit_quiz">
            <button class="btn" type="submit">Enviar respuestas</button>
        </form>

    <?php else: // mostrar resultado ?>
        <h2>Resultado</h2>
        <div class="success">
            Obtuviste <strong><?= esc($quizResult['score']) ?></strong> de <?= esc($quizResult['total']) ?>.
        </div>

        <h3>Detalles</h3>
        <?php foreach ($questions as $id => $q): 
            $user = isset($quizResult['answers'][$id]) ? $quizResult['answers'][$id] : 0;
            $correct = $q['answer'];
            $isCorrect = ($user === $correct);
        ?>
            <div class="question" style="border-color: <?= $isCorrect ? '#c8e6c9' : '#ffcdd2' ?>;">
                <strong>Pregunta <?= $id ?>:</strong> <?= esc($q['q']) ?><br>
                Tu respuesta:
                <?php if ($user === 0): ?>
                    <em>No respondiste</em>
                <?php else: ?>
                    <?= esc($q['options'][$user-1]) ?>
                <?php endif; ?>
                <br>
                Respuesta correcta: <strong><?= esc($q['options'][$correct-1]) ?></strong>
                <br>
                <?= $isCorrect ? '<span style="color:green">Correcto ✅</span>' : '<span style="color:red">Incorrecto ✖</span>' ?>
            </div>
        <?php endforeach; ?>

        <p><a class="btn" href="?p=quiz">Jugar de nuevo</a> <a class="btn" href="?p=home">Volver a biografía</a></p>
    <?php endif; ?>

<?php elseif ($page === 'contact'): ?>

    <h1>Contacto</h1>

    <?php if (!empty($contactErrors)): ?>
        <div class="error">
            <strong>Por favor corrige los siguientes errores:</strong>
            <ul>
            <?php foreach ($contactErrors as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($contactMessage !== ''): ?>
        <div class="success"><?= esc($contactMessage) ?></div>
    <?php endif; ?>

    <form method="post" action="?p=contact">
        <label for="name">Nombre</label>
        <input id="name" type="text" name="name" value="<?= isset($_POST['name']) ? esc($_POST['name']) : '' ?>">

        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="<?= isset($_POST['email']) ? esc($_POST['email']) : '' ?>">

        <label for="message">Mensaje</label>
        <textarea id="message" name="message" rows="6"><?= isset($_POST['message']) ? esc($_POST['message']) : '' ?></textarea>

        <input type="hidden" name="action" value="send_contact">
        <button class="btn" type="submit">Enviar mensaje</button>
    </form>

<?php else: ?>

    <h1>Página no encontrada</h1>
    <p>La página solicitada no existe.</p>

<?php endif; ?>
</div>

<footer>
    &copy; <?= date('Y') ?> Sitio de ejemplo - Joan Sebastián (demo). Hecho en PHP.
</footer>
</body>
</html>
