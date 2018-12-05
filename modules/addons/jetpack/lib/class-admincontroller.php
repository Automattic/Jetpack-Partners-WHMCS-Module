<?php
/**
 * Controller class for handling output displayed to the partners on the Jetpack Addon module page.
 * Accepts incoming requests from the dispatcher, handles request and creates HTML output for the
 * corresponding request page.
 */


namespace Jetpack;

use WHMCS\Database\Capsule;
require_once __DIR__ . '/../../../servers/jetpack/lib/class-jetpackpartner.php';
use JetpackPartner\JetpackPartner;

/**
 * Controller for handling admin requests.
 */
class AdminController {

	/**
	 * Undocumented variable
	 *
	 * @var array
	 */
	public $jetpack_plans = [
		'Free',
		'Personal',
		'Premium',
		'Professional',
	];

	/**
	 * Undocumented function
	 *
	 * @param array $params Required params for the action.
	 * @return mixed action to perform or error string for invalid action
	 */
	public function index( $params ) {
		$menu       = $this->jetpack_menu( $params );
		$modulelink = $params['modulelink'];
		$outut      = '';

		if ( ! $params['partner_id'] || ! $params['partner_secret'] ) {
			$output .= <<<HTML
			Module Incorrectly Configured. Missing Partner ID or Partner Secret.
			<a href="/admin/configaddonmods.php"> Update Module configuration </a>

HTML;
		} else {
			$output .= <<<HTML
			<p>Module Activated on: </p>
			<p>Partner ID: {$params['partner_id']}</p>
			<p>Partner Secret: {$params['partner_secret']}</p>
			<a href="{$modulelink}&action=validate_partner_credentials" class="btn btn-primary">
				Validate Partner Credentials
			</a>

HTML;
		}
		return $menu . $output;
	}

	/**
	 * Manage Jetpack Products
	 *
	 * @param [type] $params
	 * @return void
	 */
	public function manage_product( $params ) {
		$menu             = $this->jetpack_menu( $params );
		$product_groups   = Capsule::table( 'tblproductgroups' )->get();
		$jetpack_products = Capsule::table( 'jetpack_products' )->lists( 'product_id' );
		$modulelink       = $params['modulelink'];

		$output = '';
		if ( ! $product_groups ) {
			$output .= <<<HTML
			No Product Groups Found. Please <a href="/admin/configproducts.php?action=creategroup">
			create a product group before adding a product </a>'
HTML;
			return $menu . $output;
		}

		if ( ! $jetpack_products ) {
			$output               .= <<<HTML
			You Do not currently have any Jetpack products set up. Which product group
			would you like to create the Jetpack Products under
HTML;
			$product_group_options = '';
			foreach ( $product_groups as $product_group ) {
				$product_group_options .=
				"<option value=\"$product_group->id\">$product_group->name</option>";
			}
			$output .= <<<HTML
				<form action="{$modulelink}&action=add_product" method="post">
				<select name="product_group_id">
					{$product_group_options}
				</select><br>
				<input type="submit" value="Submit">
				</form>
HTML;
		} else {
			$products = Capsule::table( 'tblproducts' )
			->whereIn( 'id', $jetpack_products )
			->get( [ 'name', 'id' ] );
			$bundles  = Capsule::table( 'tblbundles' )->get();

			foreach ( $products as $product ) {
				$product_table_rows .=
				"<tr><td>$product->name</td><td>0</td></tr>";
				$product_select     .=
				"<option value=\"$product->id\">$product->name</option>";
			}
			$output .= <<<HTML
			<table border="1">
			<tbody>
			<tr><th>Product Name</th><th>Licenses Provisioned</th></tr>
			{$product_table_rows}
			</tbody>
			</table>
			<br>
HTML;
		}

		if ( ! empty( $bundles ) ) {
			foreach ( $bundles as $bundle ) {
				$bundle_select .=
				"<option value=\"$bundle->id\">$bundle->name</option>";
			}

			$output .= <<< HTML
			Add Product To Bundle
			<form action="{$modulelink}&action=add_product_to_bundle" method="post">
				<select name="product_group_id">
					{$product_select}
				</select>
				<select name="bundle_group_id">
					{$bundle_select}
				</select>
				<input type="submit" value="Submit">
			</form>
			<br>
			<a href="/admin/configbundles.php" class="btn btn-primary">
				Manage Bundles
			</a>
HTML;
		} else {
			$output .= <<<HTML
			You have no bundles created <br>

			<a href="/admin/configbundles.php" class="btn btn-primary">
				Manage Bundles
			</a>
HTML;
		}

		return $menu . $output;
	}

	/**
	 * Add a Jetpack Product with required configurations fields to a hosting partners whmcs product
	 * list.
	 *
	 * @param array $params Module configuration parameters.
	 * @return string $output HTML output for the add product page
	 */
	public function add_product( $params ) {
		foreach ( $this->jetpack_plans as $plan ) {
			$post_data = [
				'type'   => 'other',
				'gid'    => $_POST['product_group_id'],
				'name'   => 'Jetpack - ' . $plan . ' Plan',
				'module' => 'jetpack',
				'hidden' => 1,
			];

			$product_id = localAPI( 'AddProduct', $post_data );

			Capsule::table( 'jetpack_products' )->insert(
				[
					'product_id' => $product_id['pid'],
					'plan_type'  => $plan,
					'created_at' => date( 'Y-m-d-H:i:s' ),
					'updated_at' => date( 'Y-m-d-H:i:s' ),
				]
			);

			$custom_fields = [
				[
					'field_name'    => 'Site URL',
					'field_type'    => 'text',
					'description'   => 'The site url the Jetpack plan will be provisioned for',
					'field_options' => '',
				],
				[
					'field_name'    => 'Local User',
					'field_type'    => 'text',
					'description'   => 'The blog user for the site',
					'field_options' => '',
				],
				[
					'field_name'    => 'Plan',
					'field_type'    => 'text',
					'description'   => 'The type of plan to provision',
					'field_options' => '',
				],
			];

			foreach ( $custom_fields as $field ) {
				Capsule::table( 'tblcustomfields' )->insert(
					[
						'type'         => 'product',
						'relid'        => $product_id['pid'],
						'fieldname'    => $field['field_name'],
						'fieldtype'    => $field['field_type'],
						'fieldoptions' => $field['field_options'],
						'required'     => 'on',
						'showorder'    => 'off',
					]
				);
			}
		}

		$output = 'Product ' . $post_data['name'] . ' Successfully added.';
		return $this->manage_product( $params ) . $output;
	}

	/**
	 * Add a Jetpack product to a whmcs bundle
	 *
	 * @param array $params Module configuration parameters.
	 * @return string $output HTML output for the add product page
	 */
	public function add_product_to_bundle( $params ) {
		$bundle = Capsule::table( 'tblbundles' )->where( 'id', '=', $_POST['bundle_group_id'] )->first();
		$item_data      = ( unserialize( $bundle->itemdata ) );
		$product_bundle = [
			'type'             => 'product',
			'pid'              => $_POST['product_group_id'],
			'billingcycle'     => '0',
			'priceoverride'    => null,
			'price'            => '0.00',
			'configoption'     => null,
			'addons'           => null,
			'regperiod'        => null,
			'dompriceoverride' => null,
			'domprice'         => null,

		];
		$item_data[]          = $product_bundle;
		$serialized_item_data = serialize( $item_data );
		Capsule::table( 'tblbundles' )->where( 'id', '=', $_POST['bundle_group_id'] )->update(
			[ 'itemdata' => $serialized_item_data ]
		);

		$output = 'Bundle Updated';
		return $this->manage_product( $params ) . $output;
	}

	/**
	 * Validate a partners id and secret entered when they configured the addon module.
	 *
	 * @param array $params Module Parameters.
	 * @return bool True if there is an access token else false
	 */
	public function validate_partner_credentials( $params ) {
		$partner = new JetpackPartner( $params['partner_id'], $params['partner_secret'] );
		if ( $partner->access_token ) {
			$output .= <<<HTML
			<p>Your Partner Id and Secret are correct and can be used to provision Jetpack Plans.</p>
HTML;
		} else {
			$output .= <<<HTML
			<p>Module Incorrectly Configured. Your Partner ID or Secret is not valid.
			<a href="/admin/configaddonmods.php"> Update Module configuration </a>
			</p>

HTML;
		}
		return $this->index( $params ) . $output;
	}

	/**
	 * Main Menu for Jetpack Addon Module
	 *
	 * @param array $params Module parameters.
	 * @return string $output HTML output for the add product page
	 */
	public function jetpack_menu( $params ) {
		$modulelink = $params['modulelink'];
		return <<<HTML
			<html>
			<link rel="stylesheet" type="text/css" href="/modules/addons/jetpack/lib/admin/css/admin.css"/>
			<body>
			<div class="jetpack-nav">
			<a class="" href={$modulelink}>Partner Details</a>
			<a class="" href="{$modulelink}&action=manage_product">Manage Jetpack Products</a>
			<a class="" href="{$modulelink}&action=provision_jetpack_plans">Provision Jetpack Plans</a>
			</div>
			</body>
			</html>
HTML;
	}

	/**
	 * Provision A jetpack plan
	 *
	 * @return void
	 */
	public function provision_jetpack_plans( $params ) {
		$output = <<<HTML
		<form>
		<input type="text" name="site_url" placeholder="Site URL"><br>
		<input type="text" name="blog_user" placeholder="Blog User"><br>
		<select>
		<option value="Free">Free</option>
		<option value="Personal">Personal</option>
		<option value="Premium">Premium</option>
		<option value="Professional">Professional</option>
		</select><br>
		<input type="submit" value="Provision" class="btn btn-primary">
		</form>
HTML;
		return $this->jetpack_menu( $params ) . $output;
	}
}
