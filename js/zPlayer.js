
// STANFORD: player controller module -- developed by Zhao
var zPlayer = (function () {
    var _video = document.getElementsByTagName('video')[0];
    var _videoContainer = document.getElementById('videoContainer');
    var _videoDuration = $('.video-duration');
    var _runTimer;
    var _fadeoutTimer = null;
    var _videoDurationT;
    var _videoPoster;
    var _subArray = new Array();

    var getVideoObject = function () {
        return _video;
    }

    var play = function () {
        _video.play();
        zSender.playAction(getCurrentTime());
    };

    var pause = function () {
        _video.pause();
        zSender.pauseAction(getCurrentTime());
    };

    var setVideoMuted = function (val) {
        _video.muted = val;
    };

    var setVideoVolume = function (val) {
        _video.volume = val;
    };

    var setCurrentTime = function (val) {
        _video.currentTime = val;
    };

    var getDurationTime = function () {
        // _video.addEventListener("canplay", function () {
        if (!isNaN(_video.duration)) {
            _videoDurationT = parseInt(_video.duration);
        }
        // },false);
        return _videoDurationT;
    };

    var setDurationTime = function () {
        return parseInt(_video.duration);
    };

    var getCurrentTime = function () {
        return parseInt(_video.currentTime);
    };

    var getSubArrayLength = function () {
        return _subArray.length;
    };

    var resetControlFadeOut = function () {
        $('.video_progress').removeClass('hide');
        $('.video_progress a').removeClass('hide');
        if (_fadeoutTimer != null) {
            clearTimeout(_fadeoutTimer);
        }
        _fadeoutTimer = setTimeout(function () {
            $('.video_progress').addClass('hide');
            $('.video_progress a').addClass('hide');
        }, 3000);
        return false;
    };

    var showDuration = function () {
        var dt = getDurationTime();
        
        _videoPoster = $('video').attr('poster');
        var displaySecond = dt % 60;
        if (displaySecond < 10) {
            displaySecond = '0' + displaySecond;
        }

        var displayHours = parseInt(dt / 3600);
        if (displayHours < 10) {
            displayHours = '0' + displayHours;
        }
        dt = dt - (displayHours * 3600);
        var displayNinuts = parseInt(dt / 60);
        
        if (displayNinuts < 10) {
            displayNinuts = '0' + displayNinuts;
        }

        _videoDuration.html(displayHours + ':' + displayNinuts + ':' + displaySecond + ' ');
    };

    var launchFullScreen = function () {
        if (_videoContainer.requestFullScreen) {
            _videoContainer.requestFullScreen();
        } else if (_videoContainer.mozRequestFullScreen) {
            _videoContainer.mozRequestFullScreen();
        } else if (_videoContainer.webkitRequestFullScreen) {
            _videoContainer.webkitRequestFullScreen();
        } else {
            _videoContainer.className = _videoContainer.className + " fullscreenIE";
        }
    };

    var exitFullScreen = function () {
        if (document.cancelFullScreen) {
            document.cancelFullScreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
        } else {
            _videoContainer.className = "mobile";
        }
    };

    var showCurrentTime = function () {
        var dt = getDurationTime();

        _video.addEventListener("timeupdate", function () {
            $('#loading_icon').css('display', 'none');
            var ct = getCurrentTime();

            var percentage = ct / dt;

            var displaySecond = ct % 60;
            if (displaySecond < 10) {
                displaySecond = '0' + displaySecond;
            }

            var displayHours = parseInt(ct / 3600);
            if (displayHours < 10) {
                displayHours = '0' + displayHours;
            }

            ct = ct - (displayHours * 3600);
            var displayMinuts = parseInt(ct / 60);
            if (displayMinuts < 10) {
                displayMinuts = '0' + displayMinuts;
            }


            var progressBarLength = $('.video_progress').css('width');
            progressBarLength = parseInt(progressBarLength);
            var barLength = progressBarLength * percentage;

            if (_subArray[ct] != undefined) {
                $('#caption_stage').html(_subArray[ct][2]);
            }

            if (ct == dt) {
                $('#caption_stage').hide();
            }
            var prograessBarHandlerWidth = parseInt($('#progressbar_handle').css('width'));
            prograessBarHandlerWidth = prograessBarHandlerWidth / 2;
            $('.video-timer').html(' ' + displayHours + ':' + displayMinuts + ':' + displaySecond);
            $('.video_progress_bar').css('width', barLength + 'px');
            $('.video_progress a').css('left', barLength - prograessBarHandlerWidth + 'px');

        }, false);

        _video.addEventListener("ended", function (e) {
            $('.video_progress_bar').css('width', '0');
            $('.video_progress a').css('left', '0');
            $('.arrow-right').removeClass('pause');
            $('.arrow-right').addClass('play');
            $('.video-timer').html('00:00:00');
            $('video').attr('poster', _videoPoster);
        }, false);


        _video.addEventListener("seeked", function () {

            zSender.jumpAction(getCurrentTime());
            return false;
        }, false);
    };

    var loadCaptionFile = function () {
        var j = 0;
        $.get('test.txt', function (data) {
            var n = data.split("<-#->");
            for (var i = 1; i <= n.length - 1; i++) {
                var cell = n[i].split("-->");
                var dataS = cell[0].split(":");
                var dataS1 = parseInt(dataS[0]) * 3600;
                var dataS2 = parseInt(dataS[1]) * 60;
                var dataS3 = parseInt(dataS[2]);
                var timeS = dataS1 + dataS2 + dataS3;

                var dataE = cell[1].split(":");
                var dataE1 = parseInt(dataE[0]) * 3600;
                var dataE2 = parseInt(dataE[1]) * 60;
                var dataE3 = parseInt(dataE[2]);
                var timeE = dataE1 + dataE2 + dataE3;
                _subArray[timeS] = [timeS, timeE, cell[2]];
            };
        });
    };

    var changeResolution = function (elem) {
        if (_video.networkState != _video.NETWORK_EMPTY) {
            if (_video.NETWORK_LOADING || _video.NETWORK_IDLE) {
                var tmpCurrentTime = getCurrentTime();
            }
        }
        var videoSource = _video.getElementsByTagName('source');
        videoSource[0].src = elem.children('a').attr('data-resource');
        _video.load();

        _video.addEventListener("loadedmetadata", function () {
            _video.currentTime = parseInt(tmpCurrentTime);
            $('.arrow-right').removeClass('play');
            $('.arrow-right').addClass('pause');
            _video.play();
        }, false);

        elem.siblings().removeClass('selected');
        elem.addClass('selected');
        $('.video-speed ul li', '#user-prefs').removeClass('selected');
        $('.video-speed ul li:last-child', '#user-prefs').addClass('selected');
    };
    var switchVideos = function (elem) {
        fnFloatFooter("add");
        $('.course-sessions').addClass('horizon_line');

        $('#popUpVideo').slideDown('slow');
        var browserType = $('#hfBrowserType').val();
        $('#loading_icon').css('display', 'block');
        if (browserType == "FF") {
            console.log("FF");
            var sJsonString = elem.attr('data-resolution');

            var aResoultion = $.parseJSON(sJsonString);

            var getCCOrNot = aResoultion['cc'];
            console.log(getCCOrNot);
            if (getCCOrNot == "N") {
                $('.video-cc', '#user-prefs').css('display', 'none');
            }
            _video.addEventListener("loadedmetadata", function () {
                $('.arrow-right', '#video-controls').removeClass('play').addClass('pause');
                _video.play();
            }, false);
            var haswebmresource = false;
            var hasogvresource = false;
            for (var verifywebm = 0; verifywebm < aResoultion['url'].length; verifywebm++) {
                if (aResoultion['url'][verifywebm]['webm-url'] != "") {
                    haswebmresource = true;
                    break;
                }
            }

            for (var verifyogv = 0; verifyogv < aResoultion['url'].length; verifyogv++) {
                if (aResoultion['url'][verifyogv]['ogv-url'] != "") {
                    hasogvresource = true;
                    break;
                }
            }

            if (detectMediaSupport2("webem") && haswebmresource) {
                
                $('.video-resolution>ul').html('');
                var sAppendHtml = '';
                var keyLock = true;
                for (var i = 0; i < aResoultion['url'].length; i++) {
                    
                    if (aResoultion['url'][i]['webm-url'] != "" && keyLock == true) {
                        var videoSource = _video.getElementsByTagName('source');
                        videoSource[0].src = aResoultion['url'][i]['webm-url'];
                        _video.load();
                        zSender.getIDAction(aResoultion['assetid'], videoSource[0].src);
                        keyLock = false;
                        sAppendHtml += '<li class="selected"><a data-resource="' + aResoultion['url'][i]['webm-url'] + '" href="javascript:void(0);">' + aResoultion['url'][i]['title'] + '</a></li>';
                    } else if (aResoultion['url'][i]['webm-url'] != "") {
                        if (aResoultion['url'][i]['webm-url'] != "") {
                            sAppendHtml += '<li><a data-resource="' + aResoultion['url'][i]['webm-url'] + '" href="javascript:void(0);">' + aResoultion['url'][i]['title'] + '</a></li>';
                        }
                    }

                }
                $('.video-resolution>ul').html(sAppendHtml);
            } else if (detectMediaSupport2("ogg") && hasogvresource) {
                
                $('.video-resolution>ul').html('');
                var sAppendHtml = '';
                var keyLock = true;
                for (var i = 0; i < aResoultion['url'].length; i++) {
                    if (aResoultion['url'][i]['ogv-url'] != "" && keyLock == true) {
                        var videoSource = _video.getElementsByTagName('source');
                        videoSource[0].src = aResoultion['url'][i]['ogv-url'];
                        _video.load();
                        zSender.getIDAction(aResoultion['assetid'], videoSource[0].src);
                        keyLock = false;
                        sAppendHtml += '<li class="selected"><a data-resource="' + aResoultion['url'][i]['ogv-url'] + '" href="javascript:void(0);">' + aResoultion['url'][i]['title'] + '</a></li>';
                    } else if (aResoultion['url'][i]['ogv-url'] != "") {
                        if (aResoultion['url'][i]['ogv-url'] != "") {
                            sAppendHtml += '<li><a data-resource="' + aResoultion['url'][i]['ogv-url'] + '" href="javascript:void(0);">' + aResoultion['url'][i]['title'] + '</a></li>';
                        }

                    }

                }
                $('.video-resolution>ul').html(sAppendHtml);
            } else {
                var sJsonString = elem.attr('data-resolution');

                var aResoultion = $.parseJSON(sJsonString);
                
                var sUrl = elem.attr('data-bitrate');
                var appendURL = '';
                for (var i = 0; i < aResoultion['url'].length; i++) {
                    if (aResoultion['url'][i]['title'] == sUrl) {
                        appendURL = aResoultion['url'][i]['mp4-url'];
                        break;
                    }
                }
                var appendHtml = '<object height="489" width="810" type="application/x-shockwave-flash" data="../../Scripts/StrobeMediaPlayback.swf">\
                <param name="movie" value="../../Scripts/StrobeMediaPlayback.swf"></param>\
                <param name="flashvars" value="src=' + appendURL + '"></param>\
                <param name="allowFullScreen" value="true"></param>\
                <param name="allowscriptaccess" value="always"></param>\
                <param name="wmode" value="direct"></param>\
            </object>';
                $('#videoContainer').html(appendHtml);
            }
        } else {
            console.log("other browser");
            if (detectMediaSupport()) {

                var sJsonString = elem.attr('data-resolution');
                
                var aResoultion = $.parseJSON(sJsonString);

                var getCCOrNot = aResoultion['cc'];
                console.log(getCCOrNot);
                if (getCCOrNot == "N") {
                    $('.video-cc', '#user-prefs').css('display', 'none');
                }

                var sUrl = elem.attr('data-bitrate');

                _video.addEventListener("loadedmetadata", function () {
                    $('.arrow-right', '#video-controls').removeClass('play').addClass('pause');
                    _video.play();
                }, false);

                $('.video-resolution>ul').html('');
                var sAppendHtml = '';
                for (var i = 0; i < aResoultion['url'].length; i++) {
                    if (aResoultion['url'][i]['title'] == sUrl) {
                        var videoSource = _video.getElementsByTagName('source');
                        videoSource[0].src = aResoultion['url'][i]['mp4-url'];
                        _video.load();
                        zSender.getIDAction(aResoultion['assetid'], videoSource[0].src);
                        sAppendHtml += '<li class="selected"><a data-resource="' + aResoultion['url'][i]['mp4-url'] + '" href="javascript:void(0);">' + aResoultion['url'][i]['title'] + '</a></li>';
                    } else {
                        sAppendHtml += '<li><a data-resource="' + aResoultion['url'][i]['mp4-url'] + '" href="javascript:void(0);">' + aResoultion['url'][i]['title'] + '</a></li>';
                    }

                }
                $('.video-resolution>ul').html(sAppendHtml);
            } else {
                var sJsonString = elem.attr('data-resolution');

                var aResoultion = $.parseJSON(sJsonString);
                var sUrl = elem.attr('data-bitrate');
                var appendURL = '';
                for (var i = 0; i < aResoultion['url'].length; i++) {
                    if (aResoultion['url'][i]['title'] == sUrl) {
                        appendURL = aResoultion['url'][i]['mp4-url'];
                        break;
                    }
                }
                var appendHtml = '<object height="489" width="810" type="application/x-shockwave-flash" data="../../Scripts/StrobeMediaPlayback.swf">\
                <param name="movie" value="../../Scripts/StrobeMediaPlayback.swf"></param>\
                <param name="flashvars" value="src=' + appendURL + '"></param>\
                <param name="allowFullScreen" value="true"></param>\
                <param name="allowscriptaccess" value="always"></param>\
                <param name="wmode" value="direct"></param>\
            </object>';
                $('#videoContainer').html(appendHtml);
            }
        }
    };
    var removeVideo = function () {
        $('#popUpVideo').slideUp('slow');
        fnFloatFooter("remove");
        //var sShowStandingLine = $('.course-overview').css('display');
        //if (sShowStandingLine == 'none') {
            $('.course-sessions').removeClass('horizon_line');
        //}
        var browserType = $('#hfBrowserType').val();
        if (browserType == "FF") {
            if (detectMediaSupport2("webem") || detectMediaSupport2("ogg")) {
                var videoSource = _video.getElementsByTagName('source');
                videoSource[0].src = '';
                _video.load();
            }else {
                $('#videoContainer').html('');  
            }
        } else {
            if (detectMediaSupport()) {
                var videoSource = _video.getElementsByTagName('source');
                videoSource[0].src = '';
                _video.load();
            } else {
                $('#videoContainer').html('');
            }
        }
          
    };

    var detectMediaSupport = function () {
        var container = 'video';
        var elem = document.createElement(container);
        var mimetype = 'video/mp4; codecs="avc1.42E01E, mp4a.40.2"';
        var mimetypeOgv = 'video/ogg; codecs="theora, vorbis"';
        var mimetypeWebm = 'video/webm; codecs="vp8, vorbis"';
        if (typeof elem.canPlayType == 'function') {
            var playAble = elem.canPlayType(mimetype);
            if ((playAble.toLowerCase() == 'maybe') || (playAble.toLowerCase() == 'probably')) {
                return true;
            }
            if ($('video source').attr('src') == "") {
                return false;
            } else {
                var playAbleOgv = elem.canPlayType(mimetypeOgv);
                if ((playAbleOgv.toLowerCase() == 'maybe') || (playAbleOgv.toLowerCase() == 'probably')) {
                    return true;
                }
            }
        }
        return false;
    };

    var detectMediaSupport2 = function (datatype) {
        var container = 'video';
        var elem = document.createElement(container);
        var mimetype = 'video/mp4; codecs="avc1.42E01E, mp4a.40.2"';
        var mimetypeOgv = 'video/ogg; codecs="theora, vorbis"';
        var mimetypeWebm = 'video/webm; codecs="vp8, vorbis"';
        if (typeof elem.canPlayType == 'function') {
            if (datatype == "mp4") {
                var playAble = elem.canPlayType(mimetype);
                if ((playAble.toLowerCase() == 'maybe') || (playAble.toLowerCase() == 'probably')) {
                    return true;
                } else {
                    return false;
                }
            }

            if (datatype == "webem") {
                var playAbleWebem = elem.canPlayType(mimetypeWebm);
                if ((playAbleWebem.toLowerCase() == 'maybe') || (playAbleWebem.toLowerCase() == 'probably')) {
                    return true;
                } else {
                    return false;
                }
            }

            if (datatype == "ogg"){
                var playAbleOgv = elem.canPlayType(mimetypeOgv);
                if ((playAbleOgv.toLowerCase() == 'maybe') || (playAbleOgv.toLowerCase() == 'probably')) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    };

    return {
        player: getVideoObject,
        setVideoMuted: setVideoMuted,
        setVideoVolume: setVideoVolume,
        play: play,
        pause: pause,
        showDuration: showDuration,
        launchFullScreen: launchFullScreen,
        showCurrentTime: showCurrentTime,
        loadCaptionFile: loadCaptionFile,
        getSubArrayLength: getSubArrayLength,
        setCurrentTime: setCurrentTime,
        getDurationTime: getDurationTime,
        changeResolution: changeResolution,
        resetControlFadeOut: resetControlFadeOut,
        exitFullScreen: exitFullScreen,
        switchVideos: switchVideos,
        removeVideo: removeVideo,
        getCurrentTime: getCurrentTime
    };
})();


$(function () {
    $("#volume-slider").slider({
        orientation: "vertical",
        range: "min",
        max: 1,
        value: 0.6,
        step: 0.05,
        animate: true,
        slide: function (event, ui) {
            zPlayer.setVideoMuted(false);
            if (ui.value <= 0.05) {
                zPlayer.setVideoMuted(true);
            } else {
                zPlayer.setVideoVolume(ui.value);
            }
        },
        start: function (event, ui) {
            $('#volume-slider .ui-slider-handle').addClass('mousedown');
        },
        stop: function (event, ui) {
            $('#volume-slider').addClass('hidebar');
            $('#volume-slider .ui-slider-handle').removeClass('mousedown');
        }
    });

});

// jQuery

$('#slideUpButton').click(function () {
    zPlayer.removeVideo();
    $('.course-overview').removeClass('horizon_line');
});

$('#fullscreen-btn').click(function () {
    if ($(this).hasClass('esc')) {
        $(this).removeClass('esc');
        zPlayer.exitFullScreen();
    } else {
        $(this).addClass('esc');
        zPlayer.launchFullScreen();
    }
});

$('.arrow-right').click(function () {
    if ($(this).hasClass('play')) {
        $(this).removeClass('play');
        $(this).addClass('pause');
        zPlayer.play();
    } else {
        $(this).removeClass('pause');
        $(this).addClass('play');
        zPlayer.pause();
    }
});

$('#volume-btn').click(function () {
    $('#volume-slider').toggleClass('hidebar');
    $('.video-speed').children('ul').css('display', 'none');
    $('.video-resolution').children('ul').css('display', 'none');
});

$('.video_progress').click(function (e) {
    zSender.setBeginTime(zPlayer.getCurrentTime());
    var ps = $(this).offset();
    var newPosition = e.pageX - ps.left;
    var progressBarLength = $('.video_progress').css('width');
    progressBarLength = parseInt(progressBarLength);
    var getDuration = zPlayer.getDurationTime();
    var getTargetTime = newPosition / progressBarLength * getDuration;

    var handlerWidth = parseInt($('#progressbar_handle').css('width')) / 2;

    $('.video_progress_bar').css('width', newPosition + 'px');
    newPosition = newPosition - handlerWidth;
    $('#progressbar_handle').css('left', newPosition + 'px');
    zPlayer.setCurrentTime(getTargetTime);
});

$('.video-resolution>ul li').live("click", function () {
    zPlayer.changeResolution($(this));

});

$('.video-speed').click(function () {
    $(this).children('ul').slideToggle('normal');
    $('.video-resolution').children('ul').css('display', 'none');
});

$('.video-resolution').click(function () {
    $(this).children('ul').slideToggle('normal');
    $('.video-speed').children('ul').css('display', 'none');
});

$('.video-cc').click(function () {
    $(this).children('a').toggleClass('selected');
    var subArrayLength = zPlayer.getSubArrayLength();
    if ($(this).children('a').hasClass('selected')) {
        if (subArrayLength == 0) {
            zPlayer.loadCaptionFile();
            $('#caption_stage').show();
        } else {
            $('#caption_stage').show();
        }
        return false;
    } else {
        $('#caption_stage').hide();
        return false;
    }
});

// press esc for escape full screen on IE
$(document).keyup(function (e) {
    if (e.keyCode == 27) {
        $('#fullscreen-btn').removeClass('esc');
        zPlayer.exitFullScreen();
    }
});

$('.video-speed li').live('click', function () {
    $(this).siblings().removeClass('selected');
    $(this).addClass('selected');

    var myVideo = zPlayer.player();
    var rate = $(this).attr('data-value');

    if (typeof myVideo != 'undefined') {
        if (rate == 1) {
            myVideo.playbackRate = 1.0000001;
        } else {
            myVideo.playbackRate = rate;
        }
        return;
    }
});

// progress handler drag and drop
var handle = (function () {
    var _mouseUporDown = false;
    var _uiSliderHandle = false;

    var getMouseStatus = function () {
        return _mouseUporDown;
    };

    var setMouseStatus = function (val) {
        _mouseUporDown = val;
    };

    var setUiSliderHandle = function (val) {
        _uiSliderHandle = val;
    };

    var getUiSliderHandle = function () {
        return _uiSliderHandle;
    };
    return {
        getMouseStatus: getMouseStatus,
        setMouseStatus: setMouseStatus,
        setUiSliderHandle: setUiSliderHandle,
        getUiSliderHandle: getUiSliderHandle
    };
})();

$(function () {
    $('#videoContainer').on('mousemove', function (e) {
        zPlayer.resetControlFadeOut();
        var canmoveornot = handle.getMouseStatus();
        var volumehandle = handle.getUiSliderHandle();
        if (canmoveornot == true) {
            var ps = $('.video_progress').offset();
            mousecurrentPosition = e.clientX - ps.left;
            var barWidth = $('.video_progress').css('width');
            barWidth = parseInt(barWidth);
            if (mousecurrentPosition >= 0 && mousecurrentPosition <= barWidth) {
                var nobWidth = parseInt($('.video_progress a').css('width'));
                nobWidth = nobWidth / 2;
                var knobLeft = mousecurrentPosition - nobWidth;
                $('.video_progress a').css('left', knobLeft + 'px');
                $('.video_progress_bar').css('width', mousecurrentPosition + 'px');

                var getDuration = zPlayer.getDurationTime();
                var getTargetTime = mousecurrentPosition / barWidth * getDuration;
                zPlayer.setCurrentTime(getTargetTime);

            }
            return false;
        } else if (volumehandle == true) {
            return true;
        }
    });
});

$('video').live('mouseup', function () {
    handle.setMouseStatus(false);
    handle.setUiSliderHandle(false);
    $('#volume-slider').addClass('hidebar');
    $('.video-speed').children('ul').css('display', 'none');
    $('.video-resolution').children('ul').css('display', 'none');
});

$('#videoContainer').on('mouseleave', function () {
    handle.setMouseStatus(false);
    handle.setUiSliderHandle(false);
    $('#volume-slider').addClass('hidebar');
});

$('#volume-slider .ui-slider-handle').live('mousedown', function () {
    handle.setUiSliderHandle(true);
    $(this).addClass('mousedown');
    return false;
}).live('mouseup', function () {
    handle.setUiSliderHandle(false);
    $(this).parent().addClass('hidebar');
});


$('#video-controls').live('mouseup', function () {
    handle.setMouseStatus(false);
    return true;
});

$('#progressbar_handle').live('mousedown', function (e) {
    handle.setMouseStatus(true);
    zSender.setBeginTime(zPlayer.getCurrentTime());
    return false;

}).live('mouseup', function (e) {
    handle.setMouseStatus(false);
    return false;
});


var zSender = (function () {
    _userName = $('#spanLoggedUserName').text();
    _courseID = '';
    _courseName = '';
    _videoTitle = '';
    _videoURL = '';
    _assetID = '';
    _beginTime = 0;
    _logID = '';
    _url = '../../UserPlaybackActions/';

    var setAssetID = function (val) {
        _assetID = val;
    };
    var setUserName = function (val) {
        _userName = val;
    };
    var setVideoURL = function (val) {
        _videoURL = val;
    };
    var setBeginTime = function (val) {
        _beginTime = val;
    }

    var getBeginTime = function () {
        return _beginTime;
    }

    var getIDAction = function (id,u) {
        _assetID = id;
        _videoURL = u;
        zData = {
            userName: _userName,
            videoUrl: _videoURL,
            assetId: _assetID,
            sourceText: "VideoViewer"
        }
        console.log(zData);
        $.ajax({
            url: _url + "UserVideoStarted",
            type: 'POST',
            dataType: 'json',
            data: zData,
            success: function (d) {
                console.log(d.ok);

                if (d.ok) {
                    _logID = d.Id;
                }
            }
        });
    }

    var playAction = function (val) {
        console.log('Play' + _userName);
        if (_logID == "") {
            zData = {
                userName: "NOUSERNAME",
                videoUrl: _videoURL,
                assetId: _assetID
            }
            console.log(zData);
            $.ajax({
                url: _url + "UserVideoStarted",
                type: 'POST',
                dataType: 'json',
                data: zData,
                success: function (d) {
                    console.log(d);

                    if (d.ok) {
                        _logID = d.Id;
                        playAction(zPlayer.getCurrentTime());
                    }
                },
                error: function (a) {
                    console.log('error' + a);
                }

            });
        } else {
            zData = {
                logId: _logID,
                action: 'play',
                time: val,

            }
            console.log(zData);
            $.ajax({
                url: _url + "SetUserPlay",
                type: 'POST',
                dataType: 'json',
                data: zData,
                success: function (d) {
                    console.log(d);
                }
            });
        }

        return false;
    };

    var pauseAction = function (val) {
        console.log('Pause' + _userName);
        if (_logID == "") {
            zData = {
                userName: "NONE-USERNAME",
                videoUrl: _videoURL,
                assetId: _assetID
            }
            console.log(zData);
            $.ajax({
                url: _url + "UserVideoStarted",
                type: 'POST',
                dataType: 'json',
                data: zData,
                success: function (d) {
                    console.log(d.ok);

                    if (d.ok) {
                        _logID = d.Id;
                        pauseAction(zPlayer.getCurrentTime());
                    }
                }
            });
        } else {
            zData = {
                logId: _logID,
                action: 'pause',
                time: val
            }
            console.log(zData);
            $.ajax({
                url: _url + "SetUserPause",
                type: 'POST',
                dataType: 'json',
                data: zData,
                success: function (d) {
                    console.log(d);
                },
                error: function (a) {
                    console.log(a);
                }
            });
        }


        return false;
    };
    var _endTime = 0;
    var jumpAction = function (endT) {
        console.log('jump' + _userName);
        if (_logID == "") {
            zData = {
                userName: "NONE-USERNAME",
                videoUrl: _videoURL,
                assetId: _assetID
            }
            console.log(zData);
            $.ajax({
                url: _url + "UserVideoStarted",
                type: 'POST',
                dataType: 'json',
                data: zData,
                success: function (d) {
                    console.log(d.ok);

                    if (d.ok) {
                        _logID = d.Id;
                        jumpAction(zPlayer.getCurrentTime());
                    }
                }
            });
        } else {
            if (_endTime != endT) {
                _endTime = endT;

                zData = {
                    logId: _logID,
                    action: 'jump',
                    begin: getBeginTime(),
                    end: endT
                }
                console.log(zData);
                $.ajax({
                    url: _url + "SetUserJump",
                    type: 'POST',
                    dataType: 'json',
                    data: zData,
                    success: function (d) {
                        console.log(d);
                    }
                });
                return false;
            }
        }



    };

    var changResolutionAction = function (val) {
        console.log('Resolution' + _userName);
        zData = {
            username: _userName,
            action: 'changeResolution',
            resolution: val,
            assetid: _assetID,
            videourl: _videoURL
        }
        console.log(zData);
        // $.ajax({
        //   url:url,
        //   type:'POST',
        //   dataType: 'json',
        //   data: zData,
        //   success: function(d) {

        //   }
        // });
    };

    var changeSpeedAction = function (val) {
        console.log('Speed' + _userName);
        zData = {
            username: _userName,
            action: 'changeSpeedAction',
            rate: val,
            assetid: _assetID,
            videourl: _videoURL
        }
        console.log(zData);
        // $.ajax({
        //   url:url,
        //   type:'POST',
        //   dataType: 'json',
        //   data: zData,
        //   success: function(d) {

        //   }
        // });
    };

    var videoEndedAction = function () {
        console.log('ended' + _userName);
        zData = {
            username: _userName,
            action: 'endedAction',
            videoName: '',
            assetid: _assetID,
            videourl: _videoURL
        }
        console.log(zData);
        // $.ajax({
        //   url:url,
        //   type:'POST',
        //   dataType: 'json',
        //   data: zData,
        //   success: function(d) {

        //   }
        // });
    }


    return {
        setUserName: setUserName,
        playAction: playAction,
        pauseAction: pauseAction,
        jumpAction: jumpAction,
        changResolutionAction: changResolutionAction,
        changeSpeedAction: changeSpeedAction,
        setBeginTime: setBeginTime,
        videoEndedAction: videoEndedAction,
        setAssetID: setAssetID,
        getIDAction: getIDAction

    };
})();

