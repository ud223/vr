
function removeObject($this, url, containerSelector, valSelector) {
    if (!confirm('确认删除该对象？')) {
        return;
    }
    if (!containerSelector) {
        containerSelector = '.itm';
    }
    if (!valSelector) {
        valSelector = '.tmp';
    }
    
    var container = $this.closest(containerSelector);
    var id = container.find(valSelector).val();
    var data = {id: id};

    $.ajax({
        url: url,
        dataType: 'json',
        data: data,
        type: 'POST',
        success: function(response) {
            if (response) {
                container.fadeOut(400, function() {
                    container.remove();
                });
            } else {
                alert('删除失败');
            }
        }
    });
}
Number.prototype.timeFormat = function() {
    var _SPM = 60;
    var integer = parseInt(this);
    var min = parseInt(integer / _SPM);
    var sec = integer - min * _SPM;

    if (min < 10) {
        min = "0" + min;
    }
    if (sec < 10) {
        sec = "0" + sec;
    }
    var result = min + ":" + sec;
    return result;
};
function inArray(val, arr) {
    var result = false;
    for (var k in arr) {
        if (arr[k] === val) {
            result = true;
            break;
        }
    }
    return result;
}
(function($) {

    $.fn.clickToggle = function(option) {
        var setting = {
            closeBtnSelector: '.close-btn',
            action: 'show'
        };
        var $this = $(this);
        $.extend(setting, option);
        $this.data('setting', setting);
        $(event).stopPropagation();
        var stopPropFunc = function() {
            $(event).stopPropagation();
        };
        $this.bind('click', stopPropFunc);
        $this.toggle();
        var toggleFunc = function() {
            $this.hide();
            $this.unbind('click', stopPropFunc);
            $(window).unbind('click', toggleFunc);
        };
        $this.find(setting.closeBtnSelector).click(function() {
            toggleFunc();
        });
        $(window).bind('click', toggleFunc);
    };
    $.fn.autoToggle = function(option) {
        var setting = {
            delay: 2000,
            animationDuration: 300,
            animationDirection: 'top',
            animationDistance: '90',
            beforeHide: false,
            beforeDisplay: false
        };
        $.extend(setting, option);

        // append "null cursor" style into body for once ...
        var style_id = 'ct-null-cursor-style';
        if (!$('body').data(style_id)) {
            var style = $("<style>.null-cursor {cursor: none;}</style>");
            // 360浏览器里面不能使用下面方式去掉鼠标，否则360会强制添加鼠标图案导致画面不停上下切换
            $('body').append(style);
            $('body').data(style_id, 'done');
        }
        var $this = $(this);
        $(window).on('mousemove', function() {
            var prev_mouse = $this.data('prev_mouse');
            var current_mouse = {
                x: event.clientX,
                y: event.clientY
            };
            if (prev_mouse) {
                var MIN_DIST = 20;
                if (Math.abs(prev_mouse.x - current_mouse.x) < MIN_DIST && Math.abs(prev_mouse.y - current_mouse.y) < MIN_DIST) {
                    return;
                } else {
                    // nothing to do for now ...
                }
            }
            $this.data('prev_mouse', current_mouse);

            var delay = setting.delay;
            var animationDuration = setting.animationDuration;
            // display
            var display = {};
            display[setting.animationDirection] = 0;
            var hide = {};
            hide[setting.animationDirection] = '-' + setting.animationDistance + 'px';
            var position = {
                display: display,
                hide: hide
            };
            if ($this.data('doing') !== 'true') {
                if (setting.beforeDisplay && typeof (setting.beforeDisplay) === 'function')
                    setting.beforeDisplay();
                $this.data('doing', 'true');
                $this.stop().animate(position.display, animationDuration, 'linear', function() {
                    $this.data('doing', 'false');
                });
            }
            // hide
            var hide_time_out = $this.attr('id') + 'hidetimeout';
            var hide_time_out_id = $('body').data(hide_time_out);
            if (hide_time_out_id) {
                clearTimeout(hide_time_out_id);
            }
            $this.parent().removeClass('null-cursor');
            $('body').data(hide_time_out, setTimeout(function() {
                if (setting.beforeHide && typeof (setting.beforeHide) === 'function')
                    setting.beforeHide();
                $this.parent().addClass('null-cursor');
                $this.stop().animate(position.hide, animationDuration, 'linear');
            }, delay));
        });
    };


    $.autoToggleListRestart = function() {
        $('body').data('auto-toggle-list-stop', false);
    };
    $.autoToggleListStop = function() {
        $('body').data('auto-toggle-list-stop', true);
    };
    $.autoToggleList = function(targets) {
        if (!targets || !targets.length)
            throw 'null array';

        var setting = {
            delay: 3000,
            animationDuration: 300,
            animationDirection: 'top',
            animationDistance: '90',
            beforeHide: false,
            beforeDisplay: false
        };
        var option = {
        };
        $.each(targets, function() {
            var newOption = $.extend({}, option, this.option);
            this.option = newOption;
        });

//        $.extend(setting, option);

        // append "null cursor" style into body for once ...
        var styleId = 'ct-null-cursor-style';
        if (!$('body').data(styleId)) {
            var style = $("<style>.null-cursor {cursor: none;}</style>");
            // 360浏览器里面不能使用下面方式去掉鼠标，否则360会强制添加鼠标图案导致画面不停上下切换
            $('body').append(style);
            $('body').data(styleId, 'done');
        }
        $(window).on('mousemove', function() {
            if ($('body').data('auto-toggle-list-stop')) {
                return;
            }

            // record mouse moving
            var prevMouse = $('body').data('prevmouse');
            var currentMouse = {
                x: event.clientX,
                y: event.clientY
            };
            if (prevMouse) {
                var MINDIST = 20;
                if (Math.abs(prevMouse.x - currentMouse.x) < MINDIST && Math.abs(prevMouse.y - currentMouse.y) < MINDIST) {
                    return;
                } else {
                    // nothing to do for now
                }
            }
            $('body').data('prevmouse', currentMouse);

            var delay = setting.delay;

            $.each(targets, function() {
                // show
                var target = $(this.htmlObject);
                target.fadeIn(setting.animationDuration);
            });

            // hide
            var htoid = $('body').data('hide_time_out');
            if (htoid) {
                clearTimeout(htoid);
            }
            $('body').data('hide_time_out', setTimeout(function() {
                if ($('body').data('auto-toggle-list-stop') || $('body').data('auto-toggle-list-switch') === 'off') {
                    return;
                }
                $.each(targets, function() {
                    var target = $(this.htmlObject);
                    target.fadeOut(setting.animationDuration);
                });
            }, delay));

        });
    };

    $.fn.stopPropagation = function() {
        var e = $(this).get(0);
        if (e.stopPropagation) {
            e.stopPropagation();
        } else {
            e.cancelBubble = true;
        }
    };
})(jQuery);

