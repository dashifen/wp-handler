<?php

namespace Dashifen\WPHandler\Traits;

trait TaxonomyRegistrationTrait
{
  /**
   * getTaxonomyLabels
   *
   * Based on the array produced by the Taxonomy generator located at
   * https://generatewp.com/taxonomy, this method returns an array of labels
   * for a taxonomy based on the parameters.
   *
   * @param string $singular
   * @param string $plural
   * @param string $textDomain
   *
   * @return array
   */
  protected function getTaxonomyLabels(string $singular, string $plural, string $textDomain = 'default'): array
  {
    return [
      'name'                       => _x($plural, $singular . ' General Name', $textDomain),
      'singular_name'              => _x($singular, $singular . ' Singular Name', $textDomain),
      'menu_name'                  => __($plural, $textDomain),
      'all_items'                  => __('All ' . $plural, $textDomain),
      'parent_item'                => __('Parent ' . $singular, $textDomain),
      'parent_item_colon'          => __('Parent ' . $singular . ':', $textDomain),
      'new_item_name'              => __('New ' . $singular . ' Name', $textDomain),
      'add_new_item'               => __('Add New ' . $singular, $textDomain),
      'edit_item'                  => __('Edit ' . $singular, $textDomain),
      'update_item'                => __('Update ' . $singular, $textDomain),
      'view_item'                  => __('View ' . $singular, $textDomain),
      'separate_items_with_commas' => __('Separate ' . $plural . ' with commas', $textDomain),
      'add_or_remove_items'        => __('Add or remove ' . $plural, $textDomain),
      'choose_from_most_used'      => __('Choose from the most used ' . $plural, $textDomain),
      'popular_items'              => __('Popular ' . $plural, $textDomain),
      'search_items'               => __('Search ' . $plural, $textDomain),
      'not_found'                  => __('Not Found', $textDomain),
      'no_terms'                   => __('No ' . $plural, $textDomain),
      'items_list'                 => __($plural . ' list', $textDomain),
      'items_list_navigation'      => __($plural . ' list navigation', $textDomain),
      'filter_by_item'             => __('Filter by ' . $singular, $textDomain),
      'back_to_items'              => __('Back to ' . $plural, $textDomain),
      'item_link'                  => __($singular . ' Link', $textDomain),
      'item_link_description'      => __('A link to a ' . $singular, $textDomain),
    ];
  }
}
