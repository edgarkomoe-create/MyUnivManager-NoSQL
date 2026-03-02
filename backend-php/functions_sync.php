<?php
// functions_sync.php

/**
 * Synchronise un document MySQL vers MongoDB
 * @param string $table Nom de la table/collection
 * @param int $id ID de la ligne MySQL
 */
function syncToMongoDB($table, $id, $pdo, $mongoDB) {
    $collection = $mongoDB->selectCollection($table);

    // 1. Extraction (Source de vérité : MySQL)
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();

    if ($data) {
        // 2. Transformation (Mapping ID conforme au guide page 11)
        $data['mysql_id'] = (int)$data['id'];
        unset($data['id']); // On laisse MongoDB gérer son propre _id interne

        // 3. Chargement (Upsert : mise à jour ou insertion)
        try {
            $collection->updateOne(
                ['mysql_id' => $data['mysql_id']],
                ['$set' => $data],
                ['upsert' => true]
            );
            return true;
        } catch (Exception $e) {
            error_log("Erreur de synchro MongoDB : " . $e->getMessage());
            return false;
        }
    }
}