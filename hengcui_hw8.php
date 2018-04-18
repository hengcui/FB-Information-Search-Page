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
    
    if(isset($_GET['q']) && isset($_GET['type']) && isset($_GET['index'])){
        $q = $_GET['q'];
        $type = $_GET['type'];
        $index = $_GET['index'];
        
        echo $q;
        echo $type;
        if($type == 'event'){
            if(isset($_GET['lat']) && isset($_GET['lng'])){
                $lat = $_GET['lat'];
                $lng = $_GET['lng'];
    
                $request = $fb->request('GET','/search', ['q' => $q, 'type' => $type, 'center' => $lat.",".$lng, 'fields' => 'id,name,picture.width(700).height(700)']);
            }
        }else{
            $request = $fb->request('GET','/search', ['q' => $q, 'type' => $type, 'fields' => 'id,name,picture.width(700).height(700)']);
        }
        
        $response = $fb->getClient()->sendRequest($request);
        $json = $response->getGraphEdge();
        
        for($i=0;$i < $index;$i++){
            $json = $fb->next($json);
        }
        
        echo $json;
    }else if(isset($_GET['id'])){
        $id = $_GET['id'];
        $request = $fb->request('GET',$id, ['fields' => 'id,name,picture.width(700).height(700),albums.limit(5){name,photos.limit(2){name, picture}},posts.limit(5)']);
        $response = $fb->getClient()->sendRequest($request);
        $json = $response->getGraphNode();
        echo $json;
    }
?>