<!DOCTYPE html>
<html>

<head>
    <title>AWS S3 File Upload</title>
    <script src="https://sdk.amazonaws.com/js/aws-sdk-2.1.12.min.js"></script>
</head>


<body>
    <input type="file" id="file-chooser" multiple />
    <button id="upload-button">Upload to S3</button>
    <progress max=”100” value=”0”></progress>

    <div id="results"></div>
    <script type="text/javascript">
    AWS.config.region = 'ap-south-1'; // 1. Enter your region
    AWS.config.credentials = new AWS.CognitoIdentityCredentials({
        IdentityPoolId: 'ap-south-1:a5a0492d-1603-48ce-aad9-b9ec1449b626' // 2. Enter your identity pool
    });
    AWS.config.credentials.get(function(err) {
        if (err) alert(err);
        console.log(AWS.config.credentials);
    });
    var bucketName = 'laravels3upload'; // Enter your bucket name
    var bucket = new AWS.S3({
        params: {
            Bucket: bucketName
        }
    });
    var fileChooser = document.getElementById('file-chooser');
    var button = document.getElementById('upload-button');
    var results = document.getElementById('results');
    var d = new Date();
    var n = d.getTime();
    button.addEventListener('click', function() {
        var file = fileChooser.files[0];

        if (file) {
            results.innerHTML = '';
            var objKey = 'images/' + n+file.name;
            var params = {
                Key: objKey,
                Bucket: bucketName,
                ContentType: file.type,
                Body: file,
                ACL: 'public-read'
            };
            bucket.putObject(params, function(err, data) {
                if (err) {
                    results.innerHTML = 'ERROR: ' + err;
                } else {
                    listObjs(); // this function will list all the files which has been uploaded
                    //here you can also add your code to update your database(MySQL, firebase whatever you are using)
                }
            }).on('httpUploadProgress', function (progress) {
        var uploaded = parseInt((progress.loaded * 100) / progress.total);
        document.getElementsByTagName("progress")[0].setAttribute("value", uploaded);
    });
        } else {
            results.innerHTML = 'Nothing to upload.';
        }
    }, false);
    
    </script>
</body>

</html>