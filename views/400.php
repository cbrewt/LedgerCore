<?php
$message = $errorMessage ?? 'Bad request.';
?>
<?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>

