var client = new ClientJS();
var fingerprint = client.getFingerprint();
document.cookie = 'fingerprint='+fingerprint;
var clientBrowser = client.getBrowser();
var clientDevice = 'null';
if (client.isMobile())
  clientDevice = 'mobile';
else
  clientDevice = 'desktop';

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
        nbImage: count,
        device: clientDevice,
        browser: clientBrowser
      },
      dataType: 'json',
      success: function (result) {
        console.log({Success: "Ajax sent"});
        console.log(result);
      },
      error: function (result) {
        console.log({Error: "Ajax not sent"});
        console.log(result);
      }
    });
  });
});