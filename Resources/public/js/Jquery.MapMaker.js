/* 
 * To change this license header sad, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var pageMapList = new Array();
var currentMapPage = 1;
var numberMapPerPage = 1;
var numberOfMapPages = 0;
var mapList;
var primaryRegionArray = [];
var primaryTypeArray = [];
var infoWindow;
var makerAnimation;

var customIcons = {
    rents: {
        icon: '/img/pin_rent.png',
        shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
    },
    sales: {
        icon: '/img/pin_urgent.png',
        shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
    },
    listing: {
        icon: '/img/pin_list.png',
        shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
    },
    property: {
        icon: '/img/pin_property.png',
        shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
    },
    Retail: {
        icon: '/img/pin_sale.png',
        shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
    },
    Industrial: {
        icon: '/img/pin_success.png',
        shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
    },
    Office: {
        icon: '/img/pin_office.png',
        shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
    },
    waiting: {
        icon: '/img/pin_waiting.png',
        shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
    },
    done: {
        icon: '/img/pin_success.png',
        shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
    },
    urgent: {
        icon: '/img/pin_urgent.png',
        shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
    },
    delete: {
        icon: '/img/pin_urgent.png',
        shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
    }
};


function initMap(url, info) {
   var map = new google.maps.Map(document.getElementById("map"), {
          center: new google.maps.LatLng(-36.90524, 174.78928),
          zoom: 12,
          mapTypeId: 'roadmap',
          styles:[{"featureType":"landscape","stylers":[{"hue":"#FFBB00"},{"saturation":43.400000000000006},{"lightness":37.599999999999994},{"gamma":1}]},{"featureType":"road.highway","stylers":[{"hue":"#FFC200"},{"saturation":-61.8},{"lightness":45.599999999999994},{"gamma":1}]},{"featureType":"road.arterial","stylers":[{"hue":"#FF0300"},{"saturation":-100},{"lightness":51.19999999999999},{"gamma":1}]},{"featureType":"road.local","stylers":[{"hue":"#FF0300"},{"saturation":-100},{"lightness":52},{"gamma":1}]},{"featureType":"water","stylers":[{"hue":"#0078FF"},{"saturation":-13.200000000000003},{"lightness":2.4000000000000057},{"gamma":1}]},{"featureType":"poi","stylers":[{"hue":"#00FF6A"},{"saturation":-1.0989010989011234},{"lightness":11.200000000000017},{"gamma":1}]}],
    });

    infoWindow = new google.maps.InfoWindow;
    $('#loading').addClass('show').removeClass("hide");
    if(url !== false)
    {
        $.ajax({
            type: "GET",
            url: url,
            data:info,
            success: function(data) {
                /*console.log(data);*/
                $('#loading').addClass('hide').removeClass("show");
                if(data)  
                {
                    var json = JSON.parse(data);
                    
                    if(json[0].veraMsg === undefined || json[0].veraMsg === null)
                    {
                        map.setCenter(new google.maps.LatLng(json[0][0].lat, json[0][0].lng));
                        $.each(json, function(i, item) {
                            var name = item[0].name;
                            var address = item[0].address;
                            var type = item[0].IconType;
                            var point = new google.maps.LatLng(
                                parseFloat(item[0].lat),
                                parseFloat(item[0].lng));
                            var html = MapHtml(item[0], type);;
                            var icon = customIcons[type] || {};
                            var marker = new google.maps.Marker({
                                map: map,
                                position: point,
                                icon: icon.icon,
                                shadow: icon.shadow,
                                pid:item[0].id
                            });
                            bindInfoWindow(marker, map, infoWindow, html,icon, item);    
                        })
                    }
                    else
                    {
                        alert(json[0].veraMsg);
                    }
                }else
                {
                    alert('There is no data found, Try find again');
                }
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }  
        });
    } 
    else
    {
        $('#loading').addClass('hide').removeClass("show");
    }
}

function closeInfoWindow()
{
    infoWindow.close();
}

function setinfoWindowMsg(info)
{
    infoWindow.setContent(info);
}
function bindInfoWindow(marker, map, infoWindow, html, icon, item) {
    google.maps.event.addListener(marker, 'click', function() {
        toggleBounce(marker);   
        
        /*
            setTimeout(function(){ marker.setAnimation(null); }, 30000);
            infoWindow.setContent(html);
            infoWindow.open(map, marker);
        */
        var mainInfoPanel;
        mainInfoPanel = $('#infoPanel');
        mainInfoPanel.addClass('show').removeClass("hide");;
        mainInfoPanel.empty();
        mainInfoPanel.append(html);

        paginateArrayMap(item);
        
        $('#success-alert-bucket').addClass('hide').removeClass("show");
        $('#info-alert-bucket').addClass('hide').removeClass("show");
        $('#searchPanel').addClass('hide').removeClass("show");
        $('.closePanelInfo').click(function(){
            mainInfoPanel.addClass('hide').removeClass("show");;
            $('#searchPanel').addClass('show').removeClass("hide");
            $('.ajax-bucket-msg').addClass('hide').removeClass("show");
        });
  
    });
    
    google.maps.event.addListener(marker, "rightclick",function(event){
            $('#success-alert-bucket').addClass('hide').removeClass("show");
            $('#info-alert-bucket').addClass('hide').removeClass("show");
            $('#success-alert-bucket').addClass('hide').removeClass('show');
            var buckethtml = addBucketHtml(item);
            infoWindow.setContent(buckethtml);
            infoWindow.open(map, marker);
    });
}
        
function toggleBounce(marker) {
    if(Boolean(marker.getAnimation()))
    {
         marker.setAnimation(null);
    }else
    {
        if(makerAnimation)
        {
            makerAnimation[0].setAnimation(null);
        }
        makerAnimation = [];
        makerAnimation.push(marker);
        marker.setAnimation(google.maps.Animation.BOUNCE);  
    }
}

function paginateArrayMap(item) {
    mapList = item;
    makeList();
    refreshPage();
}
function makeList() {
    var itemLength = mapList.length;
    
    for (x = 0; x > itemLength; x++)
        mapList.push(x);
    numberOfMapPages = getNumberOfPages();
}

function getNumberOfPages() {
    return Math.ceil(mapList.length / numberMapPerPage);
}

function nextPage() {
    currentMapPage += 1;
    loadList();
}

function previousPage() {
    currentMapPage -= 1;
    loadList();
}

function firstPage() {
    currentMapPage = 1;
    loadList();
}

function lastPage() {
    currentMapPage = numberOfMapPages;
    loadList();
}
function refreshPage() {
    currentMapPage = 1;
    loadList();
}
function loadList() {
    var begin = ((currentMapPage - 1) * numberMapPerPage);
    var end = begin + numberMapPerPage;
    pageMapList = mapList.slice(begin, end);
    drawList();
    check();
}

function drawList() {
    $(".PropertyInfoMainpanel").empty();
    for (r = 0; r < pageMapList.length; r++) {
        $(".PropertyInfoMainpanel").html(pageMapList[0].html);
    }
}

function check() {
    $("#next").attr('class', currentMapPage !== numberOfMapPages ? 'nextPanelInfo enabled' : 'nextPanelInfo disabled');
    $("#previous").attr('class',currentMapPage !== 1 ? 'nextPanelInfo enabled' : 'nextPanelInfo disabled');
    $("#first").attr('class',currentMapPage !== 1 ? 'enabled' : 'disabled');
    $("#last").attr('class',currentMapPage !== numberOfMapPages ? 'enabled' : 'disabled');
}

function isEmpty(str) {
        return typeof str == 'string' && !str.trim() || typeof str == 'undefined' || str === null;
}

function EmbedHtml(lat,lng) 
{   
    var win = window.open('https://www.google.co.nz/maps/@'+lat+','+lng+',35m/data=!3m1!1e3', "_blank");
}
      
function MapHtml(item,type) 
{   
    var html;
    html ='<div class="PropertyContainer col-md-12 col-sm-12 col-xs-12 no-padding">'
                +'<div class="closeInfoPanel col-md-12 col-sm-12 col-xs-12  no-padding">'
                    +'<a href="javascript:void(0)" id="previous" onclick="previousPage()" class ="nextPanelInfo">'
                        +'<i class="fa fa-2x fa-arrow-left"></i>'
                    +'</a>'
                    +'<a href="javascript:void(0)" id="next" onclick="nextPage()" class ="nextPanelInfo">'
                        +'<i class="fa fa-2x fa-arrow-right"></i>'
                    +'</a>'
                    +'<a class="closePanelInfo">'
                        +'<i class="fa fa-2x fa-times-circle"></i>'
                    +'</a>'
                    +'<a href="javascript:void(0)" id="3dMap" onclick="EmbedHtml('+item.lat+','+item.lng+')" class ="embedMapViewBtn">'
                        +'<i class="fa fa-2x fa-map"></i>'
                    +'</a>'
                    /*
                        +'<a class="basketPanelInfo">'
                            +'<i class="fa fa-2x fa-shopping-basket"></i>'
                        +'</a>'
                    */
                +'</div>'
                +'<div class="typeInfoPanel col-md-12 col-sm-12 col-xs-12 no-padding">'+item.type+'</div>' 
                +'<div class="PropertyInfoMainpanel col-md-12 col-sm-12 col-xs-12  no-padding">'
                    /* HTML receiving from MarkerHelper.PHP*/
                +'</div>'
            +'</div>';

    return html;
}

function convertObjectToString( obj )
{
    var stringRep = "{";

    for ( var index in obj)
    {       
        var cIndex;
        if ( typeof index == "number" ) /* int index */
            cIndex = index;    
        else /* string index */
            cIndex = "\'" + index + "\'";

        if ( typeof obj[index] == "object" )
            stringRep += cIndex + ":" + convertObjectToString( 
                            obj[index] ) + ","; /* convert recursively */
        else if ( typeof obj[index] == "number" )
            stringRep += cIndex + ":" + obj[index] + ",";
        else
            stringRep += cIndex + ":\'" + obj[index] + "\',"; 

    }
    /* remove trailing comma (if not empty) */
    if ( stringRep.length > 1 )
        stringRep = stringRep.substr(0, stringRep.length - 1);
    stringRep += "}";
    return stringRep;
}


function addBucketHtml(item) 
{   
    var html;
    
    var alldata = [];
    $.each(item, function(i, data) {
            alldata.push({id:data.id, nid: data.relation});
    });
    
    var keyData = convertObjectToString(alldata);
    
    html ='<a href="javascript:void(0)"  class ="addToBucket_'+item[0].id+' btn btn-info" id="addBacketMap" data-id="'+item[0].id+'" onclick="addBucketAction('+item[0].id+','+keyData+')">'
                +'<i class="fa fa-plus"></i> Add to Bucket'
            +'</a>';
    /*
    * addBucketAction Function Created on Jquery.MapBucket.Js page 
    * Function addBucketAction(data , key) 
    * This function is using for adding data to bucket entity
    * Author : Premraj
    */
    return html;
}

function doNothing(){
    
}

function getMapSearch(url)
{   
    var link = false ;
    if(Boolean(url))
    { 
        link  = url; 
    } 
    else{
        /*link = '/markers/ajax/test/data';*/
        //link = '/markers/xml/full';
    }
    initMap(link);
}

function SearchByKeywords()
{ 

    var res = $('input[name="searchkeyword"]:checked').val();
    var searchText = $('input[name="searchkeywordinput"]').val();
    var url = 0; 
    var data = {value:searchText, select:res};
    if(res == 1)
    {
        url = '/searchMap/search/map/by/address';
    }else if(res == 2)
    {
        url = '/searchMap/search/map/by/client';
    }else
    {
        url = '/searchMap/search/map/by/tags';
    }/*console.log(data) */
    
    $("#selectedBucketInfo").data('type','property');
    initMap(url, data);
}

function showBucketProperty(bucketid)
{
    var link = '/searchMap/ajax/bucket/get/data';
    var data = {
                bucketid:bucketid, 
            };
    console.log(data);
    initMap(link, data);
}

function SearchByrents(link)
{ 
    var url = 0;
    var priClass = $('select[name="property[primaryClassification]"] option:selected').val();
    var priReg = $('select[name="property[primaryRegion]"] option:selected').val();
    var priType = $('select[name="property[primaryType]"] option:selected').val();
    var tenure = $('select[name="property[tenure]"] option:selected').val();
    var minDate = $('input[name="rental_search[minDate]"]').val();
    var maxDate = $('input[name="rental_search[maxDate]"]').val();
    var minContractRent = $('input[name="rental_search[minContractRent]"]').val();
    var maxContractRent = $('input[name="rental_search[maxContractRent]"]').val();
    var minQuantity = $('input[name="rental_search[minQuantity]"]').val();
    var maxQuantity = $('input[name="rental_search[maxQuantity]"]').val();
    var minNetRate = $('input[name="rental_search[minNetRate]"]').val();
    var maxNetRate = $('input[name="rental_search[maxNetRate]"]').val();

    var data = {
                primaryClass:priClass, 
                primaryRegion:priReg,
                primaryType:priType,
                tenure:tenure,
                minDate:minDate, 
                maxDate:maxDate,
                minContractRent:minContractRent,
                maxContractRent:maxContractRent, 
                minQuantity:minQuantity,
                maxQuantity:maxQuantity,
                minNetRate:minNetRate, 
                maxNetRate:maxNetRate,
            };
    /*console.log(data);*/
    
    $("#selectedBucketInfo").data('type','rent');
    initMap(link, data);
}

function SearchBySales(link)
{ 
    var url = 0;
    var priClass = $('select[name="property[primaryClassification]"] option:selected').val();
    var priReg = $('select[name="property[primaryRegion]"] option:selected').val();
    var priType = $('select[name="property[primaryType]"] option:selected').val();
    var tenure = $('select[name="property[tenure]"] option:selected').val();
    var minSalePrice = $('input[name="sale_search[minSalePrice]"]').val();
    var maxSalePrice = $('input[name="sale_search[maxSalePrice]"]').val();
    var minLettableArea = $('input[name="sale_search[minLettableArea]"]').val();
    var maxLettableArea = $('input[name="sale_search[maxLettableArea]"]').val();
    var minWalt = $('input[name="sale_search[minWalt]"]').val();
    var maxWalt = $('input[name="sale_search[maxWalt]"]').val();
    var minSaleDate = $('input[name="sale_search[minSaleDate]"]').val();
    var maxSaleDate = $('input[name="sale_search[maxSaleDate]"]').val();

    var data = {
                primaryClass:priClass, 
                primaryRegion:priReg,
                primaryType:priType,
                tenure:tenure,
                minSalePrice:minSalePrice, 
                maxSalePrice:maxSalePrice,
                minLettableArea:minLettableArea,
                maxLettableArea:maxLettableArea, 
                minWalt:minWalt,
                maxWalt:maxWalt,
                minSaleDate:minSaleDate, 
                maxSaleDate:maxSaleDate,
            };
    /*console.log(data);*/
    $("#selectedBucketInfo").data('type','sales');
    initMap(link, data);
}

function SearchByListings(link)
{ 
    var url = 0;
    var priClass = $('select[name="property[primaryClassification]"] option:selected').val();
    var priReg = $('select[name="property[primaryRegion]"] option:selected').val();
    var priType = $('select[name="property[primaryType]"] option:selected').val();
    var tenure = $('select[name="property[tenure]"] option:selected').val();
    var minListingDate = $('input[name="listing_search[minListingDate]"]').val();
    var maxListingDate = $('input[name="listing_search[maxListingDate]"]').val();
    var minCloseDate = $('input[name="listing_search[minCloseDate]"]').val();
    var maxCloseDate = $('input[name="listing_search[maxCloseDate]"]').val();

    var data = {
                primaryClass:priClass, 
                primaryRegion:priReg,
                primaryType:priType,
                tenure:tenure,
                minListingDate:minListingDate, 
                maxListingDate:maxListingDate,
                minCloseDate:minCloseDate,
                maxCloseDate:maxCloseDate, 
            };
    
    $("#selectedBucketInfo").data('type','listing');
    initMap(link, data);
}

function SearchByOppRentals(link)
{ 
    var url = 0;
    var priClass = $('select[name="property[primaryClassification]"] option:selected').val();
    var priReg = $('select[name="property[primaryRegion]"] option:selected').val();
    var priType = $('select[name="property[primaryType]"] option:selected').val();
    var tenure = $('select[name="property[tenure]"] option:selected').val();
    var custExist = $('input[name="property[exits]"]').is(':checked');
    var minDate = $('input[name="opportunity_search[minDate]"]').val();
    var maxDate = $('input[name="opportunity_search[maxDate]"]').val();
    var minResolvedDate = $('input[name="opportunity_search[minResolvedDate]"]').val();
    var maxResolvedDate = $('input[name="opportunity_search[maxResolvedDate]"]').val();
    var rentType = $('input[name="opportunity_search[type]"]:checked').val();
    
    if(typeof rentType == "undefined")
    {
        rentType = "0";
    }
    
    var data = {
                primaryClass:priClass, 
                primaryRegion:priReg,
                primaryType:priType,
                tenure:tenure,
                custExist:custExist,
                minDate:minDate, 
                maxDate:maxDate,
                minResolvedDate:minResolvedDate,
                maxResolvedDate:maxResolvedDate, 
                rentType:rentType,
            };
    
    initMap(link, data);
}

function SearchByOppListings(link)
{ 
    var url = 0;
    var priClass = $('select[name="property[primaryClassification]"] option:selected').val();
    var priReg = $('select[name="property[primaryRegion]"] option:selected').val();
    var priType = $('select[name="property[primaryType]"] option:selected').val();
    var tenure = $('select[name="property[tenure]"] option:selected').val();
    var custExist = $('input[name="property[exits]"]').is(':checked');
    var minDate = $('input[name="opportunity_search[minDate]"]').val();
    var maxDate = $('input[name="opportunity_search[maxDate]"]').val();
    var minResolvedDate = $('input[name="opportunity_search[minResolvedDate]"]').val();
    var maxResolvedDate = $('input[name="opportunity_search[maxResolvedDate]"]').val();
    var rentType = $('input[name="opportunity_search[list]"]:checked').val();
    
    if(typeof rentType == "undefined")
    {
        rentType = "0";
    }
    
    var data = {
                primaryClass:priClass, 
                primaryRegion:priReg,
                primaryType:priType,
                tenure:tenure,
                custExist:custExist,
                minDate:minDate, 
                maxDate:maxDate,
                minResolvedDate:minResolvedDate,
                maxResolvedDate:maxResolvedDate, 
                rentType:rentType,
            };
    
    initMap(link, data);
}


function SearchByOppCustoms(link)
{ 
    var url = 0;
    var priClass = $('select[name="property[primaryClassification]"] option:selected').val();
    var priReg = $('select[name="property[primaryRegion]"] option:selected').val();
    var priType = $('select[name="property[primaryType]"] option:selected').val();
    var tenure = $('select[name="property[tenure]"] option:selected').val();
    var custExist = $('input[name="property[exits]"]').is(':checked');
    var minDate = $('input[name="opportunity_search[minDate]"]').val();
    var maxDate = $('input[name="opportunity_search[maxDate]"]').val();
    var minResolvedDate = $('input[name="opportunity_search[minResolvedDate]"]').val();
    var maxResolvedDate = $('input[name="opportunity_search[maxResolvedDate]"]').val();
    var rentType = $('input[name="opportunity_search[custom]"]:checked').val();
    
    if(typeof rentType === "undefined")
    {
        rentType = "0";
    }
    
    var data = {
                primaryClass:priClass, 
                primaryRegion:priReg,
                primaryType:priType,
                tenure:tenure,
                custExist:custExist,
                minDate:minDate, 
                maxDate:maxDate,
                minResolvedDate:minResolvedDate,
                maxResolvedDate:maxResolvedDate, 
                rentType:rentType,
            };      
    initMap(link, data);
}

function ClearMapForm()
{
    $('select[name="property[primaryClassification]"] option:selected').removeAttr('selected');
    $('select[name="property[primaryRegion]"] option:selected').removeAttr('selected');
    $('select[name="property[primaryType]"] option:selected').removeAttr('selected');
    $('select[name="property[tenure]"] option:selected').removeAttr('selected');
    $('input[name="property[exits]"]:checked').prop('checked', false);
    
    $('input[name="rental_search[minDate]"]').val('');
    $('input[name="rental_search[maxDate]"]').val('');
    $('input[name="rental_search[minContractRent]"]').val('');
    $('input[name="rental_search[maxContractRent]"]').val('');
    $('input[name="rental_search[minQuantity]"]').val('');
    $('input[name="rental_search[maxQuantity]"]').val('');
    $('input[name="rental_search[minNetRate]"]').val('');
    $('input[name="rental_search[maxNetRate]"]').val('');
    
    $('input[name="sale_search[minSalePrice]"]').val('');
    $('input[name="sale_search[maxSalePrice]"]').val('');
    $('input[name="sale_search[minLettableArea]"]').val('');
    $('input[name="sale_search[maxLettableArea]"]').val('');
    $('input[name="sale_search[minWalt]"]').val('');
    $('input[name="sale_search[maxWalt]"]').val('');
    $('input[name="sale_search[minSaleDate]"]').val('');
    $('input[name="sale_search[maxSaleDate]"]').val('');
    
    $('input[name="listing_search[minListingDate]"]').val('');
    $('input[name="listing_search[maxListingDate]"]').val('');
    $('input[name="listing_search[minCloseDate]"]').val('');
    $('input[name="listing_search[maxCloseDate]"]').val('');
    
    $('input[name="opportunity_search[minDate]"]').val('');
    $('input[name="opportunity_search[maxDate]"]').val('');
    $('input[name="opportunity_search[minResolvedDate]"]').val('');
    $('input[name="opportunity_search[maxResolvedDate]"]').val('');
    $('input[name="opportunity_search[type]"]:checked').prop('checked', false);
    $('input[name="opportunity_search[list]"]:checked').prop('checked', false);
    $('input[name="opportunity_search[custom]"]:checked').prop('checked', false);
}

function getLocationInfo() {
    /*var api = 'AIzaSyARuiA5ODewzbXcHZHr8OaZvwvy3iI6LTQ';*/
    var api = 'AIzaSyD1zw1nTmE_iYMCs-7AwCGIBVWSVMtQda4';
    var fucntionCallback = "getMapSearch"
    var src = "https://maps.googleapis.com/maps/api/js?key="+api+"&callback="+fucntionCallback+"";
    var s = document.createElement( 'script' );
    s.setAttribute( 'src', src );
    document.body.appendChild( s );

}


+function ($) {
    getLocationInfo();

    $('.closeSearchPanel').click(function(){
        $('#searchPanel').addClass('hide').removeClass("show");
    });
    
    $('.clearSearch').click(function(){
        ClearMapForm();
    });
    
    $('#searchMap').click(function(){
        $('#searchPanel').addClass('show').removeClass("hide");
        $('#infoPanel').addClass('hide').removeClass("show");;
    });
    
    $('input[name="searchCategory"]').change(function (event) {
        var id = $(this).data('ptype');
        $('.' + id).addClass('show').removeClass('hide').siblings().addClass('hide').removeClass('show');
        $('.evi_classification').addClass('hide').removeClass('show');
        $('.classification_toggle').addClass('show').removeClass('hide');
        $('.keyword_search').addClass('hide').removeClass('show');
        $('.keyword_toggle').addClass('show').removeClass('hide');
        $('input[name="opportunity_search[type]"]').prop('checked', false);
        $('input[name="opportunity_search[list]"]').prop('checked', false);
        $('input[name="opportunity_search[custom]"]').prop('checked', false);
    });
    
    if($('input[name="searchCategory"]').is(":checked"))
    {
        $('.evi_classification').addClass('hide').removeClass('show');
        $('.classification_toggle').addClass('show').removeClass('hide');
    }
    
    $('.classification_toggle').click(function (event) {
        $('.evi_classification').addClass('show').removeClass('hide');
        $('input[name="searchCategory"]').prop('checked', false);
        $('.property_search_type').children().addClass('hide').removeClass('show');
        $(this).addClass('hide').removeClass('show');
    });
    
    $('.keyword_toggle').click(function (event) {
        $('.evi_classification').addClass('show').removeClass('hide');
        $('.keyword_search').addClass('show').removeClass('hide');
        $('input[name="searchCategory"]').prop('checked', false);
        $('.property_search_type').children().addClass('hide').removeClass('show');
        $(this).addClass('hide').removeClass('show');
        $('.classification_toggle').addClass('hide').removeClass('show');
    });
    /*
        $("input#rental_search_minDate").datepicker({
            format: "dd-mm-yyyy"
        }).on('change changeDate', function() {
            if (!parseDate($(this).val())) {
                $(this).val('');
            }
        });
    
        $('#property_primaryRegion').multiselect({
            selectAllValue: 'multiselect-all',
            refresh:true,
            deselectAll:true,

            onChange: function(element, checked) {
                primaryRegionArray = [];
                primaryTypeArray = [];
                var brands = $('#property_primaryRegion option:selected');
                $(brands).each(function(index, brand){
                    primaryRegionArray.push($(this).val());
                });
            }
        }); 

         $('#property_primaryType').multiselect({
            selectAllValue: 'multiselect-all',
            refresh:true,
            deselectAll:true,

            onChange: function(element, checked) {
                primaryTypeArray = [];
                var brands = $('#property_primaryType option:selected');
                $(brands).each(function(index, brand){
                    primaryTypeArray.push($(this).val());
                });
            }
        }); 
    */

}(jQuery);


/*
function initMapXml(url) {
    var map = new google.maps.Map(document.getElementById("map"), {
          center: new google.maps.LatLng(-36.8503976, 174.7657216),
          zoom: 12,
          mapTypeId: 'roadmap',
          styles:[{"featureType":"landscape","stylers":[{"hue":"#FFBB00"},{"saturation":43.400000000000006},{"lightness":37.599999999999994},{"gamma":1}]},{"featureType":"road.highway","stylers":[{"hue":"#FFC200"},{"saturation":-61.8},{"lightness":45.599999999999994},{"gamma":1}]},{"featureType":"road.arterial","stylers":[{"hue":"#FF0300"},{"saturation":-100},{"lightness":51.19999999999999},{"gamma":1}]},{"featureType":"road.local","stylers":[{"hue":"#FF0300"},{"saturation":-100},{"lightness":52},{"gamma":1}]},{"featureType":"water","stylers":[{"hue":"#0078FF"},{"saturation":-13.200000000000003},{"lightness":2.4000000000000057},{"gamma":1}]},{"featureType":"poi","stylers":[{"hue":"#00FF6A"},{"saturation":-1.0989010989011234},{"lightness":11.200000000000017},{"gamma":1}]}],
    });
    
    var marker;
    var infoWindow = new google.maps.InfoWindow;
    // Change this depending on the name of your PHP file //
    downloadUrl(url, function(data) {
        var xml = data.responseXML;
        var markers = xml.documentElement.getElementsByTagName("marker");
        for (var i = 0; i < markers.length; i++) {
            var name = markers[i].getAttribute("name");
            var address = markers[i].getAttribute("address");
            var type = markers[i].getAttribute("IconType");
            var point = new google.maps.LatLng(
                parseFloat(markers[i].getAttribute("lat")),
                parseFloat(markers[i].getAttribute("lng")));
            var html = MapHtml(name,address,type);
            var icon = customIcons[type] || {};
            marker = new google.maps.Marker({
                map: map,
                position: point,
                icon: icon.icon,
                animation: false,
                shadow: icon.shadow,
            });

            bindInfoWindow(marker, map, infoWindow, html, icon); 
        }
        
        marker.addListener('click', toggleBounce(marker));
    });
}

function downloadUrl(url, callback) {
    var request = window.ActiveXObject ?
        new ActiveXObject('Microsoft.XMLHTTP') :
        new XMLHttpRequest;
    request.onreadystatechange = function() {
        if (request.readyState == 4) {
            request.onreadystatechange = doNothing;
            callback(request, request.status);
        }
    };
    request.open('GET', url, true);
    request.send(null);
}
*/