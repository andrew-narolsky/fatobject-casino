/**
 * @typedef {Object} FocImportGlobals
 * @property {string} ajax_url
 * @property {string} nonce
 * @property {string} nonce_reset
 */

/**
 * @type {FocImportGlobals}
 */
const FOC_IMPORT = window.FOC_IMPORT;

/**
 * @typedef {Object} FocFrontendGlobals
 * @property {string} ajax_url  AJAX endpoint (admin-ajax.php)
 * @property {string} nonce     Frontend AJAX nonce
 */

/**
 * Global frontend object localized from PHP via wp_localize_script().
 *
 * @type {FocFrontendGlobals}
 */
const FOC_FRONTEND = window.FOC_FRONTEND;