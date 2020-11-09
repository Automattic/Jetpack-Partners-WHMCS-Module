<?php

namespace Jetpack;

use WHMCS\Database\Capsule;

class JetpackLicenseManager
{
    /**
     * Save a new License for a product prov isioned through the Jetpack Licensing API on a WHMCS order
     *
     * @param integer $order_id THe WHMCS order id.
     * @param integer $product_id The WHMCS product id.
     * @param string $license_key The Jetpack product license.
     * @param string $licnese_issued_at The date the license was issued at supplied by the Jetpack Licensing API.
     * @return void
     */
    public function saveLicense(int $order_id, int $product_id, string $license_key, string $licnese_issued_at)
    {
        Capsule::table('jetpack_product_licenses')->insert(
            [
                'order_id' => $order_id,
                'product_id' => $product_id,
                'license_key' => $license_key,
                'issued_at' => $licnese_issued_at,
            ]
        );
    }

    /**
     * Update a license record with a revoked at time provided by the Jetpack licensing API when
     * a license i reovked
     *
     * @param string $licnese_id The license id to update
     * @param string $revoked_at The time supplied by the API when the license was revoked
     * @return void
     */
    public function revokeLicense(string $licnese_id, string $revoked_at) {
        Capsule::table('jetpack_product_licenses')
        ->where([ 'id' => $licnese_id])
        ->update(['revoked_at' => $revoked_at]);
    }

    /**
     * Find an active licnese for a WHMCS order for a jetpack prdouct
     *
     * @param array WHMCS $params
     * @return StdObject
     */
    public function findActiveLicense(int $order_id, int $product_id)
    {
        return Capsule::table('jetpack_product_licenses')
        ->where(
            [
                'order_id' => $order_id,
                'product_id' => $product_id,
                'revoked_at' => null,
            ]
        )
        ->first();
    }

    /**
     * Get the license key for an active license for a jetpack product
     *
     * @return stirng The license key or "No License Key Found"
     */
    public function getLicenseKey(int $order_id, int $product_id)
    {
        $license = $this->findActiveLicense($order_id, $product_id);
        return isset($license->license_key) ? $license->license_key : 'No License Key Found';
    }
}
