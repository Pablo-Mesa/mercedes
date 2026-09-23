<?php
function render_icon($iconName, $class = "icon") {
    $filePath = __DIR__ . "/../views/components/icons/{$iconName}.svg";    
    if (file_exists($filePath)) {
        $svg = file_get_contents($filePath);
        return str_replace('<svg', "<svg class=\"{$class}\"", $svg);
    }
    return '';
}