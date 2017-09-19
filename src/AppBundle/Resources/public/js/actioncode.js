

function reload(id,url)
{
    id='#'+id;
    var path=$(id).attr('data-path');
    if (url)
    {
        path=url;
        $(id).attr('data-path',url);
    }
    $.get(path,function(result){
        $(id).html(result)
    });
}


function refreshpanel(target,url)
{
    var trgt=$('#'+target);
    $.get(url,function(result){
        $(trgt).html(result);
    })
}

function publish(id)
{
    var path='/publish/'+id;
    $.get(path,function(result){
        reload('upload_list');
    });
}

function expandcollapsesession(id,type,child,target)
{
    var icon=$(target).find('i');
    var collapsed=$(icon).hasClass('fa-plus-square');
    var placeholder=$('#'+child+id);
    if (collapsed===true)
    {
        var path='/sessions/'+type+'/'+id;
        $.get(path,function(result){
            $(placeholder).html(result);
        })
    }
    else
    {
        placeholder.html('');
    }
    icon.toggleClass('fa-plus-square fa-minus-square');
}

function openFileDialog()
{
    $.get('/uploadform',function(result){
        $('#file_upload_content').html(result);
        $('#file_dialog').modal('show');
        var myDropzone = new Dropzone("#file_dropper");
        myDropzone.on("success", function(file,result) {
            $('#file_dialog').modal('hide');
            $.get('/uploadinfo/'+result['id'],function(result){
                $('#file_upload_content').html(result);
                $('#file_dialog').modal('show');
            });
        });
    });
}

function openSettingsDialog()
{
    $.get('/settings',function(result){
       $('#file_upload_content').html(result);
        $('#file_dialog').modal('show');
    });
}

function showeditform(id)
{
    $.get('/uploadinfo/'+id,function(result){
        $('#file_upload_content').html(result);
        $('#file_dialog').modal('show');
    });
}


function sendBlendStart(id,target,type)
{
    var path='';
    if (type==='session')
    {
        path='/render/'+type+'/'+id;
    }else
    {
        path='/render/'+type+'/'+id;
    }

    $.get(path,function(result){
        reload('upload_list');
    });
}

function deleteupload(id)
{
    var path='/deleteupload/'+id;
    $.get(path,function(result){
        reload('upload_list');
    });
}

function sendcleanup(id)
{
    var path='/cleanup/'+id;
    $.get(path,function(result){
        reload('upload_list');
    });
}

function viewsession(id,type,target)
{
    var trgt=$('#'+target+id);
    if ($(trgt).html()=='')
    {
        var path='/sessions/'+type+'/'+id;
        $.get(path,function(result){
            $(trgt).html(result);
        })
    }else
    {
        $(trgt).html('');
    }

}

function viewframes(id,type,target)
{
    var trgt=$('#'+target);
    var path='/frames/'+type+'/'+id;
    $.get(path,function(result){
        $(trgt).html(result);
        $('#slideshow_dialog').modal('show');
        $('#slideshow').carousel('cycle');
    })
}

Dropzone.autoDiscover = false;
// or disable for specific dropzone:
// Dropzone.options.myDropzone = false;





