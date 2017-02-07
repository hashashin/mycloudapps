var DROPBOX_XML_LANGUAGE_EN;
var DROPBOX_XML_LANGUAGE;


function dropbox_load_language() {
var lang_array = [
						"en-US",
						"fr-FR",
						"it-IT",
						"de-DE",
						"es-ES",
						"zh-CN",
						"zh-TW",
						"ko-KR",
						"ja-JP",
						"ru-RU",
						"pt-BR",
						"cs-CZ",
						"nl-NL",
						"hu-HU",
						"nb-NO",
						"pl-PL",
						"sv-SE",
						"tr-TR"
					];
	
	var filename = "/Dropbox/lang/" + lang_array[parseInt(MULTI_LANGUAGE,10)]+".xml";
					
	wd_ajax({
		type: "GET",
		url: filename,
		dataType: "xml",
		async: false,
		cache: false,
		error: function () {},
		success: function (xml) {
			DROPBOX_XML_LANGUAGE = xml;
		}
	});
}

function dropbox_load_en_language() {
	wd_ajax({
		type: "GET",
		url: "/Dropbox/lang/en-US.xml",
		dataType: "xml",
		async: false,
		cache: false,
		error: function () {},
		success: function (xml) {
			DROPBOX_XML_LANGUAGE_EN = xml;
		}
	});
}

function _T_APP(c, id) {
	var str = "";
	var find = false;

	if (typeof DROPBOX_XML_LANGUAGE == 'undefined') dropbox_load_language();
	if (typeof DROPBOX_XML_LANGUAGE_EN == 'undefined') dropbox_load_en_language();

	$(DROPBOX_XML_LANGUAGE).find(c).each(function () {
		str = $(this).find(id).text()
		if (str != "")
			find = true;
		return false;
	});

	if (find == false) {
		$(DROPBOX_XML_LANGUAGE_EN).find(c).each(function () {
			str = $(this).find(id).text()
			return false;
		});
	}
	return str;
}

function dropbox_language() {
	$('._text_app').each(function () {
		var str = _T_APP($(this).attr('lang'), $(this).attr('datafld'));
		if (str != "") {
			$(this).empty();
			$(this).html(str);
		}
	});
}

function dropbox_ready_language() {
	dropbox_load_en_language();
	dropbox_load_language();
	dropbox_language();
}
