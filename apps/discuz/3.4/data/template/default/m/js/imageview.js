var imageListInit = function(post) {
    var ret = '';
    ret = post.message.replace(/(<img[^>]*?)(src\s*?=\s*?([\'"])([^>]+?)\3)([^>]*?>)/ig, function($0, $1, $2, $3, $4, $5) {
        return $1 + 'class="lazy" data-original="' + $4 + '"' + $5;
    });
    for(var i in post.imagelist) {
        if(post.attachments[post.imagelist[i]].url) {
            var src = TOOLS.attachUrl(post.attachments[post.imagelist[i]].url) + post.attachments[post.imagelist[i]].attachment;
        } else {
            var src = TOOLS.attachUrl(post.attachments[post.imagelist[i]].attachment);
        }
        if(post.attachments[post.imagelist[i]].thumb != 0) {
            ret += '<br /><img class="lazy" data-original="' + src + '.thumb.jpg" file="' + src + '" attach="' + post.attachments[post.imagelist[i]].pid + '" />';
        } else {
            ret += '<br /><img class="lazy" data-original="' + src + '" attach="' + post.attachments[post.imagelist[i]].pid + '" />';
        }
    }
    return ret;
};

var imageviewCommon = function(rule, subRule) {
    subRule = subRule || 'img';
    var pics = [];
    if (TOOLS.isWX()) {
        $(rule + ' ' + subRule).each(function(e, i) {
            if($(i).attr('attach')) {
                if(!pics[$(i).attr('attach')]) {
                    pics[$(i).attr('attach')] = [];
                }
                var src = $(i).attr('file') ? $(i).attr('file') : ($(i).attr('data-original') ? $(i).attr('data-original') : $(i).attr('src'));
                pics[$(i).attr('attach')].push(src);
            }
        });
    }
    $(rule + ' ' + subRule).on('click', function(e) {
        var src = $(this).attr('file') ? $(this).attr('file') : ($(this).attr('data-original') ? $(this).attr('data-original') : $(this).attr('src'));
        if (TOOLS.isWX()) {
            WeixinJSBridge.invoke('imagePreview', {
                "current": src,
                "urls": $(this).attr('attach') ? pics[$(this).attr('attach')] : [src]
            });
        } else {
            if (!$('#imageView')[0]) {
                $('body').append('<div id="imageView" class="slide-view" style="display:none;"><ul class="pv-inner"><li></li></ul></div>');
            }
            $('#imageView .pv-inner li').html('');
            $('#imageView').css('top', window.scrollY + 'px');
            $(window).on('scroll', function() {
                if($('#imageView').css('display') !== 'none') {
                    $('#imageView').css('top', window.scrollY + 'px');
                }
            });
            $(document).on('touchmove', function(e) {
                if($('#imageView').css('display') !== 'none') {
                    e.preventDefault();
                }
            });
            var img = new Image();
            img.src = src;
            img.onload = function(){
                $('#imageView .pv-inner li').append(img);
                $('#imageView').show();
                $('#imageView .pv-inner').css('top', ((window.innerHeight - $('#imageView .pv-inner img').height()) / 2) + 'px');
                $('body').css('overflow-y', 'hidden');
            };
            $('#imageView').on('click', function() {
                $(this).hide();
                $('body').css('overflow-y', 'auto');
            });
        }
    });
};