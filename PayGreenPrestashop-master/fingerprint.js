var client = new ClientJS();
var fingerprint = client.getFingerprint();

var start;
var count = 0;
var clientUrl = window.location.href;

$('img').each(function () {
  ++count;
});
$('script').each(function () {
  ++count;
})

$(document).ready(function () {
  start = new Date().getTime();

  $(window).unload(function () {
    end = new Date().getTime();
    $.ajax({
      url: 'http://local.shop.fr/modules/paygreen/paygreenAjax.php',
      type: 'post',
      async: false,
      data: {
        client: fingerprint,
        startAt: start,
        useTime: (end - start),
        nbImage: count
      },
      dataType: 'json',
      success: function (result) {
        console.log('success');
        console.log(result);
      }, error: function (result) {
        console.log('error');
        console.log(result);
      }
    });
  });
});