function setCookie(name, value, minutes) {
  var expires = "";
  if (minutes) {
    var date = new Date();
    date.setTime(date.getTime() + (minutes * 60 * 1000));
    expires = "; expires=" + date.toUTCString();
  }
  document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function getCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') c = c.substring(1, c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
  }
  return null;
}

function eraseCookie(name) {
  document.cookie = name + '=; Max-Age=-99999999;';
}


function sendJSON(url, params, timeout, callback) {
  var args = Array.prototype.slice.call(arguments, 3);
  var xhr = new XMLHttpRequest();
  xhr.ontimeout = function () {
    console.log("The POST request for " + url + " timed out.");
  };
  xhr.onload = function () {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        console.log('post: ' + url + ' success.');
        callback.apply(xhr, args);
      } else {
        console.log(xhr.statusText);
      }
    }
  };
  xhr.open("POST", url, true);
  xhr.timeout = timeout;
  xhr.send(params);
}

function add_new_uuid_result() {
  var resp = this.responseText;
  var resp_j = JSON.parse(resp);

  console.log(resp_j);
}


jQuery(document).ready(function ($) {
  if ($('#sigbro_auth--qr_code_sigbromobile').length > 0 && $('#sigbro_auth--session_uuid').length > 0) {
    console.log("SIGRBO AUTH is ready!");
    // page with custom template (our)
    var uuid = $('#sigbro_auth--session_uuid').val();
    var accURL = "sigbro://" + uuid;

    var QRC = qrcodegen.QrCode;
    var qr0 = QRC.encodeText(accURL, QRC.Ecc.HIGH);

    var code = qr0.toSvgString(4);
    var svg = document.getElementById("sigbro_auth--qr_code_sigbromobile");

    svg.setAttribute("viewBox", / viewBox="([^"]*)"/.exec(code)[1]);
    svg.querySelector("path").setAttribute("d", / d="([^"]*)"/.exec(code)[1]);
    svg.style.removeProperty("display");

    // send to API server
    url = "https://random.nxter.org/api/auth/new";

    param_json = { "uuid": uuid };
    param = JSON.stringify(param_json);

    sendJSON(url, param, 3000, add_new_uuid_result);

    // connect to SSE 
    var source = new EventSource('https://random.nxter.org:9040/stream');

    source.addEventListener(uuid, function (event) {
      var data = JSON.parse(event.data);

      try {
        var data2 = JSON.parse(data);
      } catch (err) {
        var data2 = data;
      }

      console.log(data2);

      if (data2.type == 'success' && data2.accountRS) {
        // need to log in
        setCookie('sigbro_uuid', data2.uuid, 15);
        setCookie('sigbro_token', data2.token, 15);

        var redirect_url = window.location.protocol + "//" + window.location.hostname + "/wp-admin"
        console.log("Redirect to: " + redirect_url);
        location.href = redirect_url;
      } else {
        console.log('RESPONSE HAVE NOT DATA');
        alert(data2.message);
      }
    }, false);
  }

});



