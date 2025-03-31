<?php
/**
 * Plugin Name: Anti Copy Protection
 * Description: Bloqueia copiadores como HTTrack, impede cópia de conteúdo via JavaScript e protege contra hotlinking.
 * Version: 1.2
 * Author: Damilys Araujo
 */

// Adiciona regras ao .htaccess
function acp_update_htaccess() {
    $htaccess_path = ABSPATH . '.htaccess';
    $rules = "\n# Anti Copy Protection\n";
    
    // Bloqueia copiadores como HTTrack, wget, curl, etc.
    $rules .= "<IfModule mod_rewrite.c>\n";
    $rules .= "RewriteEngine On\n";
    $rules .= "RewriteCond %{HTTP_USER_AGENT} ^.*(HTTrack|wget|curl|java|libwww|Python|scan|nmap|sqlmap|Nikto|fierce).* [NC]\n";
    $rules .= "RewriteRule .* - [F,L]\n";
    $rules .= "</IfModule>\n";

    // Bloqueia hotlinking de imagens
    $rules .= "<IfModule mod_rewrite.c>\n";
    $rules .= "RewriteCond %{HTTP_REFERER} !^$ [NC]\n";
    $rules .= "RewriteCond %{HTTP_REFERER} !^https://(www\.)?seudominio\.com [NC]\n";
    $rules .= "RewriteRule \.(jpg|jpeg|png|gif|bmp)$ - [F,NC]\n";
    $rules .= "</IfModule>\n";

    // Proteção contra bots maliciosos conhecidos
    $rules .= "SetEnvIfNoCase User-Agent \\b(BadBot|EvilScraper|FakeGoogleBot|SpamBot|Scrapy)\\b bad_bot\n";
    $rules .= "<Limit GET POST>\nOrder Allow,Deny\nAllow from all\nDeny from env=bad_bot\n</Limit>\n";

    if (is_writable($htaccess_path)) {
        file_put_contents($htaccess_path, $rules, FILE_APPEND);
    }
}
register_activation_hook(__FILE__, 'acp_update_htaccess');

// Adiciona o JavaScript para proteção
function acp_block_copy_js() {
    echo "<script>
    document.addEventListener('contextmenu', event => event.preventDefault());
    document.addEventListener('keydown', function(event) {
        if (event.ctrlKey && (event.key === 'u' || event.key === 'U' || event.key === 's' || event.key === 'S' || event.key === 'c' || event.key === 'C')) {
            event.preventDefault();
        }
    });
    document.addEventListener('selectstart', event => event.preventDefault());
    </script>";
}
add_action('wp_head', 'acp_block_copy_js');
