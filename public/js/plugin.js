
// jquery.rotate.js
(function($) {
    function initData($el) {
        var _ARS_data = $el.data('_ARS_data');
        if (!_ARS_data) {
            _ARS_data = {rotateUnits: 'deg', scale: 1, rotate: 0};
            $el.data('_ARS_data', _ARS_data);
        }
        return _ARS_data;
    }

    function setTransform($el, data) {
        $el.css('transform', 'rotate(' + data.rotate + data.rotateUnits + ') scale(' + data.scale + ',' + data.scale + ')');
    }

    $.fn.rotate = function(val) {
        var $self = $(this), m, data = initData($self);
        if (typeof val === 'undefined') {
            return data.rotate + data.rotateUnits;
        }
        m = val.toString().match(/^(-?\d+(\.\d+)?)(.+)?$/);
        if (m) {
            if (m[3]) {
                data.rotateUnits = m[3];
            }
            data.rotate = m[1];
            setTransform($self, data);
        }
        return this;
    };
    $.fn.scale = function(val) {
        var $self = $(this), data = initData($self);
        if (typeof val === 'undefined') {
            return data.scale;
        }
        data.scale = val;
        setTransform($self, data);
        return this;
    };
    var curProxied = $.fx.prototype.cur;
    $.fx.prototype.cur = function() {
        if (this.prop === 'rotate') {
            return parseFloat($(this.elem).rotate());
        } else if (this.prop === 'scale') {
            return parseFloat($(this.elem).scale());
        }
        return curProxied.apply(this, arguments);
    };
    $.fx.step.rotate = function(fx) {
        var data = initData($(fx.elem));
        $(fx.elem).rotate(fx.now + data.rotateUnits);
    };
    $.fx.step.scale = function(fx) {
        $(fx.elem).scale(fx.now);
    };
    var animateProxied = $.fn.animate;
    $.fn.animate = function(prop) {
        if (typeof prop['rotate'] != 'undefined') {
            var $self, data, m = prop['rotate'].toString().match(/^(([+-]=)?(-?\d+(\.\d+)?))(.+)?$/);
            if (m && m[5]) {
                $self = $(this);
                data = initData($self);
                data.rotateUnits = m[5];
            }
            prop['rotate'] = m[1];
        }
        return animateProxied.apply(this, arguments);
    };
})(jQuery);

(function($) {
    var DURATION = 100;
    $.fn.setSelectValue = function(options) {
        if (!options) {
            options = {};
        }
        var settings = {
            cls: "",
            tagName: "select"
        };
        $.extend(settings, options);
        var $this = $(this);
        if ($this.length && ($this.get(0).tagName.toLowerCase() === settings.tagName)) {
            var val = $this.attr('value');
            if (val) {
                $.each($this.find('option'), function() {
                    if (val === $(this).attr('value')) {
                        $(this).prop('selected', true);
                    } else {
                        $(this).prop('selected', false);
                    }
                });
            }
        } else {
            throw("dev exception : wrong tagname");
        }
    };
    var photoSelectorMethods = {
        init: function(options, save) {
            var $this = $(this);
            $this.prop('disabled', true);
            if (!options) {
                options = {};
            }
            // 初始化Setting默认值
            var settings = {
                separator: ';',
                multi: true,
                url: '/manage/photo/list',
                phototypeUrl: '/manage/phototype/list',
                thumbnailOnly: true
            };
            // 生成modal框
            var generateModal = function($this) {
                var modalId = 'gy' + new Date().getTime();
                // 避免重复id
                if ($('#' + modalId).length > 0)
                    modalId += new Date().getTime();
                var modalHtml = '<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">';
                modalHtml += '<div class="modal-dialog">';
                modalHtml += '<div class="modal-content">';
                modalHtml += '<div class="modal-header">';
                modalHtml += '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
                modalHtml += '<h4 class="modal-title">添加图片</h4>';
                modalHtml += '</div>';
                modalHtml += '<div class="modal-menu"></div>';
                modalHtml += '<div class="modal-body"></div>';
                modalHtml += '<div class="modal-page"></div>';
                modalHtml += '<div class="modal-footer">';
                modalHtml += '<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>';
                modalHtml += '</div>';
                modalHtml += '</div>';
                modalHtml += '</div>';
                modalHtml += '</div>';
                var modalPopup = $(modalHtml).attr('id', modalId);
                var selectBtn = $("<button>")
                        .attr('type', 'button')
                        .attr('class', 'btn btn-primary select-btn')
                        .html('选择');
                selectBtn.click(function() {
                    $this.photoSelector('save');
                });
                modalPopup.find('.modal-footer').append(selectBtn);
                $('body').append(modalPopup);
                return modalId;
            };
            // 合并settings
            settings.modalId = generateModal($this);
            $.extend(settings, options);
            $this.data('settings', settings);
            $this.append('<div class="gy-photo-selected"></div>');
            // “选择图片”按钮
            var launchBtn = $("<input>")
                    .attr('type', 'button')
                    .attr('data-toggle', 'modal')
                    .attr('data-target', '#' + settings.modalId)
                    .addClass('btn btn-success btn-sm')
                    .val('选择图片');
            launchBtn.click(function() {
                $this.photoSelector('start');
            });
            $this.append(launchBtn);
            $this.prop('disabled', false);
            // 设置save初始化图片
            if (typeof save === 'object') {
                save = JSON.stringify(save);
            }
            if (save) {
                $this.attr('save', save);
                $this.photoSelector('display');
            }

            $('body').append($this.photoSelector('style'));
        },
        style: function() {
            // write css
            var style = "<style>";
            style += ".modal-dialog {width:85%;}";
            style += ".modal-page {margin:15px; text-align:center;}";
            style += ".gallery {display:inline-block; margin:5px; padding:5px;position:relative;}";
            style += ".gallery:hover {background-color:skyblue;cursor:pointer;}";
            style += ".gallery.choosen {background:#FFF !important; cursor:default !important;}";
            style += ".gallery .checkbt {display:inline-block; margin:5px;}";
            style += ".gallery-img {height:75px; width:100px;}";
            style += ".gallery.selected {background-color:lightgreen !important;}";
            style += ".modal-page input {margin:0 2px;}";
            style += ".gy-sd {border:1px solid #eee;display:inline-block;padding:10px;margin:0 5px 5px 0;position:relative;}";
            style += ".gy-sd img {height:75px;width:100px}";
            style += ".gy-sd .rm, .gy-sd .lt, .gy-sd .rt {background:#FFF;border:none;color:#888;border:0 0 0 2px;height:24px;line-height:24px;margin:0;right:0; top:0;padding:0;position:absolute;width:24px;}";
            style += ".gy-sd .lt, .gy-sd .rt {left:0; right:auto; top:34px;}";
            style += ".gy-sd .rt {left:auto; right:0;}";
            style += ".gallery .label-success {display:none;position:absolute;left:0;top:0;}";
            style += ".gallery.choosen {opacity:0.5}";
            style += ".gallery.choosen .label-success {display:block;}";
            style += ".modal-menu .nav {background:#F8F8F8; padding: 10px 20px 0 20px;}";
            style += ".modal-menu .nav * {font-size:smaller;}";
            style += ".gallery .label-warning {font-size:smaller; position:absolute; right:0; top:0}";
            style += "</style>";
            return style;
        },
        renderMenu: function(resource) {
            var $this = $(this), settings = $this.data('settings'), modalId = settings.modalId, $modal = $('#' + modalId);
            var modalMenu = $modal.find('.modal-menu');
            modalMenu.empty();
            var addLi = function(containerUl, name, phototypeId, description, liCls) {
                var $li = $("<li>").attr('phototype-id', phototypeId);
                if (liCls)
                    $li.addClass(liCls);
                var $a = $("<a>").attr("href", "javascript:void(0)")
                        .attr('title', description)
                        .html(name);
                $li.append($a);
                $li.click(function() {
                    var obj = $(this).closest('li');
                    var cls = "active";
                    if (!obj.hasClass(cls)) {
                        obj.siblings('li').removeClass(cls);
                        obj.addClass(cls);
                        // request
                        var pid = obj.attr('phototype-id');
                        $modal.attr('loaded', null);
                        $this.photoSelector('request', {phototype: pid});
                    }
                });
                containerUl.append($li);
            };
            var $ul = $("<ul>").addClass("nav nav-tabs");
            addLi($ul, "全部", null, null);
            $.each(resource, function() {

                var item = $(this);
                var name = item[0].name;
                var description = item[0].description;
                var phototypeId = item[0].id;
                addLi($ul, name, phototypeId, description);
            });
            modalMenu.append($ul);
            $($ul.find('li').get(0)).click();
        },
        renderPhoto: function(resource) {
            var $this = $(this), settings = $this.data('settings'), modalId = settings.modalId, $modal = $('#' + modalId);
            var modalBody = $modal.find('.modal-body');
            modalBody.empty();
            if (resource) {
                var selected = $modal.attr('selected');
                var arr = false;
                if (selected) {
                    arr = selected.split(settings.separator);
                }
                $.each(resource, function() {
                    var item = $(this);
                    var name = item[0].name;
                    var type = item[0].type;
                    var thumbnail = item[0].thumbnail;
                    var img = $('<img>')
                            .addClass('gallery-img');
                    var gallery = $('<div>')
                            .addClass('gallery')
                            .attr('name', name)
                            .attr('type', type);
                    if (!thumbnail) {
                        img.attr('src', item[0].path.orig);
                        gallery.append("<label class='label label-warning'>无缩略</label>");
                    } else {
                        img.attr('src', item[0].path.small);
                        gallery.attr('thumbnail', true);
                    }
                    gallery.append(img);
                    gallery.append($("<span>").addClass('label label-success').html('已选择'));
                    gallery.click(function() {
                        if (settings.thumbnailOnly && !$(this).attr('thumbnail')) {
                            alert("抱歉，不能选择该图片（因为没有缩略图）");
                            return false;
                        }

                        if (!gallery.hasClass('choosen')) {
                            if (!settings.multi) {
                                $(this).closest('.modal-body')
                                        .find('.gallery.selected')
                                        .removeClass('selected');
                            }
                            $(this).closest('.gallery')
                                    .toggleClass('selected');
                        }
                    });
                    if ($.inArray(name, arr) >= 0) {
                        gallery.addClass('selected');
                    }
                    modalBody.append(gallery);
                });
            }
        },
        renderPagebar: function(param) {
            var $this = $(this), settings = $this.data('settings'), modalId = settings.modalId, $modal = $('#' + modalId);
            // 显示pagebar
            var pagebar = $modal.find('.modal-page');
            pagebar.empty();
            for (var i = 1; i <= param.count; i++) {
                var ACTIVECLS = 'active';
                var pageBtn = $("<input>").attr('type', 'button').attr('page', i).addClass('btn btn-default btn-sm').val(i);
                if (param.page === i) {
                    pageBtn.addClass(ACTIVECLS);
                }
                pageBtn.click(function() {
                    var selfBtn = $(this);
                    if (!selfBtn.hasClass(ACTIVECLS)) {
                        // 调整class
                        selfBtn.siblings('.btn-default').removeClass(ACTIVECLS);
                        selfBtn.addClass(ACTIVECLS);
                        // 刷新图片
                        var page = selfBtn.attr('page');
                        var data = {page: page};
                        // 请求对应的phototype id
                        var phototype = $modal.find('.modal-menu .active').attr('phototype-id');
                        if (phototype)
                            data.phototype = phototype;
                        $this.photoSelector('request', data);
                    }
                });
                pagebar.append(pageBtn);
            }
        },
        renderChoosen: function() {
            var $this = $(this), settings = $this.data('settings'), modalId = settings.modalId, $modal = $('#' + modalId);
            var s = $this.attr('save');
            if (s && s !== "{}") {
                s = JSON.parse(s);
            }
            var galleries = $modal.find('.gallery');
            $.each(galleries, function() {
                var name = $(this).attr('name');
                if (s && s[name]) {
                    // 标为已选择状态
                    $(this).addClass('choosen');
                } else {
                    // 取消掉的照片恢复可选状态
                    $(this).removeClass('choosen');
                }
            });
        },
        request: function(param) {
            var $this = $(this), settings = $this.data('settings'), modalId = settings.modalId, $modal = $('#' + modalId);
            if (!param)
                param = {};
            if (!param.page)
                param.page = 1;
            var url = settings.url;
            var data = {format: 'json', page: param.page};
            if (param.phototype)
                data.phototype = param.phototype;
            $.ajax({
                url: url,
                dataType: 'json',
                data: data,
                success: function(response) {
                    if (response.code === 200) {
                        var count = response.count;
                        var resource = response.data;
                        // 显示图片
                        $this.photoSelector('renderPhoto', resource);
                        if (!$modal.attr('loaded')) {
                            // 显示pagebar
                            $this.photoSelector('renderPagebar', {page: param.page, count: count});
                            $modal.attr('loaded', true);
                        }
                        $this.photoSelector('renderChoosen');
                    }
                }
            });
        },
        requestMenu: function() {
            var $this = $(this), settings = $this.data('settings'), modalId = settings.modalId, $modal = $('#' + modalId);
            var phototypeUrl = settings.phototypeUrl;
            if (!phototypeUrl)
                return;
            var data = {format: 'json'};
            $.ajax({
                url: phototypeUrl,
                dataType: 'json',
                data: data,
                success: function(response) {
                    if (response.code === 200) {
                        var resource = response.data;
                        if (!$modal.attr('loaded')) {
                            // 显示menu
                            $this.photoSelector('renderMenu', resource);
                        }
                    }
                }
            });
        },
        start: function() {
            var $this = $(this), settings = $this.data('settings'), modalId = settings.modalId, $modal = $('#' + modalId);
            if (!$modal.attr('loaded')) {
                $this.photoSelector('requestMenu');
            } else {
                $this.photoSelector('renderChoosen');
            }
        },
        save: function() {
            var $this = $(this), settings = $this.data('settings'), modalId = settings.modalId, $modal = $('#' + modalId);
            var arr = $modal.find('.selected');
            // 检查图片是否已经存在
            var se = $this.attr('save');
            var sn = new Array();
            if (se) {
                se = JSON.parse(se);
                $.each(se, function(name, path) {
                    sn.push(name);
                });
            } else {
                se = {};
            }
            if (!settings.thumbnailOnly) {
                se = {};
            }
            $.each(arr, function() {
                var name = $(this).attr('name');
                if ($.inArray(name, sn) >= 0) {
                    // 忽略
                } else {
                    se[name] = $(this).find('.gallery-img').attr('src');
                }

                $(this).removeClass('selected');
            });
            $this.attr('save', JSON.stringify(se));
            $modal.modal('hide');
            $this.photoSelector('display');
        },
        display: function() {
            // 选中图片显示在gy-photo container中
            var $this = $(this), settings = $this.data('settings'), modalId = settings.modalId, $modal = $('#' + modalId);
            var save = $this.attr('save');
            var gyPhotoSelected = $this.find('.gy-photo-selected');
            gyPhotoSelected.empty();
            if (save) {
                save = JSON.parse(save);
                // 重组save值
                var redisplay = function(trigger) {
                    var result = "";
                    var gs = $this.find('.gy-sd');
                    $.each(gs, function() {
                        if (!result)
                            result = {};
                        var n = $(this).attr('name');
                        var p = $(this).find('img').attr('src');
                        result[n] = p;
                    });
                    if (result)
                        result = JSON.stringify(result);
                    $this.attr('save', result);
                };
                $.each(save, function(name, path) {
                    var item = $('<div>').addClass('gy-sd')
                            .attr('name', name);
                    var rm = $("<button>").attr('type', 'button').html('&times;').addClass('rm');
                    rm.click(function() {
                        // 删除
                        var gysdItem = $(this).closest('.gy-sd');
                        gysdItem.remove();
                        redisplay($(this));
                        $this.photoSelector('renderChoosen');
                    });
                    item.append(rm);

                    if (settings.multi) {
                        var lt = $("<button>").attr('type', 'button').html('&lt;').addClass('lt');
                        lt.click(function() {
                            var gysdItem = $(this).closest('.gy-sd');
                            var ltElem = gysdItem.prev();
                            if (ltElem.length > 0) {
                                // 左移
                                var gysdItemCopy = gysdItem.clone(true);
                                gysdItem.remove();
                                ltElem.before(gysdItemCopy);
                                redisplay($(this));
                            }
                        });
                        item.append(lt);
                        var rt = $("<button>").attr('type', 'button').html('&gt;').addClass('rt');
                        rt.click(function() {
                            var gysdItem = $(this).closest('.gy-sd');
                            var rtElem = gysdItem.next();
                            if (rtElem.length > 0) {
                                // 右移
                                var gysdItemCopy = gysdItem.clone(true);
                                gysdItem.remove();
                                rtElem.after(gysdItemCopy);
                                redisplay($(this));
                            }
                        });
                        item.append(rt);
                    }
                    item.append($("<img>").attr('src', path));
                    gyPhotoSelected.append(item);
                });
            }
        }
    };
    /*
     * 启动方法
     * @param {type} method     传递字符串，将被识别为方法名; 传递对象，将作为init方法的参数（一般是options）, 并调用init方法
     * @returns {unresolved}
     */
    $.fn.photoSelector = function(method) {
        if (photoSelectorMethods[method]) {
            return photoSelectorMethods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return photoSelectorMethods.init.apply(this, arguments);
        } else {
            $.error('The method ' + method + ' does not exist in $.uploadify');
        }
    };
    $.queryString = {
        data: {},
        initial: function() {
            var aPairs, aTmp;
            var queryString = new String(window.location.search);
            queryString = queryString.substr(1, queryString.length); //remove   "?"     
            aPairs = queryString.split("&");
            for (var i = 0; i < aPairs.length; i++) {
                aTmp = aPairs[i].split("=");
                this.data[aTmp[0]] = aTmp[1];
            }
        },
        getValue: function(key) {
            return this.data[key];
        }
    };
    $.fn.integerInput = function() {
        var $this = $(this);
        $this.bind("keydown", function(event) {
            if (event.keyCode > 57 || event.keyCode < 48) {
                if (event.keyCode != 8) {
                    return false;
                }
            }
        });
        $this.bind('contextmenu', function(e) {
            return false;
        });
    };
    $.fn.xCenter = function() {
        var $this = $(this);
        var parent = $this.parent();
        var pw = parent.width();
        if ($this.css('position') != 'absolute' || $this.css('position') != 'fixed') {
            $this.css('position', 'absolute');
        }
        $this.css('left', (pw - $this.width()) / 2);
    };

    /* LOADING (START) */
    $.waiting = function(msg, container) {
        if ($('.rotatedivwrapper').length > 0) {
            return;
        }
        var rotate_style_id = 'rotate-style';
        if ($('#' + rotate_style_id).length === 0) {
            var style = "<style id='" + rotate_style_id + "'>";
            style += ".rotatedivwrapper {background:rgba(0,0,0,0.6);height:100%;left:0;position:fixed;top:0;width:100%;z-index:30;}";
            style += ".rotatedivwrapper .rotatediv {background:url(/img/loading.png);height:120px;left:50%;margin-left:-60px;margin-top:-60px;position:absolute;top:50%;width:120px;}";
            style += "</style>";
            $('body').append(style);
        }
        var obja = $("<div>").addClass('rotatedivwrapper').append($('<div>').addClass('rotatediv'));
        if (!container) {
            container = $('body');
        }
        container.append(obja);
        var wp = $('.rotatedivwrapper');
        var wp_w = wp.width();
        var wp_h = wp.height();

        var x = (($(window).width()) / 2) - (wp_w / 2);
        var y = ($(window).height() - wp_h) / 2;

        wp.css('left', x);
        wp.css('top', y);

        $.rotateDiv();
        var intervalId = window.setInterval("$.rotateDiv()", 500);

        if (msg) {
            var msgObj = $('<div>').addClass('msg').html(msg);
            wp.append(msgObj);
        }

        wp.fadeIn('fast');
        wp.attr('intid', intervalId);
    };
    $.rotateDiv = function(easing, selector, duration) {
        if (!easing) {
            easing = 'linear';
        }
        if (!selector) {
            selector = '.rotatediv';
        }
        if (!duration) {
            duration = 500;
        }
        $(selector).animate({
            rotate: '+=360deg'
        }, duration, easing);
    };
    $.endWaiting = function() {
        var wp = $('.rotatedivwrapper');
        window.clearInterval(wp.attr('intid'));
        wp.attr('intid', null);
        wp.fadeOut('fast', function() {
            wp.remove();
        });
    };

    /* LOADING (END) */


    /* PAPER (START) */
    $.fn.paper = function(options, content) {
        var settings = {
            type: 'default',
            additionalClass: ''
        };
        $.extend(settings, options);
        var accepted_type_class = {'default': 'paper-default', 'danger': 'paper-danger', 'success': 'paper-success', 'warning': 'paper-warning'};
        if (!accepted_type_class[settings.type])
            throw 'invalid type for paper';
        var container = $('<div>')
                .addClass('paper')
                .addClass(accepted_type_class[settings.type]);
        if (settings.additionalClass)
            container.addClass(settings.additionalClass);
        var $this = $(this);
        $this.remove('.paper').prepend(container.html(content));
    };
    /* PAPER (END) */

    /* VIDEO (START) */
    var videoMethods = {
        init: function(option) {
            var setting = {
                src: 'default path',
                total: "#t3",
                current: "#t1",
                seekbar: "#tv-prog-seekbar",
                loaded: "#tv-prog-loaded",
                speakerButton: "#tv-spk",
                volumeBar: "#tv-spk-bar-prog",
                fullscreenButton: "#fullscreen",
                container: "#tv",
                onPlay: false,
                onPause: false,
                onEnded: false
            };
            var $this = $(this);
            var self = $this.get(0);
            $.extend(setting, option);
//            $this.data('setting', setting);
            // save setting
            var hidden = $('.video-value-host');
            hidden.data('setting', setting);

            $this.find('source').attr('src', setting.src);
            $this.video('update');
            var xClick = function(obj) {
                var x = event.clientX - $(obj).offset().left;
                var w = $(obj).width();
                var perc = Math.ceil(x / w * 100);
                if (perc >= 99) {
                    perc = 99;
                }
                return perc;
            };
            var yClick = function(obj) {
                var y = event.clientY - $(obj).offset().top;
                var h = $(obj).height();
                var perc = Math.ceil(y / h * 100);
                if (perc >= 99) {
                    perc = 99;
                }
                perc = 100 - perc;
                return perc;
            };
            $(setting.seekbar).on('click', function() {
                $this.video('seek', xClick(this));
            });
            $(setting.speakerButton).on('click', function() {
                $this.video('switchSpeaker');
            });
            $(setting.volumeBar).on('click', function() {
                $this.video('adjustVolume', yClick(this));
                $(event).stopPropagation();
            });
            $(setting.fullscreenButton).click(function() {
                $this.video("fullscreen");
                $(this).toggleClass('on');
            });
            if (setting.onPlay && typeof (setting.onPlay) === 'function') {
                $this.on('play', setting.onPlay);
            }
            if (setting.onPause && typeof (setting.onPause) === 'function') {
                $this.on('pause', setting.onPause);
            }
            if (setting.onEnded && typeof (setting.onEnded) === 'function') {
                $this.on('ended', setting.onEnded);
            }
        },
        setting: function(key, val) {
            var hidden = $('.video-value-host');
            var _setting = hidden.data('setting');
            if (key && val) {
                // save key value
                _setting[key] = val;
                hidden.data('setting', _setting);
            } else if (key) {
                // read key
                return _setting[key];
            } else {
                // read setting
                return _setting;
            }
        },
        play: function(callback) {
            var self = $(this).get(0);
            self.play();
            if (callback) {
                callback();
            }
        },
        pause: function(callback) {
            var self = $(this).get(0);
            self.pause();
            if (callback) {
                callback();
            }
        },
        restart: function(callback, src, delayPlay) {
            var self = $(this).get(0);
            $(this).video('setting', 'src', src);
            $(this).find('source').attr('src', src);
            self.load();
            if (!delayPlay) {
                self.play();
            }
            if (callback) {
                callback();
            }
        },
        update: function() {
            var self = $(this).get(0);
            var update_id = setInterval(function() {
                // update buffer
                var setting = $(self).video('setting');
                var currentTag = $(setting.current);
                var totalTag = $(setting.total);
                // update time
                if (self.duration) {
                    currentTag.html(self.currentTime.timeFormat());
                    totalTag.html(self.duration.timeFormat());
                    $(setting.loaded).css('width', (self.currentTime / self.duration) * 100 + '%');
                }
            }, 200);
            $('body').data('update_id', update_id);
        },
        seek: function(perc, time) {
            var self = $(this).get(0);
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
        },
        switchSpeaker: function() {
            var self = $(this).get(0), setting = $(this).video('setting');
            self.muted = !self.muted;
            $(setting.speakerButton).toggleClass('disable');
            if (self.muted) {
                $($(setting.volumeBar).children().get(0)).css('height', 0);
            } else {
                var w = self.volume * 100 + "%";
                $($(setting.volumeBar).children().get(0)).css('height', w);
            }
        },
        adjustVolume: function(perc) {
            var self = $(this).get(0), setting = $(this).video('setting');
            self.muted = false;
            $(setting.speakerButton).removeClass('disable');
            var v = Math.ceil(perc / 10);
            if (v < 1) {
                v = 1;
            }
            var w = v * 10 + "%";
            v = v / 10;
            self.volume = v;
            $($(setting.volumeBar).children().get(0)).css('height', w);
        },
        currentTime: function() {
            var self = $(this).get(0);
            return self.currentTime;
        },
        duration: function() {
            var self = $(this).get(0);
            return self.duration;
        },
        fullscreen: function() {
            if (!$(this).data('fullscreen'))
                $(this).data('fullscreen', false);
            var setting = $(this).video('setting');
            var docElm = $(setting.container).get(0);

            var isFullscreen = $(this).data('fullscreen');
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
            $(this).data('fullscreen', !isFullscreen);
        }
    };
    /*
     * 启动方法
     * @param {type} method     传递字符串，将被识别为方法名; 传递对象，将作为init方法的参数（一般是option）, 并调用init方法
     * @returns {unresolved}
     */
    $.fn.video = function(method) {
        var target = this;
        if (target.get(0).tagName != "VIDEO" && target.get(0).tagName != "AUDIO") {
            target = $(target.find('video').get(0));
        }

        if (videoMethods[method]) {
            return videoMethods[method].apply(target, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            if ($('.video-value-host').length === 0) {
                var html = $("<input>").attr('type', 'hidden').attr('class', 'video-value-host');
                $('body').append(html);
            }

            return videoMethods.init.apply(target, arguments);
        } else {
            $.error('The method ' + method + ' does not exist in $.uploadify');
        }
    };
    /* VIDEO (END) */

    /* POPUP (START) */
    $.extend({
        POPUPSETTINGSTMP: {
            id: "P_popup",
            content: "hello",
            modal: false,
            closebtn: true,
//            relocate: true,
            msg: "hello",
            height: 'auto',
            width: 500,
            padding: 25,
            textAlign: 'left',
            containerBoxSelector: 'body'
        },
        popup: function(options) {
            var $popupsettings = $.extend({}, $.POPUPSETTINGSTMP, options);
            var id = $popupsettings.id;
            var w = $(window);
            $('#' + id).remove();
            var popupFrame = $('<div>').attr('id', id);
            var w = $popupsettings.width;
            var h = $popupsettings.height;
            var content = $popupsettings.content;
            content = (typeof (content) === 'string') ? $(content) : content;
            content.addClass('P_bg')
                    .css('padding', $popupsettings.padding)
                    .css('margin-left', "-" + w / 2 + "px")
                    .css('width', w)
                    .css('height', h)
                    .css('text-align', $popupsettings.textAlign);

            popupFrame.show();
            var clsbtn = $('<span>').addClass('P_closebtn').html("&times;");
            $($popupsettings.containerBoxSelector).append(popupFrame.append($(content).append(clsbtn)));
            var mt = "-" + $(content).height() / 2 + "px";
            $(content).css('margin-top', mt);

            if (!$popupsettings.modal) {
                popupFrame.children().click(function(e) {
                    e.stopPropagation();
                });
                popupFrame.click(function(e) {
                    clsbtn.click();
                });
            }

            clsbtn.click(function() {
                $(this).closest('#' + id).remove();
            });

            if ($popupsettings.closebtn) {
                clsbtn.show();
            }
        },
        alertbox: function(options) {
            var _settings = {
                width: 280,
                height: 120,
                textAlign: 'center'
            };
            $.extend(_settings, options);
            _settings.modal = true;
            var $popupsettings = $.extend({}, $.POPUPSETTINGSTMP, _settings);
            var id = "P_alertbox";
            var msg = "";
            if ($popupsettings.msg) {
                msg = $popupsettings.msg;
            }
            var wp;
            if (typeof (msg) === 'object')
                wp = $('<div>').addClass('P_wp').append(msg);
            else
                wp = $('<div>').addClass('P_wp').html(msg);
            var alertContent = $('<div>').attr('id', id).addClass('P_popupbg').append(wp);

            $popupsettings.content = alertContent;
            $.popup($popupsettings);
        },
        confirm: function(options) {
            var _settings = {
                width: 280,
                height: 120,
                textAlign: 'center',
                header: '',
                msg: '所否确定该操作？',
                confirmText: '是',
                cancelText: '否',
                confirmCallback: false,
                cancelCallback: false
            };
            $.extend(_settings, options);
            _settings.modal = true;
            var $popupsettings = $.extend({}, $.POPUPSETTINGSTMP, _settings);
//            $popupsettings.closebtn = false;
            var id = "P_confirm";

            var header = "";
            if ($popupsettings.header) {
                header = $popupsettings.header;
            }
            var msg = "";
            if ($popupsettings.msg) {
                msg = $popupsettings.msg;
            }
            var wp;

            if (typeof (header) === 'object')
                wp = $('<div>').addClass('P_wp_header').css('padding', 15).append(header);
            else
                wp = $('<div>').addClass('P_wp_header').css('padding', 15).html(header);
            if (typeof (msg) === 'object')
                wp = $('<div>').addClass('P_wp_msg').css('padding', 15).append(msg);
            else
                wp = $('<div>').addClass('P_wp_msg').css('padding', 15).html(msg);

            var cancel = $('<button>').attr('class', 'P_confirm_btn').attr('action', 'cancel').attr('type', 'button').html($popupsettings.cancelText);
            cancel.click(function() {
                $.popupclose();
                if ($popupsettings.cancelCallback) {
                    $popupsettings.cancelCallback();
                }
            });
            var confirm = $('<button>').attr('class', 'P_confirm_btn').attr('action', 'confirm').attr('type', 'button').html($popupsettings.confirmText);
            confirm.click(function() {
                $.popupclose();
                if ($popupsettings.confirmCallback) {
                    $popupsettings.confirmCallback();
                }
            });
            var btns = $('<div>').attr('class', 'P_confirm_btns').append(confirm).append(cancel);
            wp.append(btns);

            var confirmContent = $('<div>').attr('id', id).addClass('P_popupbg').append(wp);
            $popupsettings.padding = 0;
            $popupsettings.content = confirmContent;
            $.popup($popupsettings);
        },
        popupclose: function() {
            var id = $.POPUPSETTINGSTMP.id;
            $('#' + id).fadeOut(150, function() {
                $(this).remove();
            });
        }
    });

    /* POPUP (END) */


})(jQuery);
/*!
 * jQuery Cookie Plugin v1.3.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */
(function(factory) {
    if (typeof define === 'function' && define.amd) {         // AMD. Register as anonymous module.
        define(['jquery'], factory);
    } else {
        // Browser globals.
        factory(jQuery);
    }
}(function($) {

    var pluses = /\+/g;
    function raw(s) {
        return s;
    }

    function decoded(s) {
        return decodeURIComponent(s.replace(pluses, ' '));
    }

    function converted(s) {
        if (s.indexOf('"') === 0) {
            // This is a quoted cookie as according to RFC2068, unescape
            s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
        }
        try {
            return config.json ? JSON.parse(s) : s;
        } catch (er) {
        }
    }

    var config = $.cookie = function(key, value, options) {

        // write
        if (value !== undefined) {
            options = $.extend({}, config.defaults, options);
            if (typeof options.expires === 'number') {
                var days = options.expires, t = options.expires = new Date();
                t.setDate(t.getDate() + days);
            }

            value = config.json ? JSON.stringify(value) : String(value);
            return (document.cookie = [
                config.raw ? key : encodeURIComponent(key),
                '=',
                config.raw ? value : encodeURIComponent(value),
                options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                options.path ? '; path=' + options.path : '',
                options.domain ? '; domain=' + options.domain : '',
                options.secure ? '; secure' : ''
            ].join(''));
        }

        // read
        var decode = config.raw ? raw : decoded;
        var cookies = document.cookie.split('; ');
        var result = key ? undefined : {};
        for (var i = 0, l = cookies.length; i < l; i++) {
            var parts = cookies[i].split('=');
            var name = decode(parts.shift());
            var cookie = decode(parts.join('='));
            if (key && key === name) {
                result = converted(cookie);
                break;
            }

            if (!key) {
                result[name] = converted(cookie);
            }
        }

        return result;
    };
    config.defaults = {};
    $.removeCookie = function(key, options) {
        if ($.cookie(key) !== undefined) {
            // Must not alter options, thus extending a fresh object...
            $.cookie(key, '', $.extend({}, options, {expires: -1}));
            return true;
        }
        return false;
    };
}));
