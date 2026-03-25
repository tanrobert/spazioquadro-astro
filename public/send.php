<?php

// Destinatario
$sendTo  = 'roberto.tanasi@gmail.com';
$subject = 'Nuova richiesta partnership - Spazioquadro';

// Honeypot: se il campo nascosto è compilato, è un bot
if (!empty($_POST['website'])) {
    http_response_code(200); // non rivelare che è stato bloccato
    echo json_encode(['type' => 'success']);
    exit;
}

// Campi obbligatori
$required = ['azienda', 'referente', 'email'];
foreach ($required as $field) {
    if (empty(trim($_POST[$field] ?? ''))) {
        http_response_code(400);
        echo json_encode(['type' => 'error', 'message' => 'Compila tutti i campi obbligatori.']);
        exit;
    }
}

// Sanitizzazione
$azienda  = htmlspecialchars(trim($_POST['azienda']));
$referente = htmlspecialchars(trim($_POST['referente']));
$telefono  = htmlspecialchars(trim($_POST['telefono'] ?? ''));
$email     = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$zona      = htmlspecialchars(trim($_POST['zona'] ?? ''));
$tipo      = htmlspecialchars(trim($_POST['tipo'] ?? ''));
$note      = htmlspecialchars(trim($_POST['note'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['type' => 'error', 'message' => 'Indirizzo email non valido.']);
    exit;
}

// Corpo email
$body = "Nuova richiesta di partnership da spazioquadro.it\n";
$body .= str_repeat('-', 40) . "\n\n";
$body .= "Azienda:        $azienda\n";
$body .= "Referente:      $referente\n";
$body .= "Email:          $email\n";
$body .= "Telefono:       $telefono\n";
$body .= "Zona:           $zona\n";
$body .= "Tipo attività:  $tipo\n";
$body .= "Note:\n$note\n";

$headers = implode("\r\n", [
    'Content-Type: text/plain; charset=UTF-8',
    'From: Spazioquadro Web <info@spazioquadro.net>',
    "Reply-To: $referente <$email>",
]);

$sent = mail($sendTo, $subject, $body, $headers);

if ($sent) {
    echo json_encode(['type' => 'success', 'message' => 'Richiesta inviata. Ti contatteremo presto.']);
} else {
    http_response_code(500);
    echo json_encode(['type' => 'error', 'message' => 'Errore nell\'invio. Riprova più tardi.']);
}
