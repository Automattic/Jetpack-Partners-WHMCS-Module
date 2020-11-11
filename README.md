# WHMCS Jetpack Module

This module is intended to assist Jetpack Hosting partners in managing Jetpack plans for their users. The module options are available when a product is created that impleents the Jetpack WHMCS provisioning module in the list of available modules.

Once purchased the Jetpack license can be issued based on one of the 4 available options in WHMCS when creating a new product that involves a module for provisioning(3 Automatic provisioning options and the option to not provision under Module Settings when creating a product).

The module also allows for hosts to manually manage the plan from the Clients area in WHMCS when needed once the client has placed the order for the product. The product will be listed in the Clients area of WHMCS under the Products/Services tab for the specific client. Module functions include both automattic license issuing on checkout as well as the option to both issue and revoke a license using the Create and Terminate options available with the module.

## Getting Started

This documentation includes information for managing the WHMCS module.  If you’d like to become a Jetpack Hosting Partner please take a look at the [Jetpack Hosting Partner Information Page](https://jetpack.com/for/hosts/) for more information and to get started. In order to use the module a valid WHMCS license as well as a Jetpack Hosting Partner account are required. To get started with WHMCS please visit https://www.whmcs.com/.

## Installation/Setup

To install the module upload the `/modules/servers/jetpack` directory in this respository to the `/modules/servers` directory of your WHMCS installation. A server is not required for the module to function so the module will not be listed in the WHMCS Servers under the Setup tab for Products/Servers. Once uploaded to WHMCS the module is ready for use when making a new product in WHMCS.

## Usage

To use the module create a new product in one of your existing product groups. As part of the product creation process in the Module Settings tab select the “Jetpack by Automattic” module to get started. You will need to have an established Jetpack Hosting Partner account and your partner API token will be requested in order to utilize the Jetpack licensing API.

## Configuration Options
The module has 3 configuration options
- API Token: Your API token mentioned above which is required for using the Jetpack licensing API.
- Jetpack Product: The Jetpack product that will be tied to the product being created/sold in WHMCS
- Licensing Table: In order to store issued Jetpack Licenses a custom database table is created as part of the module setup. If there is a problem creating the table a warning will be displayed. Once the issue is resovled please reattempt creating the table.


**Please Note:**
As part of the the product creation for WHMCS you may use any of the 4 options under Module Settings for setting up the product. The modules Create functionality will automatically be called an account provisioned if any of the first 3 options are selected.

In the Clients Tab of WHMCS admin under Products/Services the module currently provides functionality for Create and Terminate for Jetpack plans. If you do not setup the product to be automatically provisioned on checkout you will be able perform either of these actions for your users.

## Additional Information/Troubleshooting

- Most errors that occur when a user is checking out will be logged in the WHMCS Activity Log and prefixed with 'JETPACK_PROVISIONING_MODULE_ERROR' to allow for easy searching of these logs.

