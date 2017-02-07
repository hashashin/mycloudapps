/**
 * video URL recognition patterns
 *
 * The first match group is used as video ID
 *
 * You need to touch conf/local.php to refresh the cache after changing
 * this file
 */

var sites = {
    'youtube':     'youtube\\.com/.*[&?]v=([a-z0-9_\\-]+)',
    'vimeo':       'vimeo\\.com\\/(\\d+)',
    'ustream':     'ustream\\.tv\\/recorded\\/(\\d+)\\/',
    '5min':        '5min\\.com\\/Video/.*-([0-9]+)([&?]|$)',
    'clipfish':    'clipfishi\\.de\\/.*\\/video\\/([0-9])+\\/',
    'dailymotion': 'dailymotion\\.com\\/video\\/([a-z0-9]+)_',
    'gtrailers':   'gametrailers\\.com\\/.*\\/(\\d+)',
    'metacafe':    'metacafe\\.com\\/watch\\/(\\d+)\\/',
    'myspacetv':   'vids\\.myspace\\.com\\/.*videoid=(\\d+)',
    'rcmovie':     'rcmovie\\.de\\/video\\/([a-f0-9]+)\\/',
    'scivee':      'scivee\\.tv\\/node\\/(\\d+)',
    'twitchtv':    'twitch\\.tv\\/([a-z0-9_\\-]+)(?:\\/c\\/(\\d+))?',
    'veoh':        'veoh\\.com\\/.*watch[^v]*(v[a-z0-9]+)',
    'bambuser':    'bambuser\\.com\\/v\\/(\\d+)',
    'bliptv':      '(?:blip\\.tv\\/play\\/([a-zA-Z0-9]+\\.(?:html|x))\\?p=1|(http?\\:\\/\\/blip\\.tv\\/(?!play)(?:[a-zA-Z0-9_\\-]+)\\/(?:[a-zA-Z0-9_\\-]+)))',
    'break':       'break\\.com\\/video\\/(?:(?:[a-z]+)\\/)?(?:[a-z\\-]+)-([0-9]+)',
    'viddler':     'viddler\\.com\\/(?:embed|v)\\/([a-z0-9]{8})',
    'msoffice':    '(?:office\\.com.*[&?]videoid=([a-z0-9\\-]+))',
    'slideshare':  '(?:(?:slideshare\\.net\\/slideshow\\/embed_code\\/|id=)([0-9]+)|(https?\\:\\/\\/www\\.slideshare\\.net\\/(?:[a-zA-Z0-9_\\-]+)\\/(?:[a-zA-Z0-9_\\-]+)))',
    'archiveorg':  'archive\\.org\\/embed\\/([a-zA-Z0-9_\\-]+)',
    'niconico':    'nicovideo\\.jp/watch/(sm[0-9]+)',
    'youku':       'v\\.youku\\.com/v_show/id_([[0-9A-Za-z]]+)\\.html',
    'tudou':       'tudou\\.com/programs/view/([0-9A-Za-z]+)',
    'bilibili':    'bilibili\\.com/video/av([0-9])+/'
};

