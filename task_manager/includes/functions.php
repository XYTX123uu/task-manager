<?php
function formatDate($date) {
    if (empty($date)) return '';
    return date('Y年m月d日', strtotime($date));
}
?>