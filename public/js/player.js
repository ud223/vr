$(document).ready(function() {

});

function sharingIt(obj, pagePrefix) {
    var $this = $(obj).closest('.list-item');
    var sid = $this.attr('sid');
    var title = $this.find('.lt').html();
    var img = pagePrefix + $('.alt-img').attr('src');
    if (sid) {
        // sharing special
        pagePrefix = "?special=" + sid;
    } else {
        // sharing program
        sid = $('#tv-listbar').attr('sid');
        pagePrefix += "?special=" + sid;
        var pid = $this.attr('id');
        pagePrefix += "&program=" + pid;
    }

    var content = $('.sharing-popup').clone(true);
    content.find('.cts-1 p span').html("\"" + title + "\"");
    content.find('.weibo').click(function() {
        shareTSina(title, pagePrefix, '', img);
    });
    content.find('.qqweibo').click(function() {
        shareToWb(title, pagePrefix, '', img);
    });
    content.find('.weixin').click(function() {
        var container = $(this).closest('.sharing-popup');
        container.find('.hide-qrcode').show();
        new QRCode(container.find('.cts-2 .qr').empty().get(0), {
            text: pagePrefix,
            width: 270,
            height: 270,
            colorDark: "#333333",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        //qrcode.clear(); // clear the code. 
        //qrcode.makeCode("http://naver.com"); // make another code.
        
        container.find('.cts-2').animate({
            left: 0
        }, 200, 'linear');
    });
    $.popup({
        content: content,
        containerBoxSelector: '#tv',
        height: 382,
        width: 352});
    return false;
}


function switchFullscreen() {
    var docElm = $('#tv').get(0);
    var ff = $('#tv-ctrl-fullscreen').attr('full');
    var isFullscreen = ff === 'yes' ? true : false;
    if (isFullscreen) {
        if (document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
        } else if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        }
    } else {
        if (docElm.requestFullscreen) {
            docElm.requestFullscreen();
        } else if (docElm.mozRequestFullScreen) {
            docElm.mozRequestFullScreen();
        } else if (docElm.webkitRequestFullScreen) {
            docElm.webkitRequestFullScreen();
        }
    }
    $('#tv-ctrl-fullscreen').attr('full', isFullscreen ? 'no' : 'yes');
}

function clickSeek(obj) {
    seek(xClick(obj));
}

function xClick(obj) {
    var x = event.clientX - $(obj).offset().left;
    var w = $(obj).width();
    var perc = Math.ceil(x / w * 100);
    if (perc >= 99) {
        perc = 99;
    }
    return perc;
}

function seek(perc, time) {
    var self = PLAYER;
    var seeking_id = setInterval(function() {
        var seeking_in_id = $('body').data('seeking_id');
        var readyState = self.readyState;
        if (readyState === 4) {
            $('body').data('seeking_id', null);
            clearInterval(seeking_in_id);

            var val = 0;
            if (perc) {
                val = self.duration * (perc / 100);
            } else if (time) {
                val = time;
            }
            self.currentTime = val;
        }
    }, 500);
    $('body').data('seeking_id', seeking_id);
}