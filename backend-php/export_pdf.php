<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

render_header('Générer PV');
?>
<div class="alert alert-info mt-4">
    <strong>Génération de PV (PDF)&nbsp;:</strong> Module à venir, implémentation prochaine.
</div>
<?php render_footer(); ?>