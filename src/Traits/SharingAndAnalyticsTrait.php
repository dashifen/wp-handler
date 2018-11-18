<?php

/** @noinspection PhpUndefinedFunctionInspection */

namespace Engage\WordPress\Traits;

/**
 * Trait SharingTrait
 *
 * For smaller sites where we don't want to necessarily use the SEO plugin,
 * this trait provides the basic Twitter, Facebook, and Google fields to
 * provide for basic sharing and analytics.
 *
 * @package Engage\WordPress\Traits
 */
trait SharingAndAnalyticsTrait {
	/**
	 * @var string
	 */
	protected $sharingMenuName;

	/**
	 * @var string
	 */
	protected $sharingMenuIcon;

	/**
	 * @var int
	 */
	protected $sharingMenuPosition;

	/**
	 * @var bool
	 */
	protected $sharingIsSubPage;

	/**
	 * initializeSharingAndAnalytics
	 *
	 * This should be called from the theme's primary initialize() method.
	 * It handles all the setup necessary to add these fields to the admin
	 * dashboard.
	 *
	 * @param bool   $subPage
	 * @param string $name
	 * @param string $icon
	 * @param int    $position
	 *
	 * @return void
	 */
	protected function initializeSharingAndAnalytics(
		bool $subPage = false,
		string $name = "Sharing/Analytics",
		string $icon = "dashicons-share",
		int $position = 9
	) {
		$this->sharingIsSubPage = $subPage;
		$this->sharingMenuName = $name;
		$this->sharingMenuIcon = $icon;
		$this->sharingMenuPosition = $position;

		if (function_exists("acf_add_options_page") && method_exists($this, "addAction")) {

			// as long as ACF and our AbstractTheme's addAction method exist,
			// we can continue.  first, we'll add the options page and the
			// fields that appear on it.  note that the fields are added at
			// priority 15 to be 100% sure the page exists first.

			$this->addAction("acf/init", "addOptionsPage");
			$this->addAction("acf/init", "addFieldGroups", 15);

			// now, we want to set a default value for the title field on
			// the sharing page:  the name of the blog.  someone can change
			// it, but this will work for most situations.  then, we also
			// hook a warning message to the admin_notices action so that
			// people will get bothered about filling in these data.

			$this->addAction("acf/load_value/key=field_5b217fef91019", "setDefaultSharingTitle");
			$this->addAction("admin_notices", "notifyOnMissingData");
		}
	}

	/**
	 * addOptionsPage
	 *
	 * Adds the options page to the Dashboard menu using the details passed
	 * here from the initialization function above.
	 *
	 * @return void
	 */
	protected function addOptionsPage() {
		$args = $this->sharingIsSubPage
			? [
				"page_title"  => $this->sharingMenuName,
				"parent_slug" => "options-general.php",
				"capability"  => "manage_options",
			] : [
				"page_title" => $this->sharingMenuName,
				"icon_url"   => $this->sharingMenuIcon,
				"position"   => $this->sharingMenuPosition,
				"capability" => "manage_options",
			];

		acf_add_options_page($args);
	}

	/**
	 * addFieldGroups
	 *
	 * Adds the actual ACF fields and field groups to the options page we
	 * added above.  This PHP was generated with the ACF plugin.
	 *
	 * @return void
	 */
	protected function addFieldGroups() {
		acf_add_local_field_group([
			'key'                   => 'group_5b217fc6e5c3a',
			'title'                 => 'Social Networking and Analytics',
			'fields'                => [
				[
					'key'               => 'field_5b21814b34e06',
					'label'             => 'General Fields',
					'name'              => 'general',
					'type'              => 'group',
					'instructions'      => 'These fields are used on both Twitter and Facebook.',
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'layout'            => 'block',
					'sub_fields'        => [
						[
							'key'               => 'field_5b217fef91019',
							'label'             => 'Title',
							'name'              => 'title',
							'type'              => 'text',
							'instructions'      => 'Enter the title of this site.	By default, it\'s the name of the site in Settings > General.',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => [
								'width' => '',
								'class' => '',
								'id'    => '',
							],
							'default_value'     => '',
							'placeholder'       => '',
							'prepend'           => '',
							'append'            => '',
							'maxlength'         => '',
						],
						[
							'key'               => 'field_5b21819634e07',
							'label'             => 'Description',
							'name'              => 'description',
							'type'              => 'textarea',
							'instructions'      => 'Enter a brief description of this site.	Facebook recommends one or two sentences.',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => [
								'width' => '',
								'class' => '',
								'id'    => '',
							],
							'default_value'     => '',
							'placeholder'       => '',
							'maxlength'         => '',
							'rows'              => 3,
							'new_lines'         => '',
						],
					],
				],
				[
					'key'               => 'field_5b217fcf91018',
					'label'             => 'Facebook Sharing',
					'name'              => 'facebook',
					'type'              => 'group',
					'instructions'      => 'Use these fields to determine how this site shows up when others share it on Facebook.',
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'layout'            => 'block',
					'sub_fields'        => [
						[
							'key'               => 'field_5b2180419101b',
							'label'             => 'Image',
							'name'              => 'image',
							'type'              => 'image',
							'instructions'      => 'Images must be exactly 1200 pixels wide and 630 pixels high.',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => [
								'width' => '',
								'class' => '',
								'id'    => '',
							],
							'return_format'     => 'id',
							'preview_size'      => 'thumbnail',
							'library'           => 'all',
							'min_width'         => 1200,
							'min_height'        => 630,
							'min_size'          => '',
							'max_width'         => 1200,
							'max_height'        => 630,
							'max_size'          => '',
							'mime_types'        => '',
						],
					],
				],
				[
					'key'               => 'field_5b2180d034e03',
					'label'             => 'Twitter Sharing',
					'name'              => 'twitter',
					'type'              => 'group',
					'instructions'      => 'Use these fields to describe how this site will be shown online when others tweet about it.',
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'layout'            => 'block',
					'sub_fields'        => [
						[
							'key'               => 'field_5b2180f434e04',
							'label'             => 'Twitter Handle',
							'name'              => 'handle',
							'type'              => 'text',
							'instructions'      => 'Enter the handle for the Twitter account associated with this site.	If it doesn\'t have one, use the NRSC account.',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => [
								'width' => '',
								'class' => '',
								'id'    => '',
							],
							'default_value'     => '',
							'placeholder'       => '',
							'prepend'           => '@',
							'append'            => '',
							'maxlength'         => '',
						],
						[
							'key'               => 'field_5b2181d134e08',
							'label'             => 'Image',
							'name'              => 'image',
							'type'              => 'image',
							'instructions'      => 'Unfortunately, Twitter uses a slightly different size for the images it uses when sharing pages.	Please upload an image that\'s exactly 1200 pixels wide and 675 pixels high.',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => [
								'width' => '',
								'class' => '',
								'id'    => '',
							],
							'return_format'     => 'id',
							'preview_size'      => 'thumbnail',
							'library'           => 'all',
							'min_width'         => 1200,
							'min_height'        => 675,
							'min_size'          => '',
							'max_width'         => 1200,
							'max_height'        => 675,
							'max_size'          => '',
							'mime_types'        => '',
						],
					],
				],
				[
					'key'               => 'field_5b2180129101a',
					'label'             => 'Google Analytics "UA" Code',
					'name'              => 'analytics_id',
					'type'              => 'text',
					'instructions'      => 'Enter the "UA" code provided by Google for this site\'s analytics.',
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => 'UA-',
					'append'            => '',
					'maxlength'         => '',
				],
			],
			'location'              => [
				[
					[
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'acf-options-sharing-analytics',
					],
				],
			],
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => 1,
			'description'           => '',
		]);
	}

	/**
	 * setDefaultSharingTitle
	 *
	 * If the $value parameter is empty, returns the blog's name.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	protected function setDefaultSharingTitle(string $value) {
		return (string) (empty($value) ? get_bloginfo("name") : $value);
	}

	/**
	 * notifyOnMissingData
	 *
	 * Sets an admin notice when the sharing and analytics information is
	 * not available.
	 *
	 * @return void
	 */
	protected function notifyOnMissingData() {
		$data = $this->getSharingData();
		$data = array_merge($data, $this->getAnalyticsData());

		if ($this->isDataMissing($data)) {
			/** @noinspection HtmlUnknownTarget */

			$link = sprintf('<a href="%s">%s</a>',
				admin_url("admin.php?page=acf-options-sharing-analytics"),
				$this->sharingMenuName); ?>

			<div class="notice notice-error">
				<p>Please fully complete the <?= $link ?> information
				before launching this site.</p>
			</div>

		<?php }
	}

	/**
	 * getSharingData
	 *
	 * Returns a structured array of sharing data based on the ACFs defined
	 * above.  Notice that the general title and description are included
	 * twice, once for each network.
	 *
	 * @return array
	 */
	protected function getSharingData() {
		if (function_exists("get_field")) {
			$generalTitle = get_field("general_title", "option");
			$generalDescription = get_field("general_description", "option");
			$twitterHandle = $this->getTwitterHandle();

			return [
				"facebook" => [
					"url"         => get_home_url(),
					"title"       => $generalTitle,
					"description" => $generalDescription,
					"image"       => $this->getImageSrC("facebook"),
				],

				"twitter" => [
					"site"        => $twitterHandle,
					"title"       => $generalTitle,
					"description" => $generalDescription,
					"image"       => $this->getImageSrc("twitter"),
				],
			];
		}

		return [];
	}

	/**
	 * getTwitterHandle
	 *
	 * Returns the twitter handle for this site ensuring that it begins with
	 * the @-symbol.
	 *
	 * @return string
	 */
	protected function getTwitterHandle() {
		$twitterHandle = get_field("twitter_handle", "option");

		if (substr($twitterHandle, 0, 1) !== "@") {
			$twitterHandle = "@" . $twitterHandle;
		}

		return $twitterHandle;
	}

	/**
	 * getImageSrc
	 *
	 * Given the social network we care about at this time, return the
	 * image that we're to use when this site is shared on it.
	 *
	 * @param string $network
	 *
	 * @return string
	 */
	protected function getImageSrc(string $network) {
		$imageId = get_field($network . "_image", "option");
		return wp_get_attachment_image_src($imageId, "full")[0];
	}

	/**
	 * getAnalyticsData
	 *
	 * Returns the information about analytics based on the ACFs above.
	 *
	 * @return array
	 */
	protected function getAnalyticsData() {
		if (function_exists("get_field")) {
			$uaId = strtoupper(get_field("analytics_id", "option"));

			if (substr($uaId, 0, 3) !== "UA-") {
				$uaId = "UA-" . $uaId;
			}

			return ["id" => $uaId];
		}

		return [];
	}

	/**
	 * isDataMissing
	 *
	 * Given an array, returns true if everything in it is empty.
	 *
	 * @param array $array
	 *
	 * @return bool
	 */
	protected function isDataMissing(array $array) {

		// ACF should make sure that our data is complete -- i.e. it's all
		// required in the field group.  but, we're going to check for it all
		// here just in case.  to do that, we're going to flatten the array
		// (source: https://stackoverflow.com/a/1320156/360838) and then loop
		// through it.  the first empty value we find, we return true.

		$flatArray = $this->array_flatten($array);

		foreach ($flatArray as $value) {
			if (empty($value)) {
				return true;
			}
		}

		return false;

	}

	/**
	 * array_flatten
	 *
	 * Turns a multi-dimensional array into a single-dimensional one
	 * containing all the values within the original.
	 *
	 * @param $array
	 *
	 * @return array
	 */
	protected function array_flatten(array $array) {
		$flatArray = [];

		array_walk_recursive($array, function($value) use (&$flatArray) {

			// the array_walk_recursive() function means that each $value within
			// the non-flat $array ends up here.  we trim them all and then add
			// them to $flatArray.

			$flatArray[] = trim($value);
		});

		return $flatArray;
	}
}