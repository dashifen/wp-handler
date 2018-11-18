<?php

/** @noinspection PhpUndefinedFunctionInspection */

namespace Engage\WordPress\Traits;

/**
 * Trait CustomPostTypeUITrait
 *
 * Restricts access to the CPT UI menu item to only those users that
 * have an engagedc.com email address.
 *
 * @package Engage\WordPress\Traits
 */
trait HideCustomPostTypeUITrait {
	/**
	 * restrictCPTUI
	 *
	 * Checks the current user, and if it doesn't have an engagedc.com
	 * email address, alters the admin menu to remove the CPT UI menu item.
	 * The list of domains provided access to the CPT UI menu can be filtered
	 * with the valid_cpt_admin_domains filter.
	 *
	 * @return void
	 */
	protected function restrictCPTUI() {
		if ($this->mustRestrictCPTUI()) {

			// if we're restricting access to the CPT UI, we'll add an action
			// to the admin_menu hook to remove the CPT UI item.  we'll put that
			// action at priority level 1000 since that's very likely to always
			// be after the CPT UI plugin adds their item.  rather than create
			// a named function for this, we'll just use an anonymous one.

			add_action("admin_menu", function () {
				remove_menu_page("cptui_main_menu");
			}, 1000);
		}
	}

	/**
	 * mustRestrictCPTUI
	 *
	 * Given an array of domains, or provided one after filtering the
	 * default, see if the current user's email address's domain is in
	 * that array.
	 *
	 * @param array $domains
	 *
	 * @return bool
	 */
	protected function mustRestrictCPTUI(array $domains = ["engagedc.com"]) {
		$domains = apply_filters("valid_cpt_admin_domains", $domains);

		if (!is_array($domains)) {

			// if someone filters $domains and makes it anything other than
			// an array, we just return false, i.e. that we're not restricting
			// access.

			return false;
		}

		$currentUser = wp_get_current_user();
		foreach ($domains as $domain) {
			if (strpos($currentUser->user_email, $domain) !== false) {

				// if we found one of the valid email domains in the current
				// user's address, then we also return false because they
				// should have access to the CPT UI menu item.

				return false;
			}
		}

		// if we make it all the way down here, then we never found a reason
		// to allow this person to use the CPT UI.  therefore, we try to stop
		// them from doing so.

		return true;
	}


}