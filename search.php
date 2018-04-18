<?php
    require_once __DIR__ . '/php-graph-sdk-5.0.0/src/Facebook/autoload.php';
    date_default_timezone_set('UTC');
    $fb = new Facebook\Facebook([
          'app_id' => '1880482808853670',
          'app_secret' => '2e72f0156151c7ba580b1fc343c911d1',
          'default_graph_version' => 'v2.8',
          'default_access_token' => 'EAAauSgXT9KYBAJlqS8FDhEGKu93omxKcakvCym6lx7B7hQtFmUiyZCcIcZCb2W2NPc5UHrUWuwwySZCzZBgp0u64KVsJSHZAkHrmjk2HNGHSJyJjRiRHiK5qkKtZBjenyafRKRXWz5ZAXnvIlZBmb8ZALpwoXwoZB5W87pdxmHxpJgXgZDZD'
      ]);
    
    function parseAddress($loct){
        $json=file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=941+Bloom+Walk,Los+Angeles,+CA+".$loct."&key=AIzaSyA_FDMA_Qfa_yJFhixYt7t2O8v3V8Jxrqk");
    
        $content = json_decode($json, true);
        $position = $content['results'][0]['geometry']['location'];
        
        return $position;
    }
    
    function sendReq($fb, $superVar, $q, $type){
        if($superVar === $_POST){
            if($type == 'place'){
                $location = $superVar["location"];
                $distance = $superVar["distance"];
                $position = parseAddress($location);
                $lat = $position['lat'];
                $lng = $position['lng'];
                
                $request = $fb->request('GET','/search', ['q' => $q, 'type' => $type, 'center' => $lat.",".$lng, 'distance' => $distance, 'fields' => 'id,name,picture.width(700).height(700)']);
            }else if($type == 'event'){
                $request = $fb->request('GET','/search', ['q' => $q, 'type' => $type, 'fields' => 'id,name,picture.width(700).height(700),place']);
            }else{
                $request = $fb->request('GET','/search', ['q' => $q, 'type' => $type, 'fields' => 'id,name,picture.width(700).height(700)']);
            }
        }else if($superVar === $_GET){
            
            $id = $superVar["id"];
            $request = $fb->request('GET',$id, ['fields' => 'id,name,picture.width(700).height(700),albums.limit(5){name,photos.limit(2){name, picture}},posts.limit(5)']);
        }else{
            echo 'Invalid Global Variable for Sending Request!';
            exit;
        }
        
        try {
              // Returns a `Facebook\FacebookResponse` object
             $response = $fb->getClient()->sendRequest($request);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
              echo 'Graph returned an error: ' . $e->getMessage();
              exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
              echo 'Facebook SDK returned an error: ' . $e->getMessage();
              exit;
        }
        
        if($superVar === $_POST){
            $json = $response->getGraphEdge();
        }else if($superVar === $_GET){
            $json = $response->getGraphNode();
        }
        $obj = json_decode($json, true);
        return $obj;
        var_dump($obj);
    }
    
    function echoEvents($obj){
        echo "<tr><th>Profile Photo</th><th>Name</th><th>Place</th></tr>";

         for($i = 0; $i < sizeof($obj); $i++){
            $data = (array)$obj[$i];
            $picture = (array)$data['picture'];
            
            echo "<tr>";
            echo "<td><a href=".$picture['url']." target='_blank'><img src='".$picture['url']."'width='30px' height='40px'></a></td>";  
            echo "<td>".$data['name']. "</td>";
            if(!isset($data['place'])){
                echo "<td></td>";
            }else{
                echo "<td>".$data['place']['name']."</td>";
            }
            echo "</tr>";
        }
    }
    
    function echoNotEvents($obj,$q, $type){
        echo "<tr><th>Profile Photo</th><th>Name</th><th>Detail</th></tr>";

         for($i = 0; $i < sizeof($obj); $i++){
            $data = (array)$obj[$i];
            $picture = (array)$data['picture'];

            echo "<tr>";
            echo "<td><a href=".$picture['url']." target='_blank'><img src='".$picture['url']."'width='30px' height='40px'></a></td>";  
            echo "<td>".$data['name']. "</td>";
            echo "<td><a href='http://cs-server.usc.edu:12318/search.php?id=".$data['id']."&q=".$q."&type=".$type."'>Details</a></td>";
             
            echo "</tr>";
        }
    }

    function parseSearch($obj,$q,$type){
        if(sizeof($obj) == 0){
            echo "<div id='record'>No Records has been found</div>";
        }else{
           echo "<table>";
            if($_POST['type'] == 'event'){
                echoEvents($obj);
            }else{
                echoNotEvents($obj,$q,$type);
            }
            echo "</table>";
        }          
    }
    
    function parseAlbums($obj){

        echo "<div class='title'><a href='#albums' onclick='toggle(".'albums'.");'>Albums</a></div>";

        $album = $obj['albums'];
        
        echo "<div id='albums' style='display:none;'>";
        echo "<table>";
        
        for($i = 0;$i < sizeof($album);$i++){
            if(isset($album[$i]['photos'])){
                $photo = $album[$i]['photos'];
                echo "<tr><td><a href='#albums' onclick='toggleClass(".$i.");'>".$album[$i]['name']."</a></td></tr>";
                echo "<tr class='picture' style='display:none;'><td>";
                
                for($j = 0;$j <sizeof($photo);$j++){
                    $id = $photo[$j]['id'];
                    
                    echo "<a href='https://graph.facebook.com/v2.8/".$id."/picture?access_token=EAAauSgXT9KYBAJlqS8FDhEGKu93omxKcakvCym6lx7B7hQtFmUiyZCcIcZCb2W2NPc5UHrUWuwwySZCzZBgp0u64KVsJSHZAkHrmjk2HNGHSJyJjRiRHiK5qkKtZBjenyafRKRXWz5ZAXnvIlZBmb8ZALpwoXwoZB5W87pdxmHxpJgXgZDZD' target='_blank'><img src='".$photo[$j]['picture']."' width='80' height='80'></img></a>";
                }
                
                echo "</td></tr>";
            }else{
                echo "<tr><td>".$album[$i]['name']."</td></tr>";
            }
        }
        
        echo "</table></div>";
        
    }
    
    function parsePosts($obj){
        echo "<div class='title'><a href='#posts' onclick='toggle(".'posts'.");'>Posts</a></div>";
        
        $post = $obj['posts'];
        echo "<div id='posts' style='display:none;'>";
        
        echo "<table><tr><th>Message</th></tr>";
        for($i = 0;$i < sizeof($post); $i++){
            echo "<tr><td>".$post[$i]['message']."</td></tr>";
        }
        echo "</table></div>";
    }
    
    function parseDetail($obj){
        if(!isset($obj['albums'])){
            echo "<table style='text-align:center;'><tr><td>No Albums have been found</td></tr><table>";
        }else{
            parseAlbums($obj);
        }
    
        if(!isset($obj['posts'])){
            echo "<table style='text-align:center;'><tr><td>No Posts have been found</td></tr></table>";
        }else{
            parsePosts($obj);
        }
    }
    
?>

<html>
    <head>
        <meta charset="utf-8">
        <title>Facebook Search</title>
        <style>
            body{
                font-family: serif;
            }
           #body{
                margin:10px auto auto auto;
                width:500px;
                height:150px;
                background-color: #F3F3F3;
                border:2px #D4D4D4 solid;
            }
            #record{
                border:2px #D4D4D4 solid;
                text-align: center;
                margin:20px auto auto auto;
                width:550px;
                background-color: #F3F3F3;
            }
            hr{
                width: 480px;
                
            }
            form{
                margin:10px auto auto 10px;
            }
            h1{
                font-weight: 100;
                font-family: serif;
                font-style: italic;
                margin:10px auto auto auto;
                text-align: center;
            }
            select{
                margin-left:18px;
            }
            #submit{
                margin-left:53px;
            }
            table{
                width:550px;
                text-align: left;
                margin:20px auto auto auto;
                border:2px #D4D4D4 solid;
                border-collapse: collapse;
            }
            th{
                font-weight: 600;
                border:1px #D4D4D4 solid;
            }
            td{
                border:1px #D4D4D4 solid;
            }
            .title{
                width:550px;
                text-align: center;
                background-color: #CCCCCC;
                margin:20px auto auto auto;
            }
        </style>
        <script>
            function showPlace(what){
                if(what == "place"){
                    document.getElementById("location").style.visibility = "visible";
                }else{
                    document.getElementById("location").style.visibility = "hidden";
                }
            }
            
            function toggle(a){
                if((document.getElementById('albums')!=null)&&(document.getElementById('posts')!=null)){
                    var x = document.getElementById('albums');
                    var y = document.getElementById('posts');
                
                    if((x.style.display === 'none')&&(y.style.display === 'none')){
                        a.style.display = 'block';
                    }else if((a == x)&&(x.style.display === 'block')){
                        a.style.display = 'none';
                    }else if((a == x)&&(x.style.display === 'none')){
                        a.style.display = 'block';
                        y.style.display = 'none';
                    }else if((a == y)&&(y.style.display === 'block')){
                        a.style.display = 'none';
                    }else if((a == y)&&(y.style.display === 'none')){
                        a.style.display = 'block';
                        x.style.display = 'none';
                    }
                }else if(document.getElementById('posts')==null){
                    var x = document.getElementById('albums');
                    
                    if(x.style.display === 'none'){
                        x.style.display = 'block';
                    }else{
                        x.style.display = 'none';
                    }
                }else if(document.getElementById('albums') == null){
                    var y = document.getElementById('posts');
                    
                    if(y.style.display === 'none'){
                        y.style.display = 'block';
                    }else{
                        y.style.display = 'none';
                    }
                }
                    
            }
            
            function toggleClass(i){
                var x = document.getElementsByClassName('picture');
                if(x.item(i).style.display === 'none'){
                    x.item(i).style.display = 'block';
                }else{
                    x.item(i).style.display = 'none';
                }
            }
            
            function clearPlace(){
                document.getElementById("location").style.visibility = "hidden";
                window.location.replace("http://www-scf.usc.edu/~hengcui/hengcui_hw6.html");
            }
        </script> 
    </head>
    

<?php
      if(!isset($_GET['id'])){
          
          $q = $_POST['keyword'];
          $type = $_POST['type'];
          
          $result = sendReq($fb, $_POST, $q, $type);
      }else{
          
          $q = $_GET['q'];
          $type = $_GET['type'];
          $result = sendReq($fb, $_GET,$q, $type);
      } 
?>
    <body>
        <div id="body">
            <h1>Facebook Search</h1>
            <hr />
            <form action="http://cs-server.usc.edu:12318/search.php" method="post">
                Keyword<input type="text" name="keyword" value="<?php echo $q; ?>" pattern="^(?!\d).+" autofocus required><br />
                Type:  <select name="type" onchange="showPlace(this.value)">
                        <option value='user' <?php if($type == 'user'){echo "selected";}?>>Users</option>
                        <option value='page' <?php if($type == 'page'){echo "selected";}?>>Pages</option>
                        <option value='event' <?php if($type == 'event'){echo "selected";}?>>Events</option>
                        <option value='group' <?php if($type == 'group'){echo "selected";}?>>Groups</option>
                        <option value='place' <?php if($type == 'place'){echo "selected";}?>>Places</option>
                        </select><br />
                
                <div id ="location" style="visibility:<?php if($type == 'place'){echo 'visible';}else{echo 'hidden';}?>">
                Location<input name="location" type="text" value="<?php echo $_POST['location']; ?>" >
                Distance(meters)<input name="distance" type="text" value="<?php echo $_POST['distance'];?>" pattern="^\d+(\.\d+)*$"><br />
                </div>
                <input id ="submit" type="submit" value="Search">
                <input type="reset" value="Clear" onclick="clearPlace()"><br />
            </form>
         </div>
            <?php
                if(isset($_GET['id'])){
                    parseDetail($result);
                }else if(isset($_POST)){
                    parseSearch($result,$q,$type); 
                }
            ?>
       
    </body>
</html>