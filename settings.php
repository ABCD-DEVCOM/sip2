<?php
// Prevent direct URL access to this specific view file
if (!class_exists('PluginBridge')) {
    header("HTTP/1.1 403 Forbidden");
    die("Direct access forbidden.");
}

global $arrHttp, $msgstr, $lang, $abcdPath;

// Load current configuration
$config_file = __DIR__ . '/config.json';
$current_config = [
    'ip_sip_server' => '127.0.0.1',
    'port_sip_server' => 5060,
    'port_sip_antena' => 5061
];

if (file_exists($config_file)) {
    $parsed_json = json_decode(file_get_contents($config_file), true);
    // Only merge if parsing returned a valid array
    if (is_array($parsed_json)) {
        $current_config = array_merge($current_config, $parsed_json);
    }
}

include($abcdPath . "/common/header.php");
?>

<body>
    <?php include($abcdPath . "/common/institutional_info.php"); ?>
    <div class="sectionInfo">
        <div class="breadcrumb">
            <?php echo ($msgstr['sip2_manager'] ?? 'SIP2 Manager') . ' > ' . ($msgstr['sip2_settings'] ?? 'Settings'); ?>
        </div>
        <div class="actions">
            <?php
            $backtoscript = "index.php?action=form";
            include "../../../central/common/inc_back.php"
            ?>
            <a href="javascript:document.getElementById('settingsForm').submit();" class="button_browse">
                <i class="far fa-save"></i>&nbsp;<?php echo $msgstr['sip2_save'] ?? 'Save'; ?>
            </a>
        </div>
        <div class="spacer">&#160;</div>
    </div>

    <?php include "../../../central/common/inc_div-helper.php"; ?>

    <?php if (isset($_GET['success'])): ?>
        <div style="text-align: center; color: green; font-weight: bold; padding: 10px;">
            <?php echo $msgstr['sip2_settings_saved'] ?? 'Settings saved successfully!'; ?>
        </div>
    <?php endif; ?>

    <div style="padding: 20px; display: flex; justify-content: center;">
        <form id="settingsForm" action="index.php" method="POST" style="border: 1px solid #ccc; padding: 20px; background-color: #f9f9f9; border-radius: 5px; width: 600px;">
            <input type="hidden" name="action" value="save_settings">

            <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #0984e3; padding-bottom: 5px;"><i class="fas fa-network-wired"></i> <?php echo $msgstr['sip2_server_config'] ?? 'Network Configuration'; ?></h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                <div>
                    <label for="ip_sip_server" style="display: block; font-weight: bold;"><?php echo $msgstr['sip2_ip_address'] ?? 'SIP Server IP Address:'; ?></label>
                    <input type="text" id="ip_sip_server" name="ip_sip_server" value="<?php echo htmlspecialchars($current_config['ip_sip_server'] ?? '127.0.0.1'); ?>" style="width: 100%; padding: 5px;">
                </div>
                <div>
                    <label for="port_sip_server" style="display: block; font-weight: bold;"><?php echo $msgstr['sip2_sc_port'] ?? 'Self-Checkout Port:'; ?></label>
                    <input type="number" id="port_sip_server" name="port_sip_server" value="<?php echo htmlspecialchars($current_config['port_sip_server'] ?? 5060); ?>" style="width: 100%; padding: 5px;">
                </div>
                <div>
                    <label for="port_sip_antena" style="display: block; font-weight: bold;"><?php echo $msgstr['sip2_rfid_port'] ?? 'RFID Antenna Port:'; ?></label>
                    <input type="number" id="port_sip_antena" name="port_sip_antena" value="<?php echo htmlspecialchars($current_config['port_sip_antena'] ?? 5061); ?>" style="width: 100%; padding: 5px;">
                </div>
            </div>

            <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #0984e3; padding-bottom: 5px;"><i class="fas fa-building"></i> <?php echo $msgstr['sip2_lib_info'] ?? 'Library Information'; ?></h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                <div>
                    <label for="sip_library_name" style="display: block; font-weight: bold;"><?php echo $msgstr['sip2_lib_name'] ?? 'Library Name:'; ?></label>
                    <input type="text" id="sip_library_name" name="sip_library_name" value="<?php echo htmlspecialchars($current_config['sip_library_name'] ?? 'CDRJB'); ?>" style="width: 100%; padding: 5px;">
                </div>
                <div>
                    <label for="sip_terminal_location" style="display: block; font-weight: bold;"><?php echo $msgstr['sip2_terminal_loc'] ?? 'Terminal Location:'; ?></label>
                    <input type="text" id="sip_terminal_location" name="sip_terminal_location" value="<?php echo htmlspecialchars($current_config['sip_terminal_location'] ?? 'First Floor'); ?>" style="width: 100%; padding: 5px;">
                </div>
                <div>
                    <label for="BH" style="display: block; font-weight: bold;"><?php echo $msgstr['sip2_currency'] ?? 'Currency (3 chars):'; ?></label>
                    <input type="text" id="BH" name="BH" value="<?php echo htmlspecialchars($current_config['BH'] ?? 'USD'); ?>" maxlength="3" style="width: 100%; padding: 5px;">
                </div>
                <div>
                    <label for="message_25_enable" style="display: block; font-weight: bold;"><?php echo $msgstr['sip2_msg25'] ?? 'Enable Message 25:'; ?></label>
                    <select id="message_25_enable" name="message_25_enable" style="width: 100%; padding: 5px;">
                        <option value="Y" <?php echo ($current_config['message_25_enable'] ?? 'Y') === 'Y' ? 'selected' : ''; ?>><?php echo $msgstr['sip2_yes'] ?? 'Yes'; ?></option>
                        <option value="N" <?php echo ($current_config['message_25_enable'] ?? 'Y') === 'N' ? 'selected' : ''; ?>><?php echo $msgstr['sip2_no'] ?? 'No'; ?></option>
                    </select>
                </div>
            </div>

            <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #0984e3; padding-bottom: 5px;"><i class="fas fa-database"></i> <?php echo $msgstr['sip2_db_mapping'] ?? 'Database Field Mapping'; ?></h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label for="campo_titulo" style="display: block; font-weight: bold;"><?php echo $msgstr['sip2_field_title'] ?? 'Title Field:'; ?></label>
                    <input type="text" id="campo_titulo" name="campo_titulo" value="<?php echo htmlspecialchars($current_config['campo_titulo'] ?? 'v245^a'); ?>" style="width: 100%; padding: 5px;">
                </div>
                <div>
                    <label for="campo_inventario" style="display: block; font-weight: bold;"><?php echo $msgstr['sip2_field_inv'] ?? 'Inventory Field:'; ?></label>
                    <input type="text" id="campo_inventario" name="campo_inventario" value="<?php echo htmlspecialchars($current_config['campo_inventario'] ?? 'v82^c'); ?>" style="width: 100%; padding: 5px;">
                </div>
                <div>
                    <label for="campo_ubicacion" style="display: block; font-weight: bold;"><?php echo $msgstr['sip2_field_loc'] ?? 'Location Field:'; ?></label>
                    <input type="text" id="campo_ubicacion" name="campo_ubicacion" value="<?php echo htmlspecialchars($current_config['campo_ubicacion'] ?? 'v900^o'); ?>" style="width: 100%; padding: 5px;">
                </div>
                <div>
                    <label for="campo_tipo_seguridad" style="display: block; font-weight: bold;"><?php echo $msgstr['sip2_field_sec'] ?? 'Security Type Field:'; ?></label>
                    <input type="text" id="campo_tipo_seguridad" name="campo_tipo_seguridad" value="<?php echo htmlspecialchars($current_config['campo_tipo_seguridad'] ?? 'v900^z'); ?>" style="width: 100%; padding: 5px;">
                </div>
            </div>
        </form>
    </div>

    <?php include($abcdPath . "/common/footer.php"); ?>
</body>

</html>