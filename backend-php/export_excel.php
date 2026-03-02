<?php
require 'config.php';
session_start();

// simple placeholder pour action d'export en Excel
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

render_header('Export Excel');
?>
<div class="alert alert-info mt-4">
    <strong>Fonctionnalité d'export Excel&nbsp;:</strong> Ce module est en cours de développement.
    <br>Les fichiers seront bientôt générés automatiquement.
</div>
<?php render_footer(); ?>