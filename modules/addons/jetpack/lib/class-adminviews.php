<?php

namespace Jetpack;

/**
 * Maintains functions used to create views for the whmcs addon module.
 */
class AdminViews {

	/**
	 * Link to Jetpack Addon Module
	 *
	 * @var string
	 */
	public $module_link;

	/**
	 * Jetpack Partner Id
	 *
	 * @var string
	 */
	public $partner_id;

	/**
	 * Jetpack Partner Secret
	 *
	 * @var string
	 */
	public $partner_secret;

	/**
	 *
	 * @param array $params whmcs module parameters.
	 */
	public function __construct( $params ) {
		$this->module_link    = $params['modulelink'];
		$this->partner_id     = $params['partner_id'];
		$this->partner_secret = $params['partner_secret'];
	}

	/**
	 * Makes a module page. Adds the main menu and page content. Also adds any notification messages
	 * before the page content.
	 *
	 * @param string $output page html contents.
	 * @param string $message notifcation messages to be displayed before page content.
	 */
	public function make_module_page( $output, $message = '' ) {
		return $message . $this->jetpack_menu() . $output;
	}

	/**
	 * Makes a form select option
	 *
	 * @param string $id select value.
	 * @param string $name select name.
	 * @return string $output HTMLoutput.
	 */
	public function make_select_options( $id, $name ) {
		return "<option value=\"$id\">$name</option>";
	}

	/**
	 * Main Menu for Jetpack Addon Module
	 *
	 * @return string $output HTML output for the add product page
	 */
	public function jetpack_menu() {
		return <<<HTML
			<html>
			<link rel="stylesheet" type="text/css" href="/modules/addons/jetpack/lib/css/admin.css" />

			<body>
				<p> The addon module allows you to create a Jetpack product for each of the availalbe
					Jetpack Plans (Free, Personal, Premium, Professional) with the required custom fields
					for provisioning pre configured. Use the Validate Credentials option to make sure your
					partner ID and partner secret are valid and can be used to provision Jetpack plans. </p>
				<div class="jetpack-nav">
					<a class="" href="{$this->module_link}">Partner Details</a>
					<a class="" href="{$this->module_link}&action=manage_product">Manage Jetpack Products</a>
					<a class="" href="{$this->module_link}&action=provision_jetpack_plans">Provision Jetpack Plans</a>
				</div>
			</body>

			</html>
HTML;
	}

	/**
	 * Display a message when an action is performed. The type can be info, success or error.
	 *
	 * @param string $type The type of message being displayed.
	 * @param string $title The title of the message.
	 * @param string $message The message to display.
	 * @return string $output HTMLoutput.
	 */
	public function make_action_message( $type, $title, $message ) {
		$type_div = [
			'info'    => 'infobox',
			'success' => 'successbox',
			'error'   => 'errorbox',
		];
		return <<<HTML
					<div class="{$type_div[$type]}"><strong>
						<span class="title">{$title}!</span>
					</strong><br>{$message}</div>
HTML;
	}

	/**
	 * Views for the partner details page
	 *
	 * @return string $output HTMLoutput.
	 */
	public function partner_details() {
		if ( ! $this->partner_id || ! $this->partner_secret ) {
			$output .= <<<HTML
			Module Incorrectly Configured. Missing Partner ID or Partner Secret.
			<a href="/admin/configaddonmods.php"> Update Module configuration </a>

HTML;
		} else {
			$output .= <<<HTML
				<table class="datatable no-margin" width="100%" border="0" cellspacing="1" cellpadding="3">
					<tbody>
						<tr>
							<th>Partner ID</th>
							<th>Partner Secret</th>
						</tr>
						<tr>
							<td>{$this->partner_id}</td>
							<td>{$this->partner_secret}
						</tr>
						</td>
					</tbody>
				</table>

				<div class="btn-container">
					<a href="{$this->module_link}&action=validate_partner_credentials" class="btn btn-primary">
						Validate Partner Credentials
					</a>
				</div>
HTML;
		}
		return $this->make_module_page( $output );
	}

	/**
	 * Content displayed when the partner host does not have any product groups created. Product
	 * groups are necessary for products to be added.
	 *
	 * @return string $output HTMLoutput.
	 */
	public function manage_products_no_product_groups() {
		$message = $this->make_action_message(
			'info',
			'No Product Group Found',
			'No Product Groups Found. A product group is necessary in order ccreate the Jetpack Products.
			Please <a href="/admin/configproducts.php?action=creategroup"> create a product group before
			adding the Jetpack products </a>'
		);

		return $this->make_module_page( '', $message );
	}

	/**
	 * Displayed when there are no jetpack products created using the addon module identified.
	 * Products created using the addon module are stored in the jetpack_products table which is
	 * created when the module is activated.
	 *
	 * @param array $product_groups An array of product groups the host has created.
	 */
	public function manage_products_no_jetpack_products( $product_groups ) {
		$message .= $this->make_action_message(
			'info',
			'No Jetpack products created',
			'You Do not currently have any Jetpack products set up. Which product group
			would you like to create the Jetpack Products under?'
		);

		$product_group_options = '';
		foreach ( $product_groups as $product_group ) {
			$product_group_options .= $this->make_select_options(
				$product_group->id,
				$product_group->name
			);
		}
		$output .= <<<HTML
			<form action="{$this->module_link}&action=add_product" method="post">
				<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
					<tbody>
						<tr>
							<td width="150" class="fieldlabel">Product Group</td>
							<td class="fieldarea">
								<select name="product_group_id">
									{$product_group_options}
								</select><br>
					</tbody>
				</table>
				<div class="btn-container">
					<input type="submit" value="Create" class="btn btn-primary">
				</div>
			</form>
HTML;
		return $this->make_module_page( $output, $message );

	}

	/**
	 * Manage Product
	 *
	 * @param array $products Jetpack products.
	 * @param array $bundles whmcs bundles.
	 * @return string $output HTMLoutput.
	 */
	public function manage_products_view_products( $products, $bundles ) {
		foreach ( $products as $product ) {
			$product_table_rows .=
			"<tr><td><a href=\"/admin/configproducts.php?action=edit&id=$product->id\">$product->name</a></td><td>0</td></tr>";
			$product_select     .=
			"<option value=\"$product->id\">$product->name</option>";
		}

		$output .= <<<HTML
			<table class="datatable no-margin" width="100%" border="0" cellspacing="1" cellpadding="3">
				<tbody>
					<tr>
						<th>Product Name</th>
						<th>Licenses Provisioned</th>
					</tr>
					{$product_table_rows}
				</tbody>
			</table>
			<br>
HTML;
		if ( ! empty( $bundles ) ) {
			$output .= '<a href="/admin/configbundles.php" class="btn btn-primary">
			Manage Bundles
		</a>';
		} else {
			$message = $this->make_action_message(
				'info',
				'No Bundles Found',
				'You have no bundles created <a href="/admin/configbundles.php">Create a Product
				Bundle to add a Jetpack product to it.</a>'
			);
		}

		return $this->make_module_page( $output, $message );
	}

	/**
	 * Form for manually provisioning a jetpack plan.
	 *
	 * @return string $output HTMLoutput.
	 */
	public function provision_jetpack_plans() {
		$output = <<<HTML
			<form action="{$this->module_link}&action=provision_plan" method="post">
				<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
					<tbody>
						<tr>
							<td width="150" class="fieldlabel">Plan Type</td>
							<td class="fieldarea">
								<select name="plan_type" class="form-control select-inline">
									<option value="Free">Free</option>
									<option value="Personal">Personal</option>
									<option value="Premium">Premium</option>
									<option value="Professional">Professional</option>
								</select></td>
						</tr>
						<tr>
							<td class="fieldlabel">
								Site URL </td>
							<td class="fieldarea">
								<input type="text" class="form-control input-500" name="site_url">
							</td>
						</tr>
						<tr>
							<td class="fieldlabel">
								Blog User </td>
							<td class="fieldarea">
								<input type="text" class="form-control input-500" name="blog_user">
							</td>
						</tr>
					</tbody>
				</table>
				<div class="btn-container">
					<input type="submit" value="Provision Plan" class="btn btn-primary">
				</div>
			</form>
HTML;
		return $this->make_module_page( $output );
	}
}