<?php
// Prevent direct URL access to this specific view file
if (!class_exists('PluginBridge')) {
    header("HTTP/1.1 403 Forbidden");
    die("Direct access forbidden.");
}

global $arrHttp, $msgstr, $lang, $abcdPath;

// Load current configuration to check port status
$config_file = __DIR__ . '/config.json';
$current_config = [
    'ip_sip_server' => '127.0.0.1',
    'port_sip_server' => 5060
];

if (file_exists($config_file)) {
    $current_config = array_merge($current_config, json_decode(file_get_contents($config_file), true));
}

$host = $current_config['ip_sip_server'];
$port = $current_config['port_sip_server'];

// Ping the socket port to check if it is actively listening
$connection = @fsockopen($host, $port, $errno, $errstr, 1);
$is_online = is_resource($connection);
if ($is_online) {
    fclose($connection);
}

// Use the secure path provided by the bridge to load core ABCD templates
include($abcdPath . "/common/header.php");
?>

<body>
    <?php include($abcdPath . "/common/institutional_info.php"); ?>

    <div class="sectionInfo">
        <div class="breadcrumb">
            <?php echo $msgstr['sip2_manager'] ?? 'SIP2 Manager'; ?>
        </div>
        <div class="actions">
            <?php
            $backtoscript = "/central/settings/conf_abcd.php";
            include "../../../central/common/inc_back.php"
            ?>
            <a href="index.php?action=settings" class="button_browse">
                <i class="fas fa-cog"></i>
                <span><strong><?php echo $msgstr['sip2_settings'] ?? 'Settings'; ?></strong></span>
            </a>
        </div>
        <div class="spacer">&#160;</div>
    </div>

    <?php include "../../../central/common/inc_div-helper.php"; ?>

    <!-- Modal Transparente -->
    <div id="sipModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:9999; text-align:center;">
        <div style="background:#fff; margin:15% auto; padding:40px; width:400px; border-radius:10px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
            <i id="modalIcon" class="fas fa-circle-notch fa-spin" style="font-size: 4em; margin-bottom: 20px; color: #0984e3;"></i>
            <h2 id="modalTitle" style="margin-top:0; color: #333;"><?php echo $msgstr['sip2_processing'] ?? 'Processing...'; ?></h2>
            <p id="modalMsg" style="color:#666; font-size: 1.1em; margin-bottom: 25px;"><?php echo $msgstr['sip2_wait_msg'] ?? 'Please wait.'; ?></p>

            <button id="modalBtn" onclick="window.location.reload();" class="menuButton" style="display: none; margin: 0 auto; border: none; cursor:pointer;">
                <span><i class="fas fa-sync-alt" style="font-size: 1.5em; margin: 0 10px 0 -15px; color: #0984e3;"></i><strong><?php echo $msgstr['sip2_refresh_status'] ?? 'Refresh Status'; ?></strong></span>
            </button>
        </div>
    </div>

    <iframe name="background_process" style="display:none;"></iframe>

    <!-- Layout ABCD -->
    <div class="middle homepage">
        <div class="mainBox">
            <div class="boxContent">

                <div class="sectionTitle">
                    <h1>
                        <i class="fas fa-network-wired" style="color: #636e72;"></i> <?php echo $msgstr['sip2_title'] ?? 'SIP2 Protocol Manager'; ?>

                        <?php if ($is_online): ?>
                            <span style="background-color: #27ae60; color: white; font-size: 16px; padding: 6px 15px; border-radius: 25px; vertical-align: middle; margin-left: 20px;">
                                <i class="fas fa-check-circle"></i> <?php echo $msgstr['sip2_online'] ?? 'ONLINE'; ?>
                            </span>
                        <?php else: ?>
                            <span style="background-color: #d63031; color: white; font-size: 16px; padding: 6px 15px; border-radius: 25px; vertical-align: middle; margin-left: 20px;">
                                <i class="fas fa-times-circle"></i> <?php echo $msgstr['sip2_offline'] ?? 'OFFLINE'; ?>
                            </span>
                        <?php endif; ?>
                    </h1>
                </div>

                <div style="padding: 20px 40px; font-size: 1.1em; color: #2d3436; border-bottom: 1px solid #dfe6e9; margin-bottom: 30px;">
                    <p><?php echo $msgstr['sip2_description'] ?? 'Manages communication with SC and RFID.'; ?></p>
                    <p><strong><?php echo $msgstr['sip2_configured_host'] ?? 'Configured TCP Host:'; ?></strong> <code><?php echo htmlspecialchars($host . ':' . $port); ?></code></p>
                </div>

                <div class="sectionButtons" style="display: flex; gap: 20px; padding-left: 40px;">

                    <form action="index.php" target="background_process" method="post" onsubmit="processAction('<?php echo addslashes($msgstr['sip2_starting_title'] ?? 'Starting'); ?>', '<?php echo addslashes($msgstr['sip2_starting_msg'] ?? 'Wait'); ?>', 'fa-play-circle', '#27ae60');">
                        <input type="hidden" name="action" value="start_server" />
                        <button type="submit" class="menuButton" style="border: none; background: transparent; cursor: pointer; <?php echo $is_online ? 'opacity: 0.4; pointer-events: none;' : ''; ?>">
                            <span><i class="fas fa-play" style="font-size: 2em; margin: 0 10px 0 -30px; color: #27ae60;"></i><strong><?php echo $msgstr['sip2_start_server'] ?? 'Start'; ?></strong></span>
                        </button>
                    </form>

                    <form action="index.php" target="background_process" method="post" onsubmit="processAction('<?php echo addslashes($msgstr['sip2_stopping_title'] ?? 'Stopping'); ?>', '<?php echo addslashes($msgstr['sip2_stopping_msg'] ?? 'Releasing port'); ?>', 'fa-stop-circle', '#d63031');">
                        <input type="hidden" name="action" value="stop_server" />
                        <button type="submit" class="menuButton" style="border: none; background: transparent; cursor: pointer; <?php echo !$is_online ? 'opacity: 0.4; pointer-events: none;' : ''; ?>">
                            <span><i class="fas fa-stop" style="font-size: 2em; margin: 0 10px 0 -30px; color: #d63031;"></i><strong><?php echo $msgstr['sip2_stop_server'] ?? 'Stop'; ?></strong></span>
                        </button>
                    </form>

                </div>
                <div class="spacer">&#160;</div>
            </div>
        </div>
    </div>

    <script>
        function processAction(title, msg, iconClass, color) {
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalMsg').innerText = msg;

            var icon = document.getElementById('modalIcon');
            icon.className = 'fas fa-circle-notch fa-spin';
            icon.style.color = color;

            document.getElementById('modalBtn').style.display = 'none';
            document.getElementById('sipModal').style.display = 'block';

            setTimeout(function() {
                icon.className = 'fas ' + iconClass;
                document.getElementById('modalTitle').innerText = "<?php echo addslashes($msgstr['sip2_op_completed'] ?? 'Operation Completed'); ?>";
                document.getElementById('modalMsg').innerText = "<?php echo addslashes($msgstr['sip2_op_registered'] ?? 'Action registered.'); ?>";
                document.getElementById('modalBtn').style.display = 'block';
            }, 2000);
        }
    </script>
    <?php include($abcdPath . "/common/footer.php"); ?>
</body>

</html>