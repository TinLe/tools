var http_request = false;

function makeRequest(url, parameters, callback) {
    http_request = false;
    if (window.XMLHttpRequest) { // Mozilla, Safari,...
        http_request = new XMLHttpRequest();
        if (http_request.overrideMimeType) {
            // set type accordingly to anticipated content type
            //http_request.overrideMimeType('text/xml');
            http_request.overrideMimeType('text/html');
        }
    } else if (window.ActiveXObject) { // IE
        try {
            http_request = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                http_request = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {}
        }
    }
    if (!http_request) {
        alert('Cannot create XMLHTTP instance');
        return false;
    }
    http_request.onreadystatechange = callback;
    http_request.open('GET', url + parameters, true);
    http_request.send(null);
}

function get(obj,actionForm, callback) {
    var getstr = "?";
    
    for (var i=0; i<obj.elements.length; i++) {
        if (obj.elements[i].tagName == "INPUT") {
            if (obj.elements[i].type == "hidden") {
                getstr += obj.elements[i].name + "=" + obj.elements[i].value + "&";
            }
        }
        if (obj.elements[i].tagName == "SELECT") {
            var sel = obj.elements[i];
            for (var j = 0; j < sel.options.length; j++)
                if (sel.options[ j ].selected)
                    getstr += sel.name + "=" + sel.options[j].value + "&";
        }

    }
    makeRequest(actionForm, getstr, callback);
}