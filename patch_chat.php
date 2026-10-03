
<?php
$file = '/var/www/study-center-nias/resources/js/chat.js';
$content = file_get_contents($file);

$search = 'if (e.user_id !== this.currentUserId) {';
$replace = 'const exists = this.messages.some(m => m.id === e.id);
                        const hasOptimistic = this.messages.some(m => m._sending && m.body === e.body);
                        if (!exists && (e.user_id !== this.currentUserId || !hasOptimistic)) {';

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents($file, $content);
    echo "Patched chat.js\n";
} else {
    echo "Already patched or not found\n";
}
