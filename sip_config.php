<?php
// Prevent direct URL access
if (!class_exists('PluginBridge')) {
    header("HTTP/1.1 403 Forbidden");
    die("Direct access forbidden.");
}

global $cisis_path;

// Load dynamic configuration from config.json
$config_file = __DIR__ . '/config.json';
$dynamic_config = [];
if (file_exists($config_file)) {
    $parsed = json_decode(file_get_contents($config_file), true);
    if (is_array($parsed)) {
        $dynamic_config = $parsed;
    }
}

/**************** CONFIGURATION PARAMETERS FOR SIP SERVER ****************/

// SIP server IP and Ports
$ip_sip_server = $dynamic_config['ip_sip_server'] ?? "127.0.0.1";
$port_sip_server = $dynamic_config['port_sip_server'] ?? 5060;
$port_sip_antena = $dynamic_config['port_sip_antena'] ?? 5061;

// General Library Settings
$sip_library_id = "1";
$sip_library_name = $dynamic_config['sip_library_name'] ?? "CDRJB";
$sip_terminal_location = $dynamic_config['sip_terminal_location'] ?? "First Floor";
$BH = $dynamic_config['BH'] ?? "USD";
$message_25_enable = $dynamic_config['message_25_enable'] ?? "Y";

// Database Field Mapping
$campo_titulo = $dynamic_config['campo_titulo'] ?? "v245^a";
$campo_inventario = $dynamic_config['campo_inventario'] ?? "v82^c";
$campo_ubicacion = $dynamic_config['campo_ubicacion'] ?? "v900^o";
$campo_tipo_seguridad = $dynamic_config['campo_tipo_seguridad'] ?? "v900^z";

// Server Connection Rules
$max_clients = 4;
$enable_sip_log = "Y";
$checksum_CRC = "Y";
$maxretry = 3;
$sip_timeout_sc = '010';
$retries_trasaction = '003';
$protocol_version = '2.00';
$sip_supported_messages = "YYYNYYNNYNYYNNNN";
$sip_print_line = "040";
$sip_lenguaje = "001";
$sip_SC_renewal_policy = "N";

// Secure CISIS path provided by PluginBridge
$path_mx = $cisis_path;

/****************** AF Messages Configuration for each response *****************/
$AF_98 = "Status messages sent. Welcome to ACS.";
$AF_24 = "Patron Information";
$AF_12_ok = "Checkout successful.";
$AF_12_no = "Checkout failed.";
$AF_12_no_base = "Checkout failed, no information in database.";
$AF_12_no_multa = "The user has an active fine.";
$AF_12_no_user = "Error searching for user.";
$AF_12_no_user2 = "User not found in the database.";
$AF_12_no_user3 = "Membership date expired.";
$AF_12_no_item = "Item does not exist in the database.";
$AF_12_no_politica = "You cannot checkout items, please proceed to the circulation desk.";
$AF_12_no_renew = "This item is already checked out to you, please select renew.";
$AF_12_no_prestado = "This item is not available, please proceed to the circulation desk.";
$AF_12_sobrepaso = "You have reached the limit of items allowed for checkout.";
$AF_10 = "Item Checkin.";
$AF_10_no_busqueda = "Search error.";
$AF_10_no_base = "The item cannot be checked in, please proceed to the circulation desk.";
$AF_10_ok = "Item returned.";
$AF_10_ok_multa = "Item returned. A fine has been applied. Please proceed to the circulation desk for details.";
$AF_02_block = "This user is blocked.";
$AF_36_ok = "Thank you, have a good day.";
$AF_18_ok = "Item Information.";
$AF_26_ok = "User unblocked.";
$AF_26_no = "Procedure not available.";
$AF_26_no_base = "The user is not blocked.";

/****************** AG Messages Configuration for each response *****************/
$AG_12 = "Checkout transaction details.";
$AG_10 = "Checkin transaction details:";
$AG_18 = "Item Information.";
