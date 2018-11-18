<?php

/** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection PhpUndefinedConstantInspection */
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpUndefinedFieldInspection */
/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection SqlResolve */

namespace Engage\WordPress\Traits;

use WP_Post;

/**
 * Trait AutoExportCustomFieldGroupTrait
 *
 * Automatically exports ACF JSON data to a specified folder so that it can
 * be checked into git for safe-keeping.
 *
 * @package Engage\WordPress\Traits
 */
trait AutoExportCustomFieldGroupTrait {
	/**
	 * @var string
	 */
	protected $acfExportFolder;

	/**
	 * initializeCustomFieldGroupExport
	 *
	 * This method should be called from the initialize() method of a
	 * class extending the AbstractTheme object.
	 *
	 * @param string|null $folder
	 */
	protected function initializeCustomFieldGroupExport(string $folder = null) {
		if (function_exists("acf_add_options_page") && method_exists($this, "addAction")) {

			// as long as ACF and our AbstractTheme's addAction method exist,
			// we can continue.  first, we'll make sure we have a folder to use.
			// then, we make sure it exists.  if (or when) it does, then we
			// add an action that exports ACF field information when it's
			// saved to the database.

			$folder = $this->getExportFolder($folder);

			if ($this->findOrCreate($folder)) {
				/** @noinspection PhpUndefinedFunctionInspection */

				$this->acfExportFolder = trailingslashit($folder);
				$this->addAction("save_post", "exportFieldGroup", 100000000, 2);
			};
		}
	}

	/**
	 * getExportFolder
	 *
	 * Given the name of a folder (or null) returns the folder in which ACF
	 * exports shall be stored.
	 *
	 * @param string|null $folder
	 *
	 * @return string
	 */
	protected function getExportFolder(string $folder = null) {
		if (is_null($folder)) {

			// if the folder that's passed here was null, then we want to
			// set the default folder.  that is the /assets/ACFs folder in
			// the stylesheet's directory.

			/** @noinspection PhpUndefinedFunctionInspection */

			$folder = get_stylesheet_directory() . "/assets/ACFs";
		}

		// even if we just set our default above, we want to pass the folder
		// through a filter.  this is primarily so that child themes, which
		// might not be able to change the parameter to the initializing
		// method above, will still be able to change the folder location if
		// necessary.

		/** @noinspection PhpUndefinedFunctionInspection */

		return apply_filters("acf_export_folder", $folder);
	}

	/**
	 * findOrCreate
	 *
	 * Given the name of a folder, create it if it does not exist.
	 *
	 * @param string $folder
	 *
	 * @return bool
	 */
	protected function findOrCreate(string $folder) {
		if (!is_dir($folder)) {
			return mkdir($folder);
		}

		return true;
	}

	/**
	 * exportFieldGroup
	 *
	 * Given a post ID for an acf-field-group post, get the information
	 * about it that we want to export and then save it to a local file.
	 *
	 * @param int     $postId
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	protected function exportFieldGroup(int $postId, WP_Post $post) {
		if ($post->post_type === "acf-field-group") {
			list($acfName, $filename) = $this->getFieldGroupDetails($postId);

			if (empty($acfName)) {
				return;
			}

			// given the information about our ACF, we can get the contents
			// for our exported file, construct an absolute path to the file
			// we want to create, and then write the first to the latter
			// as follows:

			$contents = $this->getFieldGroupContents($acfName);
			$filename = $this->acfExportFolder . $filename . ".json";
			file_put_contents($filename, $contents);
		}
	}

	/**
	 * getFieldGroupDetails
	 *
	 * Given the ID of a post in the database, get the information
	 * needed to get the JSON data about the ACF field group it
	 * references.
	 *
	 * @param int $postId
	 *
	 * @return array
	 */
	protected function getFieldGroupDetails(int $postId) {
		global $wpdb;

		// here, we need to get the post data for the $postId that was
		// passed here.  we could use the WP_Query object to do that, but
		// it has a lot of overhead.  instead, we'll just do it live.
		// some IDEs flag the ID=%d syntax for the wpdb->prepare() method
		// as an error.  so, we'll make a variable, $d, that contains
		// that string and use it below.

		$d = "%d";

		$statement = $wpdb->prepare(
			/** @lang text */
			"SELECT post_name, post_excerpt FROM $wpdb->posts WHERE ID=$d",
			$postId
		);

		return $wpdb->get_row($statement, ARRAY_N);
	}

	/**
	 * getFieldGroupContents
	 *
	 * Uses ACF functions to get information about the specified group
	 * and returns that information as a JSON string.
	 *
	 * @param string $acfName
	 *
	 * @return string
	 */
	protected function getFieldGroupContents(string $acfName) {
		$fieldGroup = acf_get_field_group($acfName);

		if (!empty($fieldGroup)) {
			$fieldGroup['fields'] = acf_get_fields($fieldGroup);
			$json = acf_prepare_field_group_for_export($fieldGroup);
		}

		return json_encode($json ?? "");
	}
}