<?php

namespace Jetpack;

use WHMCS\Database\Capsule;

/**
 * Maintains functions used to create views for the WHMCS addon module.
 */
class AdminViews {

    /**
     * Undocumented variable
     *
     * @var array
     */
    public $admin_views = [
        'listProductsView',
        'addProductView',
        'updateAPITokenView'
    ];

    /**
     * Link to Jetpack Addon Module
     *
     * @var string
     */
    public $module_link;

    /**
     * Jetpack Partner API token
     *
     * @var string
     */
    public $partner_api_token;

    /**
     * @param array $params WHMCS module parameters.
     */
    public function __construct($params)
    {
        $this->module_link = $params['modulelink'];
        $this->partner_api_token = $params['api_token'];
    }

    public function index()
    {
        $view = '';
        foreach ($this->admin_views as $admin_view) {
            $view .= call_user_func([$this, $admin_view]);
        }
        return $view;
    }

    /**
     * Make select options from an array for output
     *
     * @param array $options Options to create select from.
     */
    public function makeSelectOptions($options)
    {
        $select_options = '';
        foreach ($options as $key => $option) {
            $select_options .= "<option value='$key'>$option</option>";
        }
        return $select_options;
    }

    /**
     * Display a message when an action is performed. The type can be info, success or error.
     *
     * @param string $type The type of message being displayed.
     * @param string $title The title of the message.
     * @param string $message The message to display.
     * @return string $output HTMLoutput.
     */
    public function makeActionMessage($type, $title, $message)
    {
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
     * List existing products and product addons table view
     */
    public function listProductsView()
    {
        $products = Capsule::table('tblproducts')->where('servertype', 'jetpack')->select(['id','name'])->get();
        $product_addons = Capsule::table('tbladdons')->where('module', 'jetpack')->select(['id','name'])->get();

        if (is_a($products, 'Illuminate\Database\Eloquent\Collection')) {
            $products = $products->toArray();
            $product_addons = $product_addons->toArray();
        }

        $table_row_products = '';
        $table_row_product_addons = '';

        if (empty($products) && empty($product_addons)) {
            $table_row_products = '<tr><td colspan ="3"; style="text-align: center";> No Jetpack products or product addons found. Add some below.</td></tr>';
        }
        if (!empty($products)) {
            foreach ($products as $product) {
                $table_row_products .= "<tr><td><a href=configproducts.php?action=edit&id=$product->id>$product->name</a></td><td>Product</td></tr>";
            }
        }
        if (!empty($product_addons)) {
            foreach ($product_addons as $product_addon) {
                $table_row_products .= "<tr><td><a href=configaddons.php?action=manage&id=$product_addon->id>$product_addon->name</a></td><td>Product Addon</td></tr>";
            }
        }
        return <<<HTML
        <div style="text-align:center;">
            <p><b>Jetpack Products</b></p>
        </div>
        <table class="datatable no-margin" width="100%" border="0" cellspacing="1" cellpadding="3">
            <tbody>
                <tr>
                    <th>Product Name</th>
                    <th>Product Type</th>
                </tr>
                {$table_row_products}
                {$table_row_product_addons}
            </tbody>
        </table>
        <br>
        <hr>
HTML;
    }

    /**
     * View for adding Jetpack Products
     */
    public function addProductView()
    {
        $products =  jetpack_FetchProducts();
        $select_options = $this->makeSelectOptions($products);
        return <<<HTML
        <div style="text-align:center;">
            <p><b>Add Jetpack products to your product catalog (hidden by default).</b></p>
        </div>
        <form action="{$this->module_link}&action=addProduct" method="post">
            <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
                <tbody>
                    <tr>
                        <td class="fieldlabel" style="min-width:200px;">Jetpack Product</td>
                        <td class="fieldarea">
                            <select name="jetpack_product" class="form-control select-inline">
                                {$select_options}
                            </select> The Jetpack product you would like to add.
                        </td>
                    </tr>
                    <tr>
                        <td class="fieldlabel" style="min-width:200px;">WHMCS Product Type</td>
                        <td class="fieldarea">
                            <select name="product_type" class="form-control select-inline">
                                <option value='product'>Product</option>"
                                <option value='product_addon'>Product Addon</option>"
                            </select> The type of WHMCS product to be created (standalone product or product addon).
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="btn-container">
                <input type="submit" value="Add Jetpack Product" class="btn btn-primary">
            </div>
        </form>
        <hr>
HTML;
    }

    /**
     * View for updating the API Token
     *
     * @return void
     */
    public function updateAPITokenView()
    {
        return <<<HTML
        <div style="text-align:center;">
            <p><b>Update your partner API token for all existing Jetpack products, addons and the addon module.</b></p>
        </div>
        <form action="{$this->module_link}&action=updateAPIToken" method="post">
            <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
                <tbody>
                <tr>
                <td class="fieldlabel">API Token</td>
                <td class="fieldarea"><input type="text" name="api_token" value="{$this->partner_api_token}" class="form-control input-inline input-500"> Your partner API token for the Jetpack licensing API.</td>
                </tbody>
            </table>
            <div class="btn-container">
                <input type="submit" value="Update API Token" class="btn btn-primary">
            </div>
        </form>
        <hr>
HTML;
    }
}
