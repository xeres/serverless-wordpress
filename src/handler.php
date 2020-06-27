<?php

$MIME_TYPES = array(
    'css'  => 'text/css',
    'js'   => 'application/javascript',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'ico'  => 'image/vnd.microsoft.icon',
    'svg'  => 'image/svg+xml',
);

// Amazon API Gateway does not support unencrypted (HTTP) endpoints.
$_SERVER['HTTPS'] = 'on';

// XXX: hard-coded
$_SERVER['DOCUMENT_ROOT'] = '/mnt/docroot';
chdir($_SERVER['DOCUMENT_ROOT']);

$file_path = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'];

if (substr($file_path, -1) == '/') {
    $file_path .= 'index.php';
}

if (file_exists($file_path)) {
    $pathinfo = pathinfo($file_path);

    if ($pathinfo['extension'] == 'php') {
        require($file_path);
    }
    else if (isset($MIME_TYPES[$pathinfo['extension']])) {
        # XXX: stackery/php-lambda-layer + HTTP API do not support binary contents
        # The following codes will raise 500 Internal Server Error.
        header('Content-Type: ' . $MIME_TYPES[$pathinfo['extension']]);
        readfile($file_path);
    }
    else {
        # XXX: I don't know what to do.
        http_response_code(406);
    }
}
else {
    http_response_code(404);
}
