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
use Jetpack\AdminViews;

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
	 * Main module entry point. Currently the partner details page.
	 *
	 * @param array $params Required params for the action.
	 * @return string HTML for the module menuo and partner details page
	 */
	public function index( $params ) {
		$views = new AdminViews( $params );
		return $views->partner_details();
	}

	/**
	 * Manage Jetpack Products
	 *
	 * @param array $params Module configuration parameters.
	 * @return string $output HTMLoutput.
	 */
	public function manage_product( $params ) {
		$views            = new AdminViews( $params );
		$product_groups   = Capsule::table( 'tblproductgroups' )->get();
		$jetpack_products = Capsule::table( 'jetpack_products' )->lists( 'product_id' );
		$modulelink       = $params['modulelink'];
		$products = Capsule::table( 'tblproducts' )
		->whereIn( 'id', $jetpack_products )
		->get( [ 'name', 'id' ] );

		if ( ! $product_groups ) {
			return $views->manage_products_no_product_groups();
		}
		if ( ! $jetpack_products || ! $products ) {
			return $views->manage_products_no_jetpack_products( $product_groups );
		} else {
			$bundles  = Capsule::table( 'tblbundles' )->get();

			return $views->manage_products_view_products( $products, $bundles );
		}
	}

	/**
	 * Provision A jetpack plan
	 *
	 * @param array $params Module Parameters.
	 * @return string $output HTMLoutput.
	 */
	public function provision_jetpack_plans( $params ) {
		$views = new AdminViews( $params );
		return $views->provision_jetpack_plans();
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
				'type'          => 'other',
				'gid'           => $_POST['product_group_id'],
				'name'          => 'Jetpack - ' . $plan . ' Plan',
				'module'        => 'jetpack',
				'hidden'        => 1,
				'configoption1' => $params['partner_id'],
				'configoption2' => $params['partner_secret'],
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
				[
					'field_name'    => 'jetpack_provisioning_details',
					'field_type'    => 'text',
					'description'   => 'Jetpack Provision details from response',
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
						'adminonly'    => 'on',
						'required'     => 'on',
						'showorder'    => 'off',
					]
				);
			}
		}

		$views   = new AdminViews();
		$message = $views->make_action_message(
			'success',
			'Products Created',
			'The Jetpack products have been created and can be reviewed below.'
		);
		return $message . $this->manage_product( $params );
	}

	/**
	 * Validate a partners id and secret entered when they configured the addon module. Return a
	 * success message if the id and secret can be used to get a token or an error message and a
	 * link to update the credentials if not.
	 *
	 * @param array $params Module Parameters.
	 * @return string $message a success or error message and the index page html.
	 */
	public function validate_partner_credentials( $params ) {
		$partner = new JetpackPartner( $params['partner_id'], $params['partner_secret'] );
		$views   = new AdminViews();
		if ( $partner->access_token ) {
			$message = $views->make_action_message(
				'success',
				'Valid Credentials',
				'Your partner credentials are valid and can be used to provision Jetpack plans.'
			);
		} else {
			$message = $views->make_action_message(
				'error',
				'Invalid Credentials',
				'<p>Module Incorrectly Configured. Your Partner ID or Secret is not valid.
				<a href="/admin/configaddonmods.php"> Update Module configuration </a>'
			);
		}
		return $message . $this->index( $params );
	}

	/**
	 * Provision a Jetpack Plan
	 *
	 * @return void
	 */
	public function provision_plan( $params ) {
		$views   = new AdminViews();
		$message = $views->make_action_message(
			'success',
			'Plan Provisioned',
			'<p>Plan successfully provisioned</a>'
		);
		$partner = new JetpackPartner( $params['partner_id'], $params['partner_secret'], $_POST['site_url'] );
		$response = $partner->provision_plan( $_POST['plan_type'], $_POST['blog_user'] );
		return $message . $views->provision_jetpack_plans();
	}

}
