<?php
$data = '<html xmlns="http://www.w3.org/1999/html"><head><title>TPCT Youtube Downloader</title><link rel="icon" type="image/png" href="https://cdn0.iconfinder.com/data/icons/large-glossy-icons/512/Spy.png"/><style>
    *{
        outline: none;
    }
    img{
    max-height: 30%;
    max-width: 15%;
    }
    a{
    text-decoration: none;
    text-decoration-color: black;
    color: greenyellow;
    }
    a:active{
    color: greenyellow;
    }
    #result #video_info{
       margin-left: 4.5%;
       border: 1px solid greenyellow;
       border-radius: 5px;
       max-height: 40%;
       max-width: 90%;
       width: auto;
       overflow: auto;
       padding:5px;
       margin-top: 5px;
    }
    #result #res{
       border: 1px solid greenyellow;
       border-radius: 5px;
       max-height: 20%;
       max- width: 90%;
       overflow: auto;
       padding:5px;
       margin-top: 5px;
    }
    #result{
        padding:5px ;
        margin-top: 5px;
        width: 99%;
        height: 89%;
        max-width: 99%;
        max-height: 89%;
        border-top: 1px  solid greenyellow;
        border-radius: 5px;
        overflow: auto;
    }
    pre{
        word-wrap: break-word;
        word-break: break-all;
        max-height: 93%;
        max-width: 99%;
        overflow: auto;
        border: transparent solid 1px;
    }
    form{
    }
    textarea{resize: none;}
    body{
        text-align: center;
        background-size: cover;
        background: url("http://fastpayads.s3.amazonaws.com/blog/wp-content/uploads/2016/02/Hackers.jpg");
    }
    #Youtube_url{
        background-color: black;
        color: lawngreen;
        border: 1px greenyellow solid;
        border-radius: 5px;
        padding: 2px;
    }
    #number{
        background-color: black;
        color: lawngreen;
        border: 1px greenyellow solid;
        border-radius: 5px;
        padding: 2px;
        max-width: 70px;
    }
    #runtime{
        display: inline;
        background-color: black;
        color: lawngreen;
        border: 1px greenyellow solid;
        border-radius: 5px;
        padding: 2px;
        max-width: 70px;
    }
    #submit{
        background-color: black;
        color: lawngreen;
        border: 1px greenyellow solid;
        border-radius: 5px;
        display: inline;
        padding: 3px;
    }
    fieldset{
        position: absolute;
        color: lawngreen;
        border: 1px greenyellow solid;
        border-radius: 5px;
        text-align: center;
        background: black;
        width: 85%;
        height: 80%;
        max-width: 70%;
        max-height: 60%;
    }
    hr{
        line-height: 7px;
        display: block;
        color:transparent;
        border: none;
    }
    legend{
        text-align: center;
        margin-left: 32%;
    }
</style></head>
<body>
<fieldset>
    <legend>
        Youtube Downloader
    </legend>
   <center><input placeholder="Youtube Video Url" type="text" name="Url" id="Youtube_url" />
        <input type="submit" id="submit" name="submit" value="Get Videos" onclick="get_vid();"/><label id=\'count\'></label> <hr/></center>
    <div id="result"></div>
</fieldset>
<script>
    function get_vid(){
       $data = document.getElementById("Youtube_url").value;
       $result = document.getElementById("result");
       $result.innerHTML = "";
       var xhttp = new XMLHttpRequest();
       xhttp.onreadystatechange = function() {
       if (xhttp.readyState == 4 && xhttp.status == 200) {
         $data = JSON.parse(xhttp.responseText);
         $urls = $data[0];
         $datas = $urls["video data"];
         $div = document.createElement("div");
         $div.id = "video_info";
         $result.appendChild($div);
         $video_data = document.getElementById("video_info");
         for (data in $datas){
             if (data.hasOwnProperty){
                 if (data == "image"){
                     $img = document.createElement("img");
                     $img.src = $datas[data];
                     $video_data.appendChild($img);
                 }else{
                     $div = document.createElement("div");
                     $div.innerHTML = data+":"+$datas[data];
                     $video_data.appendChild($div);
                 }
             }
         }
         delete $urls["video data"];
         for ($url in $urls){
             if ($url.hasOwnProperty){
                 if ($urls[$url].length == 1){
                     $div = document.createElement("div");
                     $div.id = "res";
                     $div.innerHTML = $urls[$url][0];
                     $result.appendChild($div);
                 }else{
                     
                 }
             }
         }
        }
       };
       xhttp.open("GET", "download.php?get_vid="+$data, true);
       xhttp.send();
    }
</script><script>
    function center() {
        var f = document.getElementsByTagName(\'fieldset\')[0],
                width = f.offsetWidth,
                dwidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth,
                height = f.offsetHeight,
                dheight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
        if (f.style.left != Math.floor((dwidth-width)/2)) {
            f.style.left = Math.floor((dwidth - width) / 2);
            if (f.style.top != Math.floor((dheight - height) / 2)) {
                f.style.top = Math.floor((dheight - height) / 2);
                setTimeout("center()", 200);
            }
            else {
                setTimeout("center()", 200);
            }
        }
        else{
            if (f.style.top != Math.floor((dheight-height)/2)){
                f.style.top = Math.floor((dheight-height)/2);
                setTimeout("center()", 200);}
            else{
                setTimeout("center()", 200);
            }
            }
        }
    center();
</script></body>
</html>';
echo $data;
