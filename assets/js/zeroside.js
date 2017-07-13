/**
 * ZeroSide
 */

// Getting elements
var _submit = document.getElementById('submit'),
    _file = document.getElementById('actual-upload');

// Query extractor
function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

// Notification system
var notify = function(msg, status) {
    var start = '<div class="notification ' + status + '">';
    var end = '</div>';
    var result = start + msg + end;
    document.getElementById('notibox').innerHTML = result;
};

// Checking for available URL
var check = function(val) {
    var http = new XMLHttpRequest();
    var url = "/api/check";
    var params = "id=" + val;
    http.open("POST", url, true);

    //Send the proper header information along with the request
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    http.onreadystatechange = function() { //Call a function when the state changes.
        if (http.readyState == 4 && http.status == 200) {

            var message = document.getElementById("message");
            var json = JSON.parse(http.responseText);

            if (json.code == 200) {
                message.innerText = "This URL is available";
            } else {
                message.innerText = "This URL is already taken";
            }

        }
    };
    http.send(params);
};

// Update progress
function updateProgress(evt) {
    if (evt.lengthComputable) { // evt.loaded the bytes the browser received
        // evt.total the total bytes set by the header
        // jQuery UI progress bar to show the progress on screen
        var percentComplete = ((evt.loaded / evt.total) * 100).toFixed(0);
        document.getElementById("title").innerHTML = 'Upload -> ' + percentComplete + "%";
        document.getElementById('progress').value = percentComplete;

        if (percentComplete >= 100) {
            document.getElementById("title").innerHTML = 'ZeroSide: Anonymous File Sharing';
        }
    } else {
        console.log('Error progress bar');
    }

}

// Upload form
var upload = function() {

    if (_file.files.length === 0) {
        return;
    }

    var data = new FormData();
    data.append('SelectedFile', _file.files[0]);

    var downurl = document.getElementById('downurl').value;

    var e = document.getElementById("expiration");

    data.append('downurl', downurl);
    data.append('expiration', e.options[e.selectedIndex].value);

    var request = new XMLHttpRequest();
    request.upload.onprogress = updateProgress;

    request.onreadystatechange = function() {
        if (request.readyState == 4) {
            if (request.readyState == 4 && request.status == 200) {

                var json = JSON.parse(request.responseText);
                console.log(json);

                if (json.status == 'error') {
                    notify("Upload failed, please retry", "error");
                } else {
                    notify(json.data, "success");
                    document.getElementById('staturl').value = "https://www.zeroside.co/s/" + json.stat;
                }

            }
        }
    };

    request.open('POST', '/api/upload');
    request.send(data);
};


document.addEventListener("DOMContentLoaded", function(event) {

    // Upload button tweak
    document.getElementById('upload').addEventListener('click', function() {
        document.getElementById('actual-upload').click();
    });

    // Send file
    _submit.addEventListener('click', upload);

    // Check redirections
    if(getParameterByName("r") == 404){
        notify("This page does not exists or expired.", "is-danger");
    } else if (getParameterByName("r") == "404d"){
        notify("This download is invalid. <br>Please retry later", "is-warning");
    } else if (getParameterByName("r") == 500){
        notify("Server error<br>Please retry later or contact administrator (via GitHub issues)", "is-danger");
    }
});