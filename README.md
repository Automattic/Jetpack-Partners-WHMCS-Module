# WHMCS Jetpack Module

This module is intended to assist Jetpack Hosting partners in managing Jetpack plans for their users. The package includes a WHMCS Addon module and a Provosioning module. The Addon module is intended to assist a host with setting up 4 products for each available Jetpack Plan which can then be sold individual or bundled with existing products.

The module also allows for hosts to manually manage the plan from the Clients area in WHMCS when needed once the client has placed the order for the product. The product will be listed in the Clients area of WHMCS under the Products/Services tab for the specific client. Module functions include both automatic provisioning on checkout as well as manual provisioning and plan cancellation from the WHMCS Clients management.

## Getting Started

This documentation includes information for managing the WHMCS Addon and Provisioning module. For documentation on the Jetpack Hosting Partners Program please refer to the [Jetpack Hosting Partner Docs](https://github.com/Automattic/jetpack/tree/master/docs/partners). If you’d like to become a Jetpack Hosting Partner please take a look at the [Jetpack Hosting Partner Information Page](https://jetpack.com/for/hosts/) for more information and to get started. In order to use the module a valid WHMCS license as well as a Jetpack Hosting Partner account are required. To get started with WHMCS please visit https://www.whmcs.com/.

## Installation/Setup

To install the module upload the `/modules/servers/jetpack` directory in this respository to the `/modules/servers` directory of your WHMCS installation and the modules `/modules/addons/jetpack` to the `/modules/addons` directory. Once uploaded to WHMCS the modules are ready for use in WHMCS.

## Usage

### Addon Module
To use the Addon modules select the Addon Modules option from the Setup options in the WHMCS admin navigation menu. If the addon module was correctly installed it will be listed in the available modules. You will need to select the activate option after which you'll need to select the configure option and enter in your provided partner id and partner secret. You will also need to select at least one option in the Access Control list otherwise you will not be able to use the module.

The addon module will then be listed under the Addons tab in the WHMCS admin navigation menu. Navigate to the module and you will have options available to validate your partner credentials and create jetpack products.

WHMCS requires a product group in order to create a product so if you do not yet have one created you will be directed to do so. Once a product group is available select the create option. This will create 4 new products for each available Jetpack plan. By default the products are hidden and will not be available for purchase unless you configure them otherwise. You can edit each product as you would a normal product in WHMCS by clicking on the name in the addon module settings.

You may add each product to an already existing product bundle which will allow and Jetpack plan to be included with a purchase at checkout.

**Custom Fields -**
The custom field names are required by the provisioning module to function correctly. **These should not be edited.**


### Provisioning Module

The provisioning module is also added as the default module with your partner id and secret for provisioning plans. These should also not be edited unless your partner id or secret changes.

In the Clients Tab of WHMCS admin under Products/Services the module currently provides functionality for Create and Terminate for Jetpack plans. If you do not setup the product to be automatically provisioned on checkout you will be able perform either of these actions for your users.

## Additional Information/Troubleshooting

- The jetpack_provisioning_details field will either contain a URL that a user can use to complete the setup for a provisioned jetpack plan or a message indicating that a plan is waiting. The plan will be waiting in the event that the domain that was supplied for provisioning does not resolve likely because it was also just purchased.

- The module will relay issues that are preventing it from functioning properly that are due to incorrect set up of the product whenever manual provisioning is attempted. Examples of these include an incorrectly entered required field or a missing hosting partner information like the client id or secret.

- Most errors that occur when a user is checking out will be logged in the WHMCS Activity Log and prefixed with 'JETPACK MODULE' to allow for easy searching of these logs.

- Other uncommon errors typically associated with failures in the API request process for provisioning or terminating a plan will require that Module logging be enabled in the Logs section of the Utilities tab for WHMCS. The module provisioning failure should still be logged in the Activity Log however to indicate the failure but will not include the request/response details. Please disable module logging once the error is captured.
