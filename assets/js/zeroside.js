/**
 * ZeroSide
 */

var _submit = document.getElementById('submit'), 
    _file = document.getElementById('actual-upload'), 
    _progress = document.getElementById('progress');

var uniqid = function () {
    return (new Date().getTime() + Math.floor((Math.random() * 10000) + 1)).toString(16);
};

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

var upload = function(){

    if(_file.files.length === 0){
        return;
    }

    var data = new FormData();
    data.append('SelectedFile', _file.files[0]);

    var request = new XMLHttpRequest();
    request.onreadystatechange = function(){
        if(request.readyState == 4){
            try {
                var resp = JSON.parse(request.response);
            } catch (e){
                var resp = {
                    status: 'error',
                    data: 'Unknown error occurred: [' + request.responseText + ']'
                };
            }
            console.log(resp.status + ': ' + resp.data);
        }
    };

    request.upload.addEventListener('progress', function(e){
        _progress.value = Math.ceil(e.loaded/e.total) * 100;
    }, false);

    request.open('POST', '/api/upload');
    request.send(data);
};

document.addEventListener("DOMContentLoaded", function (event) {

    document.getElementById('upload').addEventListener('click', function(){
        document.getElementById('actual-upload').click();
    });

    var id = uniqid();

    var stat = document.getElementById('staturl');
    stat.value = "https://www.zeroside.co/stat/" + id;

    _submit.addEventListener('click', upload);
});