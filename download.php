<?php
error_reporting(0);
$file_size = function ($bytes)
{
    if ($bytes >= 1073741824)
    {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    }
    elseif ($bytes >= 1048576)
    {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    }
    elseif ($bytes >= 1024)
    {
        $bytes = number_format($bytes / 1024, 2) . ' kB';
    }
    elseif ($bytes > 1)
    {
        $bytes = $bytes . ' bytes';
    }
    elseif ($bytes == 1)
    {
        $bytes = $bytes . ' byte';
    }
    else
    {
        $bytes = '0 bytes';
    }

    return $bytes;
};
$size = function($url = '') {
    global $config;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_HEADER,         true);
    curl_setopt($ch, CURLOPT_NOBODY,         true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT,        10);
    $r = curl_exec($ch);
    foreach(explode("\n", $r) as $header) {
        if(strpos($header, 'Content-Length:') === 0) {
            return explode('Content-Length:', $header)[1];
        }
    }
    return '';
};
$curl_get_data = function($url=''){
    if (filter_var($url, FILTER_VALIDATE_URL))
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
            curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
            $data = curl_exec($ch);
            curl_copy_handle($ch);
            curl_close($ch);
            return $data;
        }else{
            return null;
        }
    }else{return null;}
};
$youtube_url_checker = function ($url=''){
    if (filter_var($url, FILTER_VALIDATE_URL)){
        $rx = '~
     ^(?:https?://)?
     (?:www\.)?
     (?:youtube\.com|youtu\.be)
     /watch\?v=([^&]+)
     ~x';
        if (preg_match($rx, $url)){
            return $url;
        } else{
            return null;
        }
    }
    else{
        $rx = '~
     ^(?:https?://)?
     (?:www\.)?
     (?:youtube\.com|youtu\.be)
     /watch\?v=([^&]+)
     ~x';
        if (preg_match($rx, $url)){
            return $url;
        } else{
            if (strlen($url) >= 11){
                return 'ID:'.$url;
            }else{
                return null;
            }
        }
    }
};
$youtube_video_id = function ($url = ''){
    global $youtube_url_checker;
    $url = $youtube_url_checker($url);
    if ($url){
        if (strpos($url, 'ID:') === false){
            $url = explode('?', $url)[1];
            $url = explode('=', $url);
            $url = $url[array_search('v', $url)+1];
            return $url;
        } else{
            if ($url){
                return explode('ID:',$url)[sizeof(explode('ID:',$url))-1];
            }
            else{
                return null;
            }
        }
    }  else{
        return null;
    }
};
$json_full_info = function($url= ''){
    global $youtube_url_checker, $youtube_video_id, $curl_get_data;
    $url = $youtube_url_checker($url);
    if (strpos($url,'ID:') !== false){
        $data = $curl_get_data('http://www.youtube.com/oembed?url=https://www.youtube.com/watch?v='.explode('ID:',$url)[1].'&format=json');
        if (strpos($data, 'Not Found') === false) {
            $data = json_decode($data, true);
            $image = explode('/', $data['thumbnail_url']);
            $image = join('/', array_slice($image, 0, count($image) - 1, true)) . '/mqdefault.jpg';
            $title = $data['title'];
            $author_name = $data['author_name'];
            $author_url = $data['author_url'];
            $data = [];
            $data = ['image' => $image, 'title' => $title, 'author_name' => $author_name, 'author_url' => $author_url];
            return $data;
        }else{
            return null;
        }
    }
    elseif (strpos($url,'ID:') === false){
        $data = $curl_get_data('http://www.youtube.com/oembed?url='.$url.'&format=json');
        if (strpos($data, 'Not Found') === false) {
            $data = json_decode($data, true);
            $image = explode('/', $data['thumbnail_url']);
            $image = join('/', array_slice($image, 0, count($image) - 1, true)) . '/mqdefault.jpg';
            $title = $data['title'];
            $author_name = $data['author_name'];
            $author_url = $data['author_url'];
            $data = [];
            $data = ['image' => $image, 'title' => $title, 'author_name' => $author_name, 'author_url' => $author_url];
            return $data;
        }else{
            return null;
        }
    }
    else{
        return null;
    }
};
$video_info = function($url = ''){
    global $youtube_url_checker, $youtube_video_id, $curl_get_data, $size, $file_size;
    $url = $youtube_video_id($youtube_url_checker($url));
    if (strpos($url, 'ID:') !==false){
        $url = explode('ID:', $url);
        $url = $url[sizeof($url)-1];
    }
    if ($url) {
        $url = 'http://www.youtube.com/get_video_info?&video_id=' . $url . '';
        $data = $curl_get_data($url);
        $avail_formats = [];
        $filter = function ($data){
            $main = [];
            $data = explode('=', $data);
            foreach ($data as $d){
                $main = array_merge($main, explode('&', $d));
            }
            if (is_array($main[sizeof($main)-1])){
                $main = $main[sizeof($main)-1];
            }
            $status = $main[array_search('status', $main)+1];
            if ($status == 'fail'){
                return false;
            }
            else{
                return true;
            }
        };
        $decode = function ($url){
            $is_encoded = function ($data){
                if (preg_match('~%[0-9A-F]{2}~i', $data)){
                    return true;
                }else{
                    return false;
                }
            };
            while ($is_encoded($url)){
                $url = urldecode($url);
            }
            return $url;
        };
        $i = 0;
        if ($filter($data)){
            parse_str($data);
            foreach(explode(',', $url_encoded_fmt_stream_map) as $data){
                parse_str($data);
                $avail_formats[$i]['itag'] = $itag;
                $avail_formats[$i]['quality'] = $quality;
                $type = explode(';',$type);
                $avail_formats[$i]['type'] = $type[0];
                $url = urldecode($url);
                $avail_formats[$i]['size'] = $file_size($size($url));
                $play_back = 'http://redirector.googlevideo.com/'.explode('.googlevideo.com', urldecode($url))[1];
                $avail_formats[$i]['url'] = $url;
                $avail_formats[$i]['playback'] = $play_back;
                parse_str(urldecode($url));
                $avail_formats[$i]['expires'] = date("G:i:s T", $expire);
                $avail_formats[$i]['ipbits'] = $ipbits;
                $avail_formats[$i]['ip'] = $ip;
                $i++;
            }
            return $avail_formats;
        }else{
            return null;
        }
    }else{
        return null;
    }
};
$encryption = function($data= ''){
    $data = base64_encode($data);
    $encrypted = '';
    foreach(str_split($data) as $d){
        $encrypted .= chr(ord($d)+2);
    }
    return $encrypted;
};
$decryption = function($data = ''){
    $encrypted = '';
    foreach(str_split($data) as $d){
        $encrypted .= chr(ord($d)-2);
    }
    return base64_decode($encrypted);
};
$parser = function($url = ''){
    global $curl_get_data, $youtube_url_checker, $youtube_video_id, $encryption, $json_full_info, $video_info;
    if ($youtube_url_checker($url)){
        $data = $json_full_info($url);
        if (isset($data)) {
            $title = $data['title'];
            $image = $data['image'];
            $author_name = $data['author_name'];
            $author_url = $data['author_url'];
            $data = $video_info($url);
            if (isset($data)) {
                $main = ['video data' => ['title' => $title, 'image' => $image, 'author_name' => $author_name, 'author_url' => $author_url]];
                $x = 0;
                foreach ($data as $d) {
                    switch ($d['quality']){
                        case 'hd720':
                            $d['quality'] = '720p';
                            break;
                        case 'medium' && $x == 0:
                            $d['quality'] = '480p';
                            $x++;
                            break;
                        case 'medium' && $x == 1:
                            $d['quality'] = '360p';
                            $x++;
                            break;
                        case 'small' && $x == 2:
                            $d['quality'] = '240p';
                            $x++;
                            break;
                        case 'small' && $x == 3:
                            $d['quality'] = '144p';
                            $x = 0;
                            break;
                        default:
                            break;
                    }
                    $url = $d['quality'].'['.$d['size'].']' . ' => <a href="download.php?get=' . $encryption($encryption($d['url']) . '&mime='.$d['type'].'&title='.$title).'">' . explode('/',$d['type'])[1] . '</a>';
                    $main[] = [$url];
                }
                return array($main);
            }else{
                return 'Please Enter Valid Youtube Video Url';
            }
        }else{
            return 'Please Enter Valid Youtube Video Url';
        }
    }
    else{
        return 'Please Enter Valid Youtube Video Url';
    }
};
$playlist_checker = function ($url= ''){
    $rx = '~
     ^(?:https?://)?
     (?:www\.)?
     (?:youtube\.com|youtu\.be)
     (?:/watch\?v=([^&]+))?(&list=([^&]+))?
     ~x';
    if (preg_match($rx, $url)){
        return $url;
    }
    elseif (strpos(strtolower($url), 'list') !== false){
        return 'LIST:'.$url;
    }
    elseif (strlen($url) >= strlen('PLviB05QdPlXnjfrtrLBH-qj-9O9sUYfLT')){
        return 'LIST:'.$url;
    }
    else{
        return null;
    }
};
$playlist = function($playlist_url){
    global $playlist_checker, $curl_get_data;
    $playlist_url = $playlist_checker($playlist_url);
    if ($playlist_url){
        if (strpos($playlist_url, "LIST:") !== false){
            $playlist_url = explode('LIST:', $playlist_url)[1];
            $playlist_url = 'https://www.youtube.com/playlist?list='.$playlist_url;
        }
        else{
            parse_str($playlist_url, $main);
            $playlist_url = $main['list'];
        }
        $playlist_url = 'https://www.youtube.com/playlist?list='. $playlist_url;
        $data = $curl_get_data($playlist_url);
        var_dump($data);
    }else{
        return "Please Enter Valid Playlist ID";
    }
};
$query_checker = function(){
    global $decryption, $size, $parser, $playlist, $playlist_checker;
    if (isset($_GET['get'])){
        $data = explode('&',$decryption($_GET['get']));
        $url = $decryption($data[0]);
        $mime = filter_var(explode('=',$data[1])[1]);
        $ext  = str_replace(array('/', 'x-'), '', strstr($mime, '/'));
        $title = explode('=',$data[2])[1].'.'.$ext;
        if (isset($mime, $url, $title)){
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE)
            {
                header('Content-Type: "' . $mime . '"');
                header('Content-Disposition: attachment; filename="' . $title . '"');
                header('Expires: 0');
                header('Content-Length: '.$size($url));
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header("Content-Transfer-Encoding: binary");
                header('Pragma: public');
            }
            else
            {
                header('Content-Type: "' . $mime . '"');
                header('Content-Disposition: attachment; filename="' . $title . '"');
                header("Content-Transfer-Encoding: binary");
                header('Expires: 0');
                header('Content-Length: '.$size($url));
                header('Pragma: no-cache');
            }
            readfile($url);
            exit();
        }
    }
    elseif(isset($_GET['get_vid'])){
        echo json_encode($parser($_GET['get_vid']));
    }
    elseif(isset($_GET['get_playlist'])){}
    else{
         /*echo $playlist('https://www.youtube.com/watch?v=jZervxL950s&index=1&list=PLviB05QdPlXnjfrtrLBH-qj-9O9sUYfLT');*/
        header('Location: index.php');
    }
};
function receiver(){
    global $query_checker, $parser;
    $query_checker();
}
receiver();
