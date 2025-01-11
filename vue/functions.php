<?php
function AfficheAlerte(?string $alerte) {
    if(!is_null($alerte) && !empty($alerte)) {
        return "<p><strong>{$alerte}</strong></p>";
    }
}
?>