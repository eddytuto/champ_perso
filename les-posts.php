<?php
/*
Plugin Name: Les posts
Description: Affiche les articles.
Version: 1.0
Author: Eddy Martin
*/

// Fonction pour obtenir les données météorologiques d'une ville depuis OpenWeatherMap

function get_data_meteo($la_ville)
{
    $api_key = 'Entrez votre clé personnelle openweathermap';
    $api_key = "255153f717c045816ce3e37a01968bcd";
    $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($la_ville) . "&appid=$api_key&units=metric";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    // print_r ($data);

    if ($data && isset($data['main'])) {
        // print_r($data); 
        $temp_min = $data['main']['temp_min'];
        $temp_max = $data['main']['temp_max'];
        $humidity = $data['main']['humidity'];
        // retour d'un tableau de trois éléments
        return array($temp_min, $temp_max, $humidity);
    } else {
        return array('N/A', 'N/A',  'N/A'); // Retourner des valeurs par défaut si les données ne sont pas disponibles
    }
}


function custom_article_list_page()
{
    echo '<div class="wrap">';
    echo '<h2>Liste des destinations</h2>';

    // Get all published posts
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1, // affiche tous les articles
        'orderby'        => 'title',  // Tri par post_title
        'order'          => 'ASC',    // Ordre croissant (A à Z)
    );

    $posts = get_posts($args);

    // Afficher le nombre total de posts




    if ($posts) {
        $total_posts = count($posts); // détecte le nombre d'articles

        // require_once("processus/bdd_retirer_repetition.php");
        // récupère les villes avoisinantes
        require_once("data/ville.php");
        // print_r ($ville);
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Post Title  - (' . $total_posts . ' articles) </th>';
        echo '<th>Ville avoisinante</th>';
        echo '<th>Température minimale</th>';
        echo '<th>Température maximale</th>';
        echo '<th>Humidité</th>';
        echo '<th>Categories</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($posts as $post) {
            setup_postdata($post); // équivalent the_post()

            $post_title =  get_the_title($post);

            $data_meteo = get_data_meteo($ville[$post_title]); // Obtenir les données météorologiques
            // Utiliser les slug de champs personnalisé défini dans ACF
            update_field('temperature_minimum', $data_meteo[0],  $post->ID);
            update_field('temperature_maximum', $data_meteo[1],  $post->ID);
            update_field('humidite', $data_meteo[2], $post->ID);
            update_field('ville_avoisinante', $ville[$post_title], $post->ID);


            // Get post categories
            $categories = get_the_category($post);



            echo '<tr>';
            echo '<td>' . esc_html($post_title) . '</td>';
            echo '<td>' . $ville[esc_html($post_title)] . '</td>';
            echo '<td>' .  $data_meteo[0] . '</td>';
            echo '<td>' .  $data_meteo[1] . '</td>';
            echo '<td>' . $data_meteo[2]  . '</td>';
            echo '<td>';

            if ($categories) {
                $category_names = array();

                foreach ($categories as $category) {
                    $category_names[] = esc_html($category->name);
                }

                echo implode(', ', $category_names);
            }

            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No posts found.</p>';
    }

    echo '</div>';
    // Exemple de débogage
    error_log('Plugin les-posts'); // Vérifie si le plugin est en cours d'exécution
    error_log('Le nombre d\'article trouvés: ' . count($posts)); // Affiche le nombre d'articles récupérés
}

// Add custom dashboard page menu item
function custom_article_list_menu()
{
    add_menu_page('Custom Article List', 'Article List', 'manage_options', 'custom-article-list', 'custom_article_list_page');
}

// Hook functions into WordPress
add_action('admin_menu', 'custom_article_list_menu');
