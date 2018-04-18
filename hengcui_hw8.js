"use strict";
/*indicate current pagenumber for pagination*/
var pageNum = 0;
/*check if user has clicked the search button, if isClicked > 0, while tabSwitch, it can showtable, else tabSwitch do nothing*/
var isClicked = 0;
/*user position*/
var coords={latitude: 0, longitude: 0};
/*when detailButton is clicked , current name and photoUrl related to this detail*/
var detailName, detailUrl;
/*actively tab-content div*/
var tabDOM;

/*clear all pages*/
function clearAll(){
    $(".tab-content div").empty();
    pageNum = 0;
    isClicked = 0;
    coords.latitude = 0;
    coords.longitude = 0;
    detailName = null;
    detailUrl = null;
    tabDOM = null;
    if($("input").val() != ""){
        $("input").val("");
    }
    localStorage.clear();
    $("li[class~='active']").removeClass("active");
}
/*get current user position*/ 
var options = {
    enableHighAccuracy: true,
    timeout: 8000,
    maximumAge: 0
}; 
/*return position coords when success*/
function getPositionSuccess(pos){
    coords.latitude = pos.coords.latitude;
    coords.longitude = pos.coords.longitude;
}
/*alert info when getposition fail*/   
function getPositionFail(pos){
    alert("Fail to get current User position");
}

/*facebook post api*/
 window.fbAsyncInit = function() {
        FB.init({
            appId      : '1880482808853670',
            xfbml      : true,
            cookie     : true,
            version    : 'v2.8'
        });

};

(function(d, s, id){
 var js, fjs = d.getElementsByTagName(s)[0];
 if (d.getElementById(id)) {return;}
 js = d.createElement(s); js.id = id;
 js.src = "//connect.facebook.net/en_US/sdk.js";
 fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

function fbPost(){
    FB.ui({
        app_id:'1880482808853670',
        method:'feed',
        link:window.location.href,
        picture:detailUrl,
        name:detailName,
        caption: 'FB SEARCH FROM USC CSCI571'
    },function(response){
        if(response&& !response.error_message){
            alert("Posted successfully");
        }else{
            alert("Not Posted");
        }
    });
}
/*send req when tabSwitch - check if isClicked>0 send req; if isClicked=0, do nothing*/
function tabSwitch(type){
    $(tabDOM).empty();
    pageNum = 0;
    if(isClicked > 0){
        req(type);
    }
}
/*send request to php to get json - when current type is favorite, create table; else, send req to get data*/   
function req(type){
    if(type === null){
        tabDOM = "div[class~='active']";
    }else{
        tabDOM = "#"+type;
    }
    
    if($("input").val() !=""){
        $(tabDOM).empty();
        /*progress bar*/
        $(tabDOM).append("<div class='progress'><div class='progress-bar progress-bar-striped active' role='progressbar' aria-valuenow='50' aria-valuemax='100' aria-valuemin='0' style='width:50%'></div></div>");

        if($(tabDOM).attr("id") === "favorite"){
             $("#favorite").empty();
             var index = 1;
            /*set header of favorite table*/
            $("#favorite").append("<table class='table table-hover col-md-10'><thead><tr><th>#</th><th>Profile photo</th><th>Name</th><th>Type</th><th>Favorite</th><th>Details</th></tr></thead><tbody></tbody></table>");
            /*set content of favorite table*/
            for(var i in localStorage){
                if(localStorage.getItem(i) !== null){
                    $("#favorite table tbody").append("<tr><td>"+index+"</td>"+localStorage.getItem(i)+"</tr>");
                    index++;
                }
            }
        }else{
            /*when type is event, get user current position and add center=latitude,longitude to query parameter*/
            if($(tabDOM).attr("id") === 'event'){
                navigator.geolocation.getCurrentPosition(getPositionSuccess,getPositionFail,options);
                $.ajax({
                    url: "http://cs-server.usc.edu:12318/hengcui_hw8.php",
                    type: "GET",
                    data: {
                        q: $("input[type=text]").val(),
                        type: $(tabDOM).attr("id"),
                        index: pageNum,
                        lat: coords.latitude,
                        lng: coords.longitude
                    },
                    dataType: "json",
                    success:showTable
                });
            }else{
                $.ajax({
                    url: "http://cs-server.usc.edu:12318/hengcui_hw8.php",
                    type: "GET",
                    data: {
                        q: $("input[type=text]").val(),
                        type: $(tabDOM).attr("id"),
                        index: pageNum
                    },
                    dataType: "json",
                    success:showTable
                });
            }
            isClicked++;
        }
    }
}

function showTable(response){
    var rowIndex = 0;
    /*set header of table when type is not favorite*/
    $(tabDOM).empty();

    $(tabDOM).append("<table class='table table-hover col-md-10'><thead><tr><th>#</th><th>Profile photo</th><th>Name</th><th>Favorite</th><th>Details</th></tr></thead><tbody></tbody></table>");

    /*set content of table*/
    for(var i=0;i < response.length;i++){
        var name = response[i]['name'];
        var id= response[i]['id'];
        var pictureUrl = response[i]['picture']['url'];
        
        
        $(tabDOM+" table tbody").append("<tr></tr>");
        /*set num, photo, name*/
        $(tabDOM+" table tr:eq("+(i+1)+")").append("<td>"+(i+1)+"</td><td class='photo'><img src='"+pictureUrl+"' width='30px' height='30px' class='img-circle'></td><td class='name'>"+name+"</td>");

        /*create Favorite Button-use localStorage to set whether the star is empty or not*/
        if(localStorage.getItem(name) !== null){
            $(tabDOM+" table tr:eq("+(i+1)+")").append("<td class='fav'><button aria-label='favorite' onclick='setFav(this, "+i+", "+id+")'><span class='glyphicon glyphicon-star' aria-hidden='true'></span></button></td>");
        }else{
           $(tabDOM+" table tr:eq("+(i+1)+")").append("<td class='fav'><button aria-label='favorite' onclick='setFav(this, "+i+", "+id+")'><span class='glyphicon glyphicon-star-empty' aria-hidden='true'></span></button></td>");
        }

        /*create Detail Button-use id as name to track the id needed for tbe http request */
        $(tabDOM+" table tr:eq("+(i+1)+")").append("<td class='detail'><button aria-label='detail' onclick='detailReq(this, "+id+")'><span class='glyphicon glyphicon-chevron-right' aria-hidden='true'></span></button></td>");
        
        rowIndex++;
    }
    addPageButton(rowIndex);
}
/*favorite star click event- toggle star-type and set/remove localStorage*/
function setFav(obj,number, id){
    /* use number to find para in favorite section*/
     var type = $(tabDOM).attr("id");
     var name = $(tabDOM+" .name:eq("+number+")").html();  
     var photo = $(tabDOM+" .photo:eq("+number+")").html();


    if(localStorage.getItem(name) == null){
        $(obj).children().attr("class","glyphicon glyphicon-star");

        localStorage.setItem(name, "<td class='photo'>"+photo+"</td><td class='name'>"+name+"</td><td>"+type+"</td><td><button aria-label='delete' onclick='deleteFav(this)'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button></td><td class='detail'><button aria-label='detail' onclick='detailReq(this, "+id+")'><span class='glyphicon glyphicon-chevron-right' aria-hidden='true'></span></button></td>");
    }else{
        localStorage.removeItem(name);
        $(obj).children().attr("class","glyphicon glyphicon-star-empty");
    }
}
/*delete button - delete favorite from localStorage and remove tr from current table*/
function deleteFav(obj){
    var name = $(obj).parent().prev().prev().text();

    $(obj).parent().parent().remove();
    localStorage.removeItem(name);
}

/*json recevied by ajax do not contain paging part, so use index as a query parameter to track the next and prev page*/    
function pageReq(obj){
    if($(obj).text() == "Next"){
        console.log(pageNum);
        pageNum++;
    }else if($(obj).text() == "Previous"){
        pageNum--;
    }

    if(pageNum >= 0){
        $.ajax({
                url: "http://hengcui.us-west-1.elasticbeanstalk.com",
                type: "GET",
                data: {
                    q: $("input[type=text]").val(),
                    type: $(tabDOM).attr("id"),
                    index: pageNum
                },
                dataType: "json",
                success:showTable
        });
    }else{
        return;
    }
} 

/*add pager at the bottom of table*/    
function addPageButton(num){
    if(pageNum > 0){
        if(num > 24){
            $(tabDOM).append("<ul class='pager'><li><a href='#' onclick='pageReq(this)'>Previous</a></li><li><a href='#' onclick='pageReq(this)'>Next</a></li></ul>");
        }else{
            $(tabDOM).append("<ul class='pager' id='pager'><li><a href='#' onclick='pageReq(this)'>Previous</a></li></ul>");
        }
    }else{
        if(num >= 24){
            $(tabDOM).append("<ul class='pager' id='pager'><li><a href='#' onclick='pageReq(this)'>Next</a></li></ul>");
        }
    }
}
/*when click on Detail send a http request to get albums and posts*/
function detailReq(obj, idNum){
    detailName = $(obj).parent().siblings("[class='name']").text();
    detailUrl = $(obj).parent().siblings("[class='photo']").children().attr('src');
    var type = $(tabDOM).attr("id");

    $(tabDOM).empty();
    $(tabDOM).append("<div class='row' id='topButton'></div>");
    /*create top left back button back: send http request to reget the previous page*/
    $("#topButton").append("<button id='back' onclick='req(null)'>❮ Back</button><div id='rightButton'></div>");
    /*create top right favorite star button*/
    if(localStorage.getItem(detailName) !== null){
        $("#rightButton").append("<button aria-label='favorite' id='star' onclick='setDetailFav(this, "+idNum+")'><span class='glyphicon glyphicon-star' aria-hidden='true' ></span></button>");
    }else{
        $("#rightButton").append("<button aria-label='favorite'  id='star' onclick='setDetailFav(this, "+idNum+")'><span class='glyphicon glyphicon-star-empty' aria-hidden='true'></span></button>");
    }
    /*create top right fbPost button*/
    $("#rightButton").append("<button id='fbButton' onclick='fbPost()'><img src='facebook.png' alt='facebookIcon' width='15px' height='15px'></div>");
    /*create albums and posts panel and panel-heading*/                            
    $(tabDOM).append("<div class='row'><div class='col-md-6'><div class='panel panel-default'><div class='panel-heading'>Albums</div><div class='panel-body'><div class='panel-group' id='album'></div></div></div></div><div class='col-md-6'><div class='panel panel-default'><div class='panel-heading'>Posts</div><div class='panel-body'><div class='panel-group' id='post'></div></div></div></div></div></div>");
    /*progress bar*/
    $("#album").append("<div class='progress'><div class='progress-bar progress-bar-striped active' role='progressbar' aria-valuenow='50' aria-valuemax='100' aria-valuemin='0' style='width:50%'></div></div>");
    $("#post").append("<div class='progress'><div class='progress-bar progress-bar-striped active' role='progressbar' aria-valuenow='50' aria-valuemax='100' aria-valuemin='0' style='width:50%'></div></div>");

    $.ajax({
        url: "http://hengcui.us-west-1.elasticbeanstalk.com",
        type: "GET",
        data: {
            id: idNum
        },
        dataType: "json",
        success: createDetailTable,
        error: createFailDetailTable
    });
}

/*when http successfully returned, create detail Page table*/
function createDetailTable(response){
    var albums = response['albums'];
    var posts = response['posts'];

    $("#album").empty();
    $("#post").empty();
    /*if no albums or posts returned output 'no data found', else according to response add albums and posts'name and photos to panel-body*/
    if(typeof(albums) !== 'undefined'){
        createAlbums(albums);
    }else{
        $("#album").append("<div class='panel panel-warning'><div class='panel-body'>No data found.</div></div>");
    }

    if(typeof(posts) !== 'undefined'){
        createPosts(posts);
    }else{
         $("#post").append("<div class='panel panel-warning'><div class='panel-body'>No data found.</div></div>");
    }
}

function createFailDetailTable(response){
    $("#album").empty();
    $("#post").empty();
    $("#album").append("<div class='panel panel-warning'><div class='panel-body'>No data found.</div></div>");
    $("#post").append("<div class='panel panel-warning'><div class='panel-body'>No data found.</div></div>");
}

/*create Albums panel - usc panel-collapse to complement toggle function;display first panel-body*/        
function createAlbums(obj){
    for(var i = 0;i < obj.length;i++){
        if(typeof(obj[i]['photos']) !== 'undefined'){
            $("#album").append("<div class='panel panel-default'><div class='panel-heading'><a data-toggle='collapse' data-parent='#album' href='#ab"+i+"'>"+obj[i]['name']+"</a></div><div id='ab"+i+"' class='panel-collapse collapse'><div class='panel-body'></div></div></div></div>");

            for(var j = 0;j < obj[i]['photos'].length; j++){
                var photoId = obj[i]['photos'][j]['id'];
                var photoSrc ="https://graph.facebook.com/v2.8/"+photoId+"/picture?access_token=EAAauSgXT9KYBAJlqS8FDhEGKu93omxKcakvCym6lx7B7hQtFmUiyZCcIcZCb2W2NPc5UHrUWuwwySZCzZBgp0u64KVsJSHZAkHrmjk2HNGHSJyJjRiRHiK5qkKtZBjenyafRKRXWz5ZAXnvIlZBmb8ZALpwoXwoZB5W87pdxmHxpJgXgZDZD";

               $("#ab"+i+" .panel-body").append("<img width='100%' height='300px' src='"+photoSrc+"'>");
            }
            $("#ab0").attr("class", "panel-collapse collapse in");
            $("#ab"+i+" .panel-body").append("</div>");
        }else{
            $("#album").append("<div class='panel panel-default'><div class='panel-heading'>"+obj[i]['name']+"</div></div>");  
        }
    }
}
/*create Posts panel- contains photoImg, created_time, message*/    
function createPosts(obj){
    for(var i = 0;i < obj.length;i++){
        $("#post").append("<div class='panel panel-default'><div class='panel-body'><table><tr><td style='width:15%;' rowspan='2'><img width='40px' height='40px' src='"+detailUrl+"'></td><td class='postname'>"+detailName+"</td><tr><td class='posttime'>"+obj[i]['created_time']['date']+"</td></tr></table><div class='message'>"+obj[i]['message']+"</div></div></div>");
    }
}
/*click favorite star in detail Page to toggle button and set/remove localStorage*/
function setDetailFav(obj,number){
    /* use number to find para in favorite section*/
     var type = $("div[class~='active']").attr("id");

    if(localStorage.getItem(detailName) == null){
        $(obj).children().attr("class","glyphicon glyphicon-star");

        localStorage.setItem(detailName, "<td class='photo'><img src='"+detailUrl+"' width='30px' height='30px' class='img-circle'></td><td class='name'>"+detailName+"</td><td>"+type+"</td><td><button aria-label='delete' onclick='deleteFav(this)'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button></td><td class='detail'><button aria-label='detail onclick='detailReq(this, "+number+")'><span class='glyphicon glyphicon-chevron-right' aria-hidden='true'></span></button></td>");
    }else{
        localStorage.removeItem(detailName);
        $(obj).children().attr("class","glyphicon glyphicon-star-empty");
    }
}