<?php
session_start();

// 1. Limpa a memória
$_SESSION = array();

// 2. Apaga o cookie do navegador (força bruta)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destrói no servidor
session_destroy();

// 4. Destrói o arquivo físico da sessão no servidor (AQUI ESTÁ O SEGREDO)
if (ini_get("session.save_handler") == "files") {
    $session_file = session_save_path() . '/sess_' . session_id();
    if (file_exists($session_file)) {
        unlink($session_file); // Apaga o arquivo na força bruta
    }
}

// 5. Volta pro login
header("Location: login.php");
exit();
?>