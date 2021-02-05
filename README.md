# WHMCS Jetpack Modules

The modules are intended to assist Jetpack Hosting partners in managing Jetpack products for their users. The modules options are available when a product or product addon is created that impleents the Jetpack WHMCS provisioning module in the list of available modules.

Once purchased the Jetpack license can be issued based on one of the 4 available options in WHMCS when creating a new product that involves a module for provisioning(3 Automatic provisioning options and the option to not provision under Module Settings when creating a product).

The provisioning module also allows for hosts to manually manage the product from the WHMCS clients area in WHMCS when needed once the client has placed the order for the product. The product will be listed in the clients area of WHMCS under the products/services tab for the specific client. Module functions include both automattic license issuing on checkout as well as the option to both issue and revoke a license using the create and terminate options available with the module.

## Getting Started
This documentation includes information for managing the WHMCS provisioning and addon modules.  If you’d like to become a Jetpack Hosting Partner please take a look at the [Jetpack Hosting Partner Information Page](https://jetpack.com/for/hosts/) for more information and to get started. In order to use the module a valid WHMCS license as well as a Jetpack Hosting Partner account and API token are required. To get started with WHMCS please visit https://www.whmcs.com/.

## Available Modules
The repository includes 2 modules for WHMCS.
**Please note that while the provisioning module can be used on it's own the addon module will require the provisioning module to be available in order to be used.**
  - A provisioning module that handles API calls to the Jetpack licensing API for issuing and revoking licenses as well as retrieving a list of avialble products for Jetpack provisioing
  - An addon module to help faciliate creation of Jetpack producsts as WHMCS products and product addons. The addon module also provides an option to update the partner API token for all existing products where the provisioning module is used.

## Installation/Setup
To install the modules upload them their respective directories within WHMCS (`/modules/servers` and `/modules/addons`). A server is not required for the module to function so the module will not be listed in the WHMCS Servers under the Setup tab for Products/Servers. Once uploaded to WHMCS the modules are ready for use when making a new product in WHMCS.

### Provisioning Module
To use the provisioing module create a new product or product addon in one of your existing product groups. As part of the product creation process in the Module Settings tab select the “Jetpack Provisioing” module to get started. You will need to have an established Jetpack Hosting Partner account and your partner API token will be requested in order to utilize the Jetpack licensing API. The provisioning module will create a table `jetpack_product_licenses` on it's first use if the table has not previously been created either by the addon module or previous use of the provisioning module.

#### Provisioning module configuration options
The provisioning module has 2 configuration options
- API Token: Your API token mentioned above which is required for using the Jetpack licensing API.
- Jetpack Product: The Jetpack product that will be tied to the product being created/sold in WHMCS

### Addon Module
To use the addon module first activate it from the Addon Modules section within WHMCS system settings and provide your partner API token in the module configuration. The module will then be available under Addons in the main WHMCS menu. In order to allow products to be created the addon module will create a  product group "Jetpack" which will be the default product group for all products created using the addon module. All products and products created using the addon module have the required module for them set to the Jetpack provisioning module with the right configuration option set for the API
token and the Jetpack product. All products created with the addon module are also created with minimal values and hidden by default to allow for adjustments before being made available to end users.

**Please Note:**
As part of the the product creation for WHMCS you may use any of the 4 options under Module Settings for setting up the product. The modules Create functionality will automatically be called an account provisioned if any of the first 3 options are selected.

In the Clients Tab of WHMCS admin under Products/Services the module currently provides functionality for Create and Terminate for Jetpack plans. If you do not setup the product to be automatically provisioned on checkout you will be able perform either of these actions for your users.

## Additional Information/Troubleshooting

- Most errors that occur when a user is checking out will be logged in the WHMCS Activity Log and prefixed with 'JETPACK_PROVISIONING_MODULE_ERROR' to allow for easy searching of these logs.

