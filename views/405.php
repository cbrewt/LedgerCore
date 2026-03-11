<?php
$message = $errorMessage ?? 'Method not allowed.';
?>
<?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>

