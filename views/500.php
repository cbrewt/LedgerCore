<?php
$message = $errorMessage ?? 'Internal server error.';
?>
<?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>

