<?php

// .clinerules-dev/*.md を .clinerules に一つのファイルにする
$dir = '.clinerules-dev';
$files = glob($dir.'/*.md');
$clinerules = '';
foreach ($files as $file) {
    $clinerules .= file_get_contents($file);
}
file_put_contents('.clinerules', $clinerules);
