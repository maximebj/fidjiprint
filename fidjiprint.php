<?php

/*
 * Plugin Name:       FidjiPrint
 * Plugin URI:        
 * Description:       Les fonctionnalités spécifiques de FidjiPrint
 * Version:           1.0
 * Requires at least: 6.8
 * Requires PHP:      8.0
 * Author:            C'est toi
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
*/

# Vérification de sécurité pour empêcher l'accès direct au fichier
defined("ABSPATH") || exit;

# Fonction pour personnaliser la requête du bloc "Articles liés"
function capitaine_related_posts_query($query_vars, $block_instance)
{
  if ($block_instance->context["queryId"] !== 7) {
    return $query_vars;
  }

  $current_post_id = get_the_ID();
  $current_post_categories = wp_get_post_categories($current_post_id, ["fields" => "ids"]);

  $query_vars["post__not_in"] = [$current_post_id];
  $query_vars["cat"] = $current_post_categories;

  return $query_vars;
}
add_filter("query_loop_block_query_vars", "capitaine_related_posts_query", 10, 2);

# Permet d'afficher le titre court (champ ACF) d'un produit dans un bloc personnalisé
function fidjiprint_register_short_title()
{
  # Nom unique du bloc
  $block_name = 'fidjiprint/titre-court';

  # Déclaration du bloc
  register_block_type(
    $block_name,
    [
      'title'           => 'Titre court',
      'category'        => 'text',
      'icon'            => 'images-alt',
      'attributes'     => [
        'level'   => [
          'label'   => 'Niveau de titre',
          'type'    => 'string',
          'enum'    => ['h2', 'h3', 'h4', 'p'],
          'default' => 'h3',
        ],
      ],
      'render_callback' => function ($attributes) {
        $block_props = get_block_wrapper_attributes();

        $titre_court = get_field('titre_court') ?? get_the_title();
        if (empty($titre_court)) {
          $titre_court = "Titre du produit";
        }

        $link = get_permalink();
        if (empty($link) || $link === "_") {
          $link = "#";
        }

        return sprintf('<%2$s %4$s><a href="%1s">%3$s</a></%2$s>', $link, esc_attr($attributes['level']), esc_html($titre_court), wp_kses_data($block_props));
      },
      'supports'        => [
        'autoRegister' => true,
        'color' => [
          'text' => true,
          'background' => true,
        ],
        'typography' => [
          'fontSize' => true,
          'textAlign' => true,
        ],
      ],
    ]
  );

  # Feuille de style rattachée au bloc
  wp_enqueue_block_style(
    $block_name,
    [
      "handle" => "fidjiprint-titre-court",
      "src"    => plugin_dir_url(__FILE__) . "css/fidjiprint-titre-court.css",
      "path"   => plugin_dir_path(__FILE__) . "css/fidjiprint-titre-court.css",
      "ver"    => filemtime(plugin_dir_path(__FILE__) . "css/fidjiprint-titre-court.css")
    ]
  );
}
add_action('init', 'fidjiprint_register_short_title');
