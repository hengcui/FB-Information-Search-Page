<?php 
    header("Access-Control-Allow-Origin: *");
    require_once __DIR__ . '/php-graph-sdk-5.0.0/src/Facebook/autoload.php';
    date_default_timezone_set('UTC');
  
    $fb = new Facebook\Facebook([
          'app_id' => '1880482808853670',
          'app_secret' => '2e72f0156151c7ba580b1fc343c911d1',
          'default_graph_version' => 'v2.8',
          'default_access_token' => 'EAAauSgXT9KYBAJlqS8FDhEGKu93omxKcakvCym6lx7B7hQtFmUiyZCcIcZCb2W2NPc5UHrUWuwwySZCzZBgp0u64KVsJSHZAkHrmjk2HNGHSJyJjRiRHiK5qkKtZBjenyafRKRXWz5ZAXnvIlZBmb8ZALpwoXwoZB5W87pdxmHxpJgXgZDZD'
      ]);
    $token = 'EAAauSgXT9KYBAJlqS8FDhEGKu93omxKcakvCym6lx7B7hQtFmUiyZCcIcZCb2W2NPc5UHrUWuwwySZCzZBgp0u64KVsJSHZAkHrmjk2HNGHSJyJjRiRHiK5qkKtZBjenyafRKRXWz5ZAXnvIlZBmb8ZALpwoXwoZB5W87pdxmHxpJgXgZDZD';
    
   if(isset($_GET["q"]) && isset($_GET["type"])){
            
            $keyword = $_GET["q"];
            $type = $_GET["type"];
            $json =file_get_contents("https://graph.facebook.com/v2.8/search?q=".$keyword."&type=".$type."&fields=id,name,picture.width(700).height(700)&limit=10&access_token=".$token);
            echo $json; 
        }

//        else if(isset($_GET["key"]) && isset($_GET["type"]) && $_GET["type"] == 'page'){
//            $keyword = $_GET["key"];
//            $type = $_GET["type"];
//            $json =file_get_contents("https://graph.facebook.com/v2.8/search?q={$keyword}&type=page&fields=id,name,picture.width(700).height(700)&limit=10&access_token={$token}");
//            echo $json; 
//            
//        }
//
//        else if(isset($_GET["key"]) && isset($_GET["type"]) && $_GET["type"] == 'event'){
//            $keyword = $_GET["key"];
//            $type = $_GET["type"];
//            $json =file_get_contents("https://graph.facebook.com/v2.8/search?q={$keyword }&type=event&fields=id,name,picture.width(700).height(700)&limit=10&access_token={$token}");
//            echo $json;  
//        }
//
//        else if(isset($_GET["key"]) && isset($_GET["type"]) && $_GET["type"] == 'place'){
//            $keyword = $_GET["key"];
//            $type = $_GET["type"];
//            $lat=$_GET["lat"];
//            $lon=$_GET["lon"];
//            $json =file_get_contents("https://graph.facebook.com/v2.8/search?q={$keyword }&type=place&fields=id,name,picture.width(700).height(700)&center=".$lat.",".$lon."&limit=10&access_token={$token}");
//            echo $json;  
//        }
//
//        else if(isset($_GET["key"]) && isset($_GET["type"]) && $_GET["type"] == 'group'){
//            $keyword = $_GET["key"];
//            $type = $_GET["type"];
//            $json =file_get_contents("https://graph.facebook.com/v2.8/search?q={$keyword }&type=group&fields=id,name,picture.width(700).height(700)&limit=10&access_token={$token}");
//            echo $json;  
//        }
//
//        else if(isset($_GET["id"])){
//            $id = $_GET["id"];
//            $json =file_get_contents("https://graph.facebook.com/v2.8/{$id}?fields=id,name,picture.width(700).height(700),albums.limit(5){name,photos.limit(2){name,picture}},posts.limit(5){created_time,message}&limit=10&access_token={$token}");
//            echo $json;  
//        }
?>