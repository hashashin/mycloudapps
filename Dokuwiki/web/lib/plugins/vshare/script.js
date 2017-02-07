
/**
 * Append a toolbar button
 */
if(window.toolbar != undefined){
    toolbar[toolbar.length] = {"type":  "pluginvshare",
                               "title": LANG['plugins']['vshare']['button'],
                               "icon":  "../../plugins/vshare/button.png",
                               "key":   ""};
}

/**
 * Try to determine the video service, extract the ID and insert
 * the correct syntax
 */
function tb_pluginvshare(btn, props, edid) {
    PluginVShare.edid = edid;

    PluginVShare.buildSyntax();
}

var PluginVShare = {
    edid: null,

    buildSyntax: function () {

        var text = prompt(LANG['plugins']['vshare']['prompt']);
        if (!text) return;

        // This includes the site patterns:
        /* DOKUWIKI:include sites.js */

        for (var key in sites) {

            if(sites.hasOwnProperty(key)) {
                var RE = new RegExp(sites[key], 'i');
                var match = text.match(RE);
                if (match) {
                    var urlparam = '';
                    var videoid = match[1];

                    switch (key) {
                        case 'slideshare':
                            //provided video url?
                            if(match[2]) {

                                jQuery.ajax({
                                    url: '//www.slideshare.net/api/oembed/2',
                                    dataType: 'jsonp',
                                    data: {
                                        url: match[2],
                                        format: 'jsonp'
                                    }
                                }).done(function (response, status, error) {
                                    var videoid = response.slideshow_id;
                                    PluginVShare.insert(key, videoid, urlparam);
                                }).fail(function (data, status, error) {
                                    /* http://www.slideshare.net/developers/oembed
                                     * If not found, an status 200 with response {error:true} is returned,
                                     * but "Content-Type:application/javascript; charset=utf-8" is then
                                     * wrongly changed to "Content-Type:application/json; charset=utf-8"
                                     * so it throws a parseerror
                                     */
                                    alert(LANG['plugins']['vshare']['notfound']);
                                });
                                return;
                            }
                            break;
                        case 'bliptv':
                            //provided video url?
                            if(match[2]) {

                                jQuery.ajax({
                                    url: '//blip.tv/oembed/',
                                    dataType: 'jsonp',
                                    data: {
                                        url: match[2],
                                        format: 'json'
                                    },
                                    timeout: 2000
                                }).done(function (response, status, error) {
                                    var videoidmatch = response.html.match(RE);
                                    PluginVShare.insert(key, videoidmatch[1], urlparam);
                                }).fail(function (data, status, error) {
                                    /*
                                     * If url is not found(=wrong numerical number on end), blip.tv returns a 404
                                     * because jsonp is not a xmlhttprequest, there is no 404 catched
                                     * errors are detected by waiting at the timeout
                                     */
                                    alert(LANG['plugins']['vshare']['notfound']);
                                });
                                return;
                            }
                            break;
                        case 'twitchtv':
                            if (match[2]) {
                                urlparam = '&chapter_id=' + match[2];
                            }
                            break;
                    }

                    PluginVShare.insert(key, videoid, urlparam);
                    return;
                }
            }
        }

        alert(LANG['plugins']['vshare']['notfound']);
    },

    insert: function(key, videoid, urlparam, edid) {
        var code = '{{' + key + '>' + videoid + '?medium' + urlparam + '}}';
        insertAtCarret(PluginVShare.edid, code);
    }
};

