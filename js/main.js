function getJson(url, callback) {
  var xhr = new XMLHttpRequest();
  xhr.open("get", url);

  xhr.onload = function(ev) {
    var footballData = JSON.parse(ev.target.response);
    callback(footballData);
  };
  xhr.send();
}