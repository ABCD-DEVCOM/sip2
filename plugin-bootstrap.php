<?php
if (!class_exists('PluginBridge')) {
    header("HTTP/1.1 403 Forbidden");
    die("Acesso direto proibido.");
}

$bridge = PluginBridge::getInstance();

abcd_add_hook('config_menu', function(string $menuHtml) use ($bridge): string {
    $menuHtml .= '<a href="/content/plugins/sip2/?action=form" class="menuButton moduleButton">';
    $menuHtml .= '<span><strong>Gerenciador SIP2</strong></span>';
    return $menuHtml .= '</a>';
});
?>