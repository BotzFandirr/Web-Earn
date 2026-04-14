<?php

require_once __DIR__ . '/db.php';

function smtp_send_verification(string $toEmail, string $toName, string $subject, string $htmlBody): bool
{
    $smtp = app_config()['smtp'];

    $socket = stream_socket_client(
        'tcp://' . $smtp['host'] . ':' . $smtp['port'],
        $errno,
        $errstr,
        15,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        return false;
    }

    stream_set_timeout($socket, 15);

    $ok = smtp_expect($socket, [220])
        && smtp_command($socket, 'EHLO localhost', [250])
        && smtp_command($socket, 'STARTTLS', [220])
        && stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)
        && smtp_command($socket, 'EHLO localhost', [250])
        && smtp_command($socket, 'AUTH LOGIN', [334])
        && smtp_command($socket, base64_encode($smtp['username']), [334])
        && smtp_command($socket, base64_encode($smtp['password']), [235])
        && smtp_command($socket, 'MAIL FROM:<' . $smtp['from_email'] . '>', [250])
        && smtp_command($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251])
        && smtp_command($socket, 'DATA', [354]);

    if (!$ok) {
        fclose($socket);
        return false;
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . smtp_encode_header($smtp['from_name']) . ' <' . $smtp['from_email'] . '>',
        'To: ' . smtp_encode_header($toName) . ' <' . $toEmail . '>',
        'Subject: ' . smtp_encode_header($subject),
    ];

    $message = implode("\r\n", $headers) . "\r\n\r\n" . $htmlBody . "\r\n.";

    fwrite($socket, $message . "\r\n");

    $sent = smtp_expect($socket, [250]) && smtp_command($socket, 'QUIT', [221]);
    fclose($socket);

    return $sent;
}

function smtp_command($socket, string $command, array $expectedCodes): bool
{
    fwrite($socket, $command . "\r\n");
    return smtp_expect($socket, $expectedCodes);
}

function smtp_expect($socket, array $expectedCodes): bool
{
    while (($line = fgets($socket, 515)) !== false) {
        if (strlen($line) < 3 || !is_numeric(substr($line, 0, 3))) {
            continue;
        }

        $code = (int) substr($line, 0, 3);
        $isLastLine = isset($line[3]) && $line[3] === ' ';

        if ($isLastLine) {
            return in_array($code, $expectedCodes, true);
        }
    }

    return false;
}

function smtp_encode_header(string $value): string
{
    return '=?UTF-8?B?' . base64_encode($value) . '?=';
}
