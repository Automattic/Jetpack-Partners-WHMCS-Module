<?php

namespace Jetpack;

use Exception;
use WHMCS\Database\Capsule;
use Jetpack\AdminViews;

/**
 * Controller for handling admin requests.
 */
class AdminController
{

    /**
     * Main module entry point the manage products page
     *
     * @param array $params Required params for the action.
     * @return string HTML for the module menu and the manage products page
     */
    public function index($params)
    {
        $views = new AdminViews($params);
        return $views->index();
    }


    /**
     * Update the API token
     *
     * @param array $params Module Parameters.
     * @return string $output HTMLoutput.
     */
    public function updateAPIToken($params)
    {
        $views = new AdminViews($params);
        try {
            Capsule::table('tblproducts')->where('servertype', 'jetpack')
                ->update(['configoption1' => $_POST['api_token']]);

            Capsule::table('tbladdonmodules')->where(['module' => 'jetpack', 'setting' => 'api_token'])
                ->update(['value' => $_POST['api_token']]);
        } catch (Exception $e) {
            $message = $views->make_action_message(
                'error',
                'API Token Update',
                'Unable to Update API Token ' . $e->getMessage()
            );
            return $message . $views->index();
        }
        $message = $views->make_action_message(
            'success',
            'API Token Update',
            'API Token Successfully Updated'
        );

        return $message . $views->index();
    }

    /**
     * Add a Jetpack Product with required configurations fields to a hosting partners whmcs product
     * list.
     *
     * @param array $params Module configuration parameters.
     * @return string $output HTML output for the add product page
     */
    public function addProduct($params)
    {
        $product_group = Capsule::table('tblproductgroups')->where(['name' => 'Jetpack', 'slug' => 'jetpack'])->first();
        $product_name = $this->formatProductName($_POST['jetpack_product']);
        $jetpack_product = $_POST['jetpack_product'];
        $create_status = false;
        if ($_POST['product_type'] === 'product') {
            $create_status = $this->createWHMCSProduct($product_group->id, $product_name, $params['api_token'], $jetpack_product);
        } elseif ($_POST['product_type'] === 'product_addon') {
            $create_status = $this->createWHMCSProductAddon($product_name, $params['api_token'], $jetpack_product);
        }

        $views   = new AdminViews($params);
        if ($create_status === true) {
            $message = $views->make_action_message(
                'success',
                'Product Created',
                'The Jetpack product has been created and can be reviewed below.'
            );
        } else {
            $message = $views->make_action_message(
                'error',
                'Product Not Created',
                'The Jetpack product was unable to be created. Please review the logs for details.'
            );
        }
        return $message . $views->index();
    }

    /**
     * Create a WHMCS product for a Jetpack Product
     *
     * @param string $product_group_id The product group id for the product
     * @param string $product_name The product name
     * @param string $config1 The Partner API token.
     * @param string $config2 The Jetpack product identifier.
     * @return bool
     */
    public function createWHMCSProduct($product_group_id, $product_name, $config1, $config2)
    {
        try {
            Capsule::table('tblproducts')->insert(
                [
                    'gid' => $product_group_id,
                    'name' => $product_name,
                    'type' => 'other',
                    'servertype' => 'jetpack',
                    'hidden' => 1,
                    'configoption1' => $config1,
                    'configoption2' => $config2,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            );
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Create a WHMCS product addon for a Jetpack Product
     *
     * @param string $product_name The product name
     * @param string $config1 The Partner API token.
     * @param string $config2 The Jetpack product identifier.
     * @return bool
     */
    public function createWHMCSProductAddon($product_name, $config1, $config2)
    {
        try {
            $product_addon_id = Capsule::table('tbladdons')->insertGetId(
                [
                    'name' => $product_name,
                    'type' => 'other',
                    'module' => 'jetpack',
                    'hidden' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            );
            $configoptions = [
                [
                    'entity_type' => 'addon',
                    'entity_id' => $product_addon_id,
                    'setting_name' => 'configoption1',
                    'friendly_name' => 'API Token',
                    'value' => $config1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                [
                    'entity_type' => 'addon',
                    'entity_id' => $product_addon_id,
                    'setting_name' => 'configoption2',
                    'friendly_name' => 'Jetpack Product',
                    'value' => $config2,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ];
            Capsule::table('tblmodule_configuration')->insert($configoptions);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Format product name to remove undersocres and capitalize.
     *
     * @param string $product_name
     * @return string Formatted product name string.
     */
    public function formatProductName($product_name)
    {
        return ucwords(str_replace('-', ' ', $product_name));
    }
}
