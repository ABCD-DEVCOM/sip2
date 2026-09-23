<?php
// 1. Load ABCD Core first to initialize classes and global variables
$config_path = realpath(__DIR__ . '/../../../central/config.php');
if (file_exists($config_path)) {
    require_once $config_path;
}

// 2. Validate access and manage Session
if (!class_exists('PluginBridge')) {
    header("HTTP/1.1 403 Forbidden");
    die("Direct access forbidden.");
}

// Start session if necessary[cite: 8]
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Set default language fallback if not defined by the user session
if (!isset($_SESSION["lang"])) {
    $_SESSION["lang"] = "en";
}

global $msgstr, $langManager, $lang;

$bridge = PluginBridge::getInstance();
$dbPath = $bridge->get('db_path');
$abcdPath = $bridge->get('abcd_path', realpath(__DIR__ . '/../../../central'));
$pluginPath = realpath(__DIR__);
$current_lang = $_SESSION['lang'];
$lang = $current_lang; // Assure global compatibility for core templates

// Load Plugin Translations
if (isset($langManager)) {
    $plugin_msgs = $langManager->loadPluginTranslations($pluginPath, 'sip2', 'sip2.tab', $current_lang);
    $msgstr = array_merge($msgstr ?? [], $plugin_msgs);
}

// 3. Router (Basic MVC Pattern)
$action = $_REQUEST['action'] ?? 'form';

switch ($action) {
    case 'start_server':
    case 'stop_server':
        require_once __DIR__ . '/socket_server.php';
        break;

    case 'settings':
        require_once __DIR__ . '/settings.php';
        break;

    case 'save_settings':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_config = [
                'ip_sip_server' => $_POST['ip_sip_server'] ?? '127.0.0.1',
                'port_sip_server' => (int)($_POST['port_sip_server'] ?? 5060),
                'port_sip_antena' => (int)($_POST['port_sip_antena'] ?? 5061),
                'sip_library_name' => $_POST['sip_library_name'] ?? 'CDRJB',
                'sip_terminal_location' => $_POST['sip_terminal_location'] ?? 'First Floor',
                'BH' => $_POST['BH'] ?? 'USD',
                'campo_titulo' => $_POST['campo_titulo'] ?? 'v245^a',
                'campo_inventario' => $_POST['campo_inventario'] ?? 'v82^c',
                'campo_ubicacion' => $_POST['campo_ubicacion'] ?? 'v900^o',
                'campo_tipo_seguridad' => $_POST['campo_tipo_seguridad'] ?? 'v900^z',
                'message_25_enable' => $_POST['message_25_enable'] ?? 'Y'
            ];
            file_put_contents(__DIR__ . '/config.json', json_encode($new_config, JSON_PRETTY_PRINT));
            header("Location: index.php?action=settings&success=1");
            exit;
        }
        break;

    case 'form':
    default:
        require_once __DIR__ . '/form.php';
        break;
}
