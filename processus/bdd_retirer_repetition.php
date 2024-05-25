<?php
// Charger le fichier WordPress

require_once(ABSPATH . 'wp-load.php');

// Récupérer les articles répétés avec le même titre
global $wpdb;

// Requête SQL pour identifier les articles répétés
$query = "
    SELECT post_title, GROUP_CONCAT(ID SEPARATOR ',') AS post_ids
    FROM {$wpdb->posts}
    WHERE post_type = 'post' 
    AND post_status = 'publish'
    GROUP BY post_title
    HAVING COUNT(*) > 1
";

// Exécuter la requête SQL pour récupérer les articles répétés
$results = $wpdb->get_results($query);

// Parcourir les résultats pour traiter les articles répétés
foreach ($results as $result) {
    $post_ids = explode(',', $result->post_ids);

    // Garder le premier article et fusionner les catégories des autres articles
    $keep_post_id = $post_ids[0]; // ID de l'article à conserver

    for ($i = 1; $i < count($post_ids); $i++) {
        $delete_post_id = $post_ids[$i]; // ID de l'article à supprimer

        // Vérifier les catégories de l'article à supprimer
        $categories_to_add = wp_get_post_categories($delete_post_id);

        // Ajouter ces catégories à l'article à conserver
        wp_set_post_categories($keep_post_id, $categories_to_add, true);

        // Supprimer l'article répété
        wp_delete_post($delete_post_id, true);
    }
}

echo 'Processus de fusion des articles répétés terminé.';
?>