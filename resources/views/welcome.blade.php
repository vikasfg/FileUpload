<!doctype html>
<html lang="{{ app()->getLocale() }}">
   <head>
       <meta charset="utf-8">
       <meta http-equiv="X-UA-Compatible" content="IE=edge">
       <meta name="viewport" content="width=device-width, initial-scale=1">
       <title>Laravel S3</title>
       <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
       <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
       <style>
           body, .card{
               background: #ededed;
           }
           .progress .bar{
             background: green;
           }
           .none{
            display: none;
           }
           .progressBar{
            background: green;
           }
       </style>
   </head>
   <body>
       <div class="container">
           <div class="row pt-5">
               <div class="col-sm-12">
                   @if ($errors->any())
                       <div class="alert alert-danger">
                           <button type="button" class="close" data-dismiss="alert">×</button>
                           <ul>
                               @foreach ($errors->all() as $error)
                                   <li>{{ $error }}</li>
                               @endforeach
                           </ul>
                       </div>
                   @endif
                   @if (Session::has('success'))
                       <div class="alert alert-info">
                           <button type="button" class="close" data-dismiss="alert">×</button>
                           <p>{{ Session::get('success') }}</p>
                       </div>
                   @endif
               </div>
               <div class="col-sm-8">
                   @if (count($images) > 0)
                       <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                           <div class="carousel-inner">
                               @foreach ($images as $image)
                                   <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                       <iframe class="d-block w-100" style = "height: 600px;width:700px;border: none"src="{{ $image['src'] }}" alt="First slide"></iframe>
                                       <div class="carousel-caption">
                                           <form action="{{ url('images/' . $image['name']) }}" method="POST">
                                               {{ csrf_field() }}
                                               {{ method_field('DELETE') }}

                                               <button type="submit" class="btn btn-default">Remove</button>
                                           </form>
                                       </div>
                                   </div>

                               @endforeach
                           </div>
                           <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
                               <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                               <span class="sr-only">Previous</span>
                           </a>
                           <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
                               <span class="carousel-control-next-icon" aria-hidden="true"></span>
                               <span class="sr-only">Next</span>
                           </a>
                       </div>

                   @else
                       <p>Nothing found</p>
                   @endif
               </div>
               <div class="col-sm-4">
                   <div class="card border-0 text-center">
                       <form id="awsImageUpload" data-link="{{ url('/images') }}" class="form-horizontal">
                           {{ csrf_field() }}

                           <div class="form-group">
                               <input type="file" name="image[]" id="image" multiple required>
                           </div>
                           <div class="imgItemCont">
                             
                           </div>

                           <div class="form-group">
                               <button type="submit" class="btn btn-primary">Upload</button>
                           </div>
                       </form>
                          
                            <div class="bar"></div >
                            <div class="percent" style="display:none">

                                <div class="mover"></div>
                            </div >
                        </div>

                          <progress max=”100” value=”0”></progress>

                        <div id="status"></div>
                        <div id="results"></div>

                   </div>
               </div>
               <div class="imgPrtype none">
                 <div class="imgName"></div>
                 <div class="progressBar">
                   
                 </div>
               </div>
           </div>
       </div>
       <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
       <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
    <script src="https://sdk.amazonaws.com/js/aws-sdk-2.1.12.min.js"></script>

       <script>

          AWS.config.region = 'ap-south-1'; // 1. Enter your region
          AWS.config.credentials = new AWS.CognitoIdentityCredentials({
              IdentityPoolId: 'ap-south-1:a5a0492d-1603-48ce-aad9-b9ec1449b626' // 2. Enter your identity pool
          });
          AWS.config.credentials.get(function(err) {
              if (err) alert(err);
              //console.log(AWS.config.credentials);
          });
          var bucketName = 'laravels3upload'; // Enter your bucket name
          var bucket = new AWS.S3({
              params: {
                  Bucket: bucketName
              }
          });

           $(document).ready(function(){
                $("#awsImageUpload button").on("click",function(e){
                    var frm = $(this).closest("form");
                    if (!$(frm)[0].checkValidity()) {
                      return;
                    }
                    e.preventDefault();
                    var link = frm.data("link");
                    var fileChooser = document.getElementById('image');
                    var file = fileChooser.files;
                    var l =file.length;
                     console.log(file);
                    // var form_data =  new FormData($(this).closest('form')[0]);
                    var name =[];
                     var loaded = [];
                    var sizeTotal = 0;
                    for (var i = 0; i < l; i++) {
                      name.push(file[i].name);
                      loaded[file[i].name] = 0;
                      sizeTotal += file[i].size;
                    }
                    uploadS3(file,sizeTotal,loaded);
                     // console.log('tot'+sizeTotal);
                    DBStore(name,link);
                });
                $("#awsImageUpload #image").on("change",function(){
                  var thisfile = $(this).get(0).files;
                  var fileLength = thisfile.length;
                  
                  
                  for(var i=0;i<fileLength;i++){
                    var item = $(".imgPrtype").clone().removeClass("imgPrtype none").addClass("imgItem");
                    item.find(".imgName").text(thisfile[i].name);
                    item.appendTo(".imgItemCont");
                  }
                });
           });
            var results = document.getElementById('results');
           var d = new Date();
           var n = d.getTime();
           function DBStore(file_name,link){
          
                var bar = $('.progress .bar');
                var percent = $('.progress .percent');
                var status = $('#status');
          
                $.ajax({
                  // xhr: function() {
                  //   var xhr = new window.XMLHttpRequest();
                  //   xhr.upload.addEventListener("progress", function(evt) {
                  //     if (evt.lengthComputable) {
                  //       var percentComplete = evt.loaded / evt.total;
                  //       percentComplete = parseInt(percentComplete * 100);
                  //       bar.width(percentComplete+"%");
                  //       percent.text(percentComplete+"%");
                  //       if (percentComplete === 100) {
                  //           status.text("Upload Successfully");
                  //       }
                  //     }
                  //   }, false);
                  //   return xhr;
                  // },
                 
                  type: 'POST',
                    url:  link,
                    data: {data:JSON.stringify(file_name)},
                    dataType : 'json',
                    // contentType: false,       // The content type used when sending data to the server.
                    // cache: false,             // To unable request pages to be cached
                    // processData:false,
                  success: function(result) {
                    //alert(result);
                   // console.log(file_name);
                  }
                });
             }

             function uploadS3(file,sizeTotal,loaded) {
              var loadedTotal = 0;

              for (let i = 0, filesLen = file.length; i < filesLen; i++) {

                var fn = file[i].name;
               
                if (file) {
                  var objKey = 'images/' + n+file[i].name;
                  var params = {
                    Key: objKey,
                    Bucket: bucketName,
                    ContentType: file[i].type,
                    Body: file[i],
                    ACL: 'public-read'
                  };
                  bucket.putObject(params, function(err, data) {
                    if (err) {
                        status.innerHTML = 'ERROR: ' + err;
                    } else {
                      //  listObjs(); // this function will list all the files which has been uploaded
                        //here you can also add your code to update your database(MySQL, firebase whatever you are using)
                    }
                  }).on('httpUploadProgress', function (progress) {

                  loaded[this.params.key] = progress.loaded;
                  var loadedTotal =0;
                  for (var j in loaded) {
                      loadedTotal = loaded[j];
                  }
                  
                  var uploaded = Math.round(loadedTotal / progress.total * 100);
    
                  document.getElementsByTagName("progress")[0].setAttribute("value", uploaded);
                  results.innerHTML = uploaded;
                  console.log(loadedTotal+'up'+uploaded+'loaded'+progress.loaded);
                  $(".imgItemCont .imgItem:eq("+i+") .progressBar").text(uploaded);
                  //status.text("Upload Successfully");
                   
                  });
                  }
                  else {
                      status.innerHTML = 'Nothing to upload.';
                  }
                } 
              }

              function listObjs() {
                  var prefix = 'images';
                  bucket.listObjects({
                      Prefix: prefix
                  }, function(err, data) {
                      if (err) {
                          results.innerHTML = 'ERROR: ' + err;
                      } else {
                          var objKeys = "";
                          data.Contents.forEach(function(obj) {
                              objKeys += obj.Key + "<br>";
                          });
                          results.innerHTML = objKeys;
                      }
                  });
              }
       </script>
   </body>
</html>