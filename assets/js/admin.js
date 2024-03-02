/**
 * BiggiDroid Admin Script
 *
 */
jQuery(document).ready(function ($) {
  /**
   * Init test mode check?
   *
   */
  let biggidroid_payment_mode_check = () => {
    //get the value of the selected option
    var selectedValue = $("#woocommerce_biggidroid_payment_test_mode").val();
    //check if the selected option is 'yes'
    if (selectedValue == "yes") {
      //show the #woocommerce_biggidroid_payment_test_secret_key and #woocommerce_biggidroid_payment_test_public_key
      $("#woocommerce_biggidroid_payment_test_secret_key").closest("tr").show();
      $("#woocommerce_biggidroid_payment_test_public_key").closest("tr").show();
      //hide the #woocommerce_biggidroid_payment_live_secret_key and #woocommerce_biggidroid_payment_live_public_key
      $("#woocommerce_biggidroid_payment_live_secret_key").closest("tr").hide();
      $("#woocommerce_biggidroid_payment_live_public_key").closest("tr").hide();
    } else {
      //show the #woocommerce_biggidroid_payment_live_secret_key and #woocommerce_biggidroid_payment_live_public_key
      $("#woocommerce_biggidroid_payment_live_secret_key").closest("tr").show();
      $("#woocommerce_biggidroid_payment_live_public_key").closest("tr").show();
      //hide the #woocommerce_biggidroid_payment_test_secret_key and #woocommerce_biggidroid_payment_test_public_key
      $("#woocommerce_biggidroid_payment_test_secret_key").closest("tr").hide();
      $("#woocommerce_biggidroid_payment_test_public_key").closest("tr").hide();
    }
  };

  /**
   * On change #woocommerce_biggidroid_payment_test_mode
   *
   */
  $("#woocommerce_biggidroid_payment_test_mode").change(function (e) {
    e.preventDefault();
    biggidroid_payment_mode_check();
  });

  //init
  biggidroid_payment_mode_check();
});
