$(document).ready(function () {
  console.log('document ready');
  var pathArray = location.href.split( '/' );
  var protocol = pathArray[0];
  var host = pathArray[2];
  var baseUrlClient = protocol + '//' + host;
  $.getScript(baseUrlClient + '/modules/paygreen/views/js/client.min.js', function () {
  console.log('base url : ' + baseUrlClient);
    var client = new ClientJS();
    var fingerprint = client.getFingerprint();
    document.cookie = 'fingerprint=' + fingerprint;
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

    start = new Date().getTime();

    $(window).unload(function () {
      console.log('unload');
      end = new Date().getTime();
      $.ajax({
        url: baseUrlClient + '/modules/paygreen/paygreenAjax.php',
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
          console.log({ Success: "Ajax sent" });
          console.log(result);
        },
        error: function (result) {
          console.log({ Error: "Ajax not sent" });
          console.log(result);
        }
      });
    });
  });
});
