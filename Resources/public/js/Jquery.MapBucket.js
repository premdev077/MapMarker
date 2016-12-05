function addBucketAction(item, datas)
{
    var bucketid = $("#selectedBucketInfo").data('bucket-id');
    var buckettype = $("#selectedBucketInfo").data('type');
    var propertyid = $(".addToBucket_"+item).data('id');
 
    if(bucketid !== 0 && buckettype !== 0 && propertyid !== '')
    {
        var data = {
                bucketid:bucketid, 
                type:buckettype,
                data:datas
            };
            
        $.ajax({
            type: "POST",
            url: "/markers/ajax/bucket/store",
            data:data,
            success: function(data) {
                
                if(data)  
                {
                    if(data.type)
                    {
                        closeInfoWindow();
                        $('.bucketMsgRight').empty();
                        $('#success-alert-bucket').addClass('show').removeClass('hide');
                        $('.bucketMsgRight').append(
                                $('<i>').attr('class','fa fa-check').html(" " + data.message)
                               );
                   
                    }else
                    {   
                        closeInfoWindow();
                        $('.info-alert-bucket-msg').empty();
                        $('#info-alert-bucket').addClass('show').removeClass('hide');
                        $('.info-alert-bucket-msg').html(data.message);
                    }
                }else
                {
                    alert('There is no data found, please check the values are Correct');
                }
            },
            error: function(errorThrown){
                var info = 'Bucket already Contain this property details';
                setinfoWindowMsg(info);
                console.log(errorThrown);
            }  
        });
    }
    else
    {
        alert('Please select the bucket to store');
    }
    
}
function addBucketInfoAction(item, dataX)
{
    var bucketid = $("#selectedBucketInfo").data('bucket-id');
    var buckettype = $("#selectedBucketInfo").data('type');
    var propertyid = item;
    var dataA = dataX;
    
    if(bucketid !== 0 && buckettype !== 0 && propertyid !== '')
    {
        var data = {
                bucketid:bucketid, 
                type:buckettype,
                data:dataA,
                property:item
            };
            
        $.ajax({
            type: "POST",
            url: "/markers/ajax/bucket/info/store",
            data:data,
            success: function(data) {
                if(data)  
                {   
                    if(data.type)
                    {
                        $('.bucketMsgRight').empty();
                        $('#success-alert-bucket').addClass('show').removeClass('hide');
                        $('.bucketMsgRight').append(
                                $('<i>').attr('class','fa fa-check').html(" " + data.message)
                                );
                        /*
                            if(data.type == 'listing')
                            {
                                $('.ajax-list-msg-'+data.obj_id).addClass('show').removeClass("hide");
                            }
                            else if(data.type == 'sales')
                            {
                                $('.ajax-sale-msg-'+data.obj_id).addClass('show').removeClass("hide");
                            }
                            else if(data.type == 'rent')
                            {
                                $('.ajax-rent-msg-'+data.obj_id).addClass('show').removeClass("hide");
                            }
                            else if(data.type == 'property')
                            {
                                $('.ajax-property-msg-'+data.obj_id).addClass('show').removeClass("hide");
                            }  
                        */
                    }else
                    {   
                        $('.info-alert-bucket-msg').empty();
                        $('#info-alert-bucket').addClass('show').removeClass('hide');
                        $('.info-alert-bucket-msg').html(data.message);
                    }
                   
                }else
                {
                    
                    alert('There is no data found, please check the values are Correct');
                }
            },
            error: function(errorThrown){
                var info = 'Bucket already Contain this property details';
                alert(info);
                console.log(errorThrown);
            }  
        });
    }
    else
    {
        alert('Please select the bucket to store');
    }
    
}

function selectBucketOption(bucketID, name)
{
        var bucketName =  String(name).replace("/", "");
        var SetBucketID = $("#selectedBucketInfo").data('bucket-id',bucketID);
        var BucketValue = $("#selectedBucketInfo").data('bucket-id');
        var Buckettype = $("#selectedBucketInfo").data('type');
        $("#selectedBucketName").html(bucketName);
        $("#selectedBucketType").html(Buckettype);
        $("#selectedBucketInfo").addClass('show').removeClass("hide");
        $("#buckets").css('height', '385px');
}

$(document).ready(function () {
    
    $("a.BucketMapOpen").click(function () {
        $('#BucketListPanel').addClass('show').removeClass("hide");
        $('.searchCountinuMap').addClass('show').removeClass("hide");
        $(this).addClass('hide').removeClass("show");
        $('.searchInfoMainpanel').addClass('hide').removeClass("show");
        var Buckettype = $("#selectedBucketInfo").data('type');
        $("#selectedBucketType").html(Buckettype);
    });
    
    $("a.basketPanelInfo").click(function (e) {
        e.preventDefault();
        $('#BucketListPanel').addClass('show').removeClass("hide");
        $('.searchCountinuMap').addClass('show').removeClass("hide");
        $('#infoPanel').addClass('hide').removeClass("show");
        $('.searchInfoMainpanel').addClass('hide').removeClass("show");
    });
    
     $("a.searchCountinuMap").click(function () {
        $('#BucketListPanel').addClass('hide').removeClass("show");
        $(this).addClass('hide').removeClass("show");
        $('.BucketMapOpen').addClass('show').removeClass("hide");
        $('.searchInfoMainpanel').addClass('show').removeClass("hide");
        /*
            $("#selectedBucketInfo").data('bucket-id',0);
            $("#selectedBucketInfo").data('type',0);
            $('#selectedBucketInfo').addClass('hide').removeClass("show");
        */
    });
    
    $("a.selectBucketOption").on('click', function () {
        var bucketID = $(this).data('bucket-id');
        var bucketName= $(this).data('bucket-name');
        var SetBucketID = $("#selectedBucketInfo").data('bucket-id',bucketID);
        var BucketValue = $("#selectedBucketInfo").data('bucket-id');
        var Buckettype = $("#selectedBucketInfo").data('type');
        $("#selectedBucketName").html(bucketName);
        $("#selectedBucketType").html(Buckettype);
        $("#selectedBucketInfo").addClass('show').removeClass("hide");
        $("#buckets").css('height', '385px');
    });
    
    
    
    $("a.alert-bucket-close").click(function () {
        $('#success-alert-bucket').addClass('hide').removeClass("show");
        $('#info-alert-bucket').addClass('hide').removeClass("show");
    });
    

    $("button#save_map_bucket").click(function () {
        $("#modal_new_bucket").modal('hide');
        $.ajax({
            type: 'POST',
            url: "/buckets/create",
            data: {
                bucket_name: $("input#new_bucket_name").val(),
                bucket_type: $("input#new_bucket_type").val()
            },
            success: function (response) {
                var div = '<div class="swatch" style="background-color:' + response.color + ';"></div>';
                var link = '<a class="bucket no-padding padding-left-5 col-md-8 col-sm-8" data-bucket-id="' + response.id + '">' + response.name + '</a>';
                var selectbtn = '<a class="selectBucketOption no-padding btn btn-info col-md-3 col-sm-3 pull-right no-margin" onclick="selectBucketOption('+response.id +',/ '+response.name+' /)" data-bucket-id="' + response.id + '" data-bucket-name="'+response.name+'">select</a>';
                $("ul#buckets").append('<li class="no-padding  margin-top-5 col-md-12 col-sm-12">' + div + link + selectbtn +'</li>');
                $("input#new_bucket_name").val('');
            }
        });
    });
 
    $(".clearSearch").click(function () {
        $("#selectedBucketInfo").data('bucket-id',0);
        $("#selectedBucketName").html($("#selectedBucketInfo").data('bucket-id'));
        $("#selectedBucketType").html($("#selectedBucketInfo").data('type'));
    });
 
});