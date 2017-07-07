/**
 * ZeroSide
 */

// Getting elements
var _submit = document.getElementById('submit'), 
    _file = document.getElementById('actual-upload'), 
    _progress = document.getElementById('progress');

// Notification system
var notify = function (msg, status) {
    var start = '<div class="notification ' + status + '">';
    var end = '</div>';
    var result = start + msg + end;
    document.getElementById('notibox').innerHTML = result;
}

// Checking for available URL
var check = function (val) {
    var http = new XMLHttpRequest();
    var url = "/api/check";
    var params = "id=" + val;
    http.open("POST", url, true);

    //Send the proper header information along with the request
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    http.onreadystatechange = function () { //Call a function when the state changes.
        if (http.readyState == 4 && http.status == 200) {

            var message = document.getElementById("message");
            var json = JSON.parse(http.responseText);

            if (json.code == 200) {
                message.innerText = "This URL is available";
            } else {
                message.innerText = "This URL is already taken";
            }

        }
    }
    http.send(params);
};

// Upload form
var upload = function(){

    if(_file.files.length === 0){
        return;
    }

    var data = new FormData();
    data.append('SelectedFile', _file.files[0]);

    var request = new XMLHttpRequest();
    request.onreadystatechange = function(){
        if(request.readyState == 4){
            if (request.readyState == 4 && request.status == 200) {

                console.log(request.responseText);
                var json = JSON.parse(request.responseText);

                if (json.status == 'error') {
                    notify("Upload failed, please retry", "is-danger");
                } else {
                    notify(json.data, "is-success");
                }

            }
        }
    };

    request.upload.addEventListener('progress', function(e){
        var prog = Math.ceil(e.loaded/e.total) * 100;
        console.log(prog)
        _progress.value = prog;
    }, true);

    var downurl = document.getElementById('downurl').value;

    request.open('POST', '/api/upload/' + downurl);
    request.send(data);
};


document.addEventListener("DOMContentLoaded", function (event) {

    // Upload button tweak
    document.getElementById('upload').addEventListener('click', function(){
        document.getElementById('actual-upload').click();
    });

    // Send file
    _submit.addEventListener('click', upload);
});