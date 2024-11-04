/**
 * Paystack checkout js
 *
 */
jQuery(document).ready(function ($) {
  //initialize paystack
  var handler = PaystackPop.setup({
    key: biggidroid_checkout_params.public_key,
    email: biggidroid_checkout_params.email,
    amount: biggidroid_checkout_params.amount,
    //meta
    metadata: {
      custom_fields: [
        {
          first_name: biggidroid_checkout_params.first_name,
          last_name: biggidroid_checkout_params.last_name,
          order_id: biggidroid_checkout_params.order_id
        }
      ]
    },
    //success callback
    onSuccess: function (transaction) {
      //check if transaction is successful
      if (transaction.status === "success") {
        //block the ui
        $.blockUI();
        //redirect to validation url
        window.location.href =
          biggidroid_checkout_params.redirect_url +
          "?paystack_reference=" +
          transaction.reference +
          "&order_id=" +
          biggidroid_checkout_params.order_id;
      } else {
        //alert user
        alert("Payment failed, please try again");
      }
    },
    //error callback
    onClose: function () {
      console.log("====================================");
      console.log("Payment window closed");
      console.log("====================================");
    }
  });

  $("#wc-biggidroid-payment-gateway-button").click((e) => {
    e.preventDefault();
    //open paystack popup
    handler.openIframe();
  });
});
