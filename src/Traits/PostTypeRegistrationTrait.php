<?php

namespace Dashifen\WPHandler\Traits;

trait PostTypeRegistrationTrait
{
  /**
   * getPostTypeLabels
   *
   * Based on the $labels array constructed via the Post Type generator at
   * https://generatewp.com/post-type, this method returns an array of post
   * type labels based on the singular and plural parameters.
   *
   * @param string $singular
   * @param string $plural
   * @param string $textDomain
   *
   * @return array
   */
  protected function getPostTypeLabels(string $singular, string $plural, string $textDomain = 'default'): array
  {
    // rather than add more parameters to this method, we're going to provide
    // a filter for the "featured image" label.  this is because the default
    // tends to be acceptable, but in a case when it's not, then we can use
    // this filter to alter it.
    
    $thumbnailLabel = apply_filters(
      'post-type-registration-thumbnail-label',
      'featured image',
      $singular
    );
    
    return [
      'name'                     => _x($plural, $singular . ' General Name', $textDomain),
      'singular_name'            => _x($singular, $singular . ' Singular Name', $textDomain),
      'menu_name'                => __($plural, $textDomain),
      'name_admin_bar'           => __($singular, $textDomain),
      'archives'                 => __($singular . ' Archives', $textDomain),
      'attributes'               => __($singular . ' Attributes', $textDomain),
      'parent_item_colon'        => __('Parent ' . $singular . ':', $textDomain),
      'all_items'                => __('All ' . $plural, $textDomain),
      'add_new_item'             => __('Add New ' . $singular, $textDomain),
      'add_new'                  => __('Add New', $textDomain),
      'new_item'                 => __('New ' . $singular, $textDomain),
      'edit_item'                => __('Edit ' . $singular, $textDomain),
      'update_item'              => __('Update ' . $singular, $textDomain),
      'view_item'                => __('View ' . $singular, $textDomain),
      'view_items'               => __('View ' . $plural, $textDomain),
      'search_items'             => __('Search ' . $singular, $textDomain),
      'not_found'                => __('Not found', $textDomain),
      'not_found_in_trash'       => __('Not found in Trash', $textDomain),
      'featured_image'           => __(ucwords($thumbnailLabel), $textDomain),
      'set_featured_image'       => __('Set ' . $thumbnailLabel, $textDomain),
      'remove_featured_image'    => __('Remove ' . $thumbnailLabel, $textDomain),
      'use_featured_image'       => __('Use as ' . $thumbnailLabel, $textDomain),
      'insert_into_item'         => __('Add to ' . $singular, $textDomain),
      'uploaded_to_this_item'    => __('Uploaded to this ' . $singular, $textDomain),
      'items_list'               => __($plural . ' list', $textDomain),
      'items_list_navigation'    => __($plural . ' list navigation', $textDomain),
      'filter_items_list'        => __('Filter ' . $plural . ' list', $textDomain),
      'item_published'           => __($singular . ' published.', $textDomain),
      'item_published_privately' => __($singular . ' published privately.', $textDomain),
      'item_reverted_to_draft'   => __($singular . ' reverted to draft.'),
      'item_scheduled'           => __($singular . ' scheduled.', $textDomain),
      'item_updated'             => __($singular . ' updated.', $textDomain),
      'item_link'                => __($singular . ' Link', $textDomain),
      'item_link_description'    => __('A link to a ' . $singular . '.', $textDomain),
    ];
  }
}
