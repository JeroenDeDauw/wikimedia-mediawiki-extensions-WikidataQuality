var autoCollapse=2;var collapseCaption=mw.message('hidetoc').text();var expandCaption=mw.message('showtoc').text();window.collapseTable=function(tableIndex){var Button=document.getElementById('collapseButton'+tableIndex);var Table=document.getElementById('collapsibleTable'+tableIndex);if(!Table||!Button){return false;}var Rows=Table.rows;if(Button.firstChild.data==collapseCaption){for(var i=1;i<Rows.length;i++){Rows[i].style.display='none';}Button.firstChild.data=expandCaption;}else{for(var i=1;i<Rows.length;i++){Rows[i].style.display=Rows[0].style.display;}Button.firstChild.data=collapseCaption;}}
function createCollapseButtons(){var tableIndex=0;var NavigationBoxes={};var Tables=document.getElementsByTagName('table');var i;function handleButtonLink(index,e){window.collapseTable(index);e.preventDefault();}for(i=0;i<Tables.length;i++){if($(Tables[i]).hasClass('collapsible')){var HeaderRow=Tables[i].getElementsByTagName('tr')[0];if(!HeaderRow)continue;var Header=HeaderRow.getElementsByTagName('th')[0];if(!Header)continue;NavigationBoxes[tableIndex]=Tables[i];Tables[i].setAttribute('id','collapsibleTable'+tableIndex);var Button=document.createElement('span');var ButtonLink=document.createElement('a');var ButtonText=document.createTextNode(collapseCaption);Button.className='collapseButton';ButtonLink.style.color=Header.style.color;ButtonLink.setAttribute('id','collapseButton'+tableIndex);ButtonLink.setAttribute('href','#');$(ButtonLink).on('click',$.proxy(handleButtonLink,ButtonLink,tableIndex));ButtonLink.appendChild(ButtonText);Button.appendChild(document.createTextNode('['));
Button.appendChild(ButtonLink);Button.appendChild(document.createTextNode(']'));Header.insertBefore(Button,Header.firstChild);tableIndex++;}}for(i=0;i<tableIndex;i++){if($(NavigationBoxes[i]).hasClass('collapsed')||(tableIndex>=autoCollapse&&$(NavigationBoxes[i]).hasClass('autocollapse'))){window.collapseTable(i);}else if($(NavigationBoxes[i]).hasClass('innercollapse')){var element=NavigationBoxes[i];while((element=element.parentNode)){if($(element).hasClass('outercollapse')){window.collapseTable(i);break;}}}}}mw.hook('wikipage.content').add(createCollapseButtons);var NavigationBarHide='['+collapseCaption+']';var NavigationBarShow='['+expandCaption+']';window.toggleNavigationBar=function(indexNavigationBar){var NavToggle=document.getElementById('NavToggle'+indexNavigationBar);var NavFrame=document.getElementById('NavFrame'+indexNavigationBar);if(!NavFrame||!NavToggle){return false;}if(NavToggle.firstChild.data==NavigationBarHide){for(var NavChild=NavFrame.firstChild;NavChild!=null;
NavChild=NavChild.nextSibling){if($(NavChild).hasClass('NavContent')||$(NavChild).hasClass('NavPic')){NavChild.style.display='none';}}NavToggle.firstChild.data=NavigationBarShow;}else if(NavToggle.firstChild.data==NavigationBarShow){for(var NavChild=NavFrame.firstChild;NavChild!=null;NavChild=NavChild.nextSibling){if($(NavChild).hasClass('NavContent')||$(NavChild).hasClass('NavPic')){NavChild.style.display='block';}}NavToggle.firstChild.data=NavigationBarHide;}}
function createNavigationBarToggleButton(){var indexNavigationBar=0;var divs=document.getElementsByTagName('div');for(var i=0;NavFrame=divs[i];i++){if($(NavFrame).hasClass('NavFrame')){indexNavigationBar++;var NavToggle=document.createElement('a');NavToggle.className='NavToggle';NavToggle.setAttribute('id','NavToggle'+indexNavigationBar);NavToggle.setAttribute('href','javascript:toggleNavigationBar('+indexNavigationBar+');');var isCollapsed=$(NavFrame).hasClass('collapsed');for(var NavChild=NavFrame.firstChild;NavChild!=null&&!isCollapsed;NavChild=NavChild.nextSibling){if($(NavChild).hasClass('NavPic')||$(NavChild).hasClass('NavContent')){if(NavChild.style.display=='none'){isCollapsed=true;}}}if(isCollapsed){for(var NavChild=NavFrame.firstChild;NavChild!=null;NavChild=NavChild.nextSibling){if($(NavChild).hasClass('NavPic')||$(NavChild).hasClass('NavContent')){NavChild.style.display='none';}}}var NavToggleText=document.createTextNode(isCollapsed?NavigationBarShow:NavigationBarHide);
NavToggle.appendChild(NavToggleText);for(var j=0;j<NavFrame.childNodes.length;j++){if($(NavFrame.childNodes[j]).hasClass('NavHead')){NavToggle.style.color=NavFrame.childNodes[j].style.color;NavFrame.childNodes[j].appendChild(NavToggle);}}NavFrame.setAttribute('id','NavFrame'+indexNavigationBar);}}}$(createNavigationBarToggleButton);mw.loader.using('mediawiki.user',function(){if(mw.user.isAnon()){window.wpAvailableLanguages={"aa":"Qafár af","ab":"Аҧсшәа","ace":"Acèh","af":"Afrikaans","ak":"Akan","aln":"Gegë","als":"Alemannisch","am":"አማርኛ","an":"aragonés","ang":"Ænglisc","anp":"अङ्गिका","ar":"العربية","arc":"ܐܪܡܝܐ","arn":"mapudungun","ary":"Maġribi","arz":"مصرى","as":"অসমীয়া","ast":"asturianu","av":"авар","avk":"Kotava","ay":"Aymar aru","az":"azərbaycanca","azb":"تورکجه","ba":"башҡортса","bar":"Boarisch","bat-smg":"žemaitėška","bbc":"Batak Toba","bbc-latn":"Batak Toba","bcc":"بلوچی مکرانی"
,"bcl":"Bikol Central","be":"беларуская","be-tarask":"беларуская (тарашкевіца)‎","be-x-old":"беларуская (тарашкевіца)‎","bg":"български","bh":"भोजपुरी","bho":"भोजपुरी","bi":"Bislama","bjn":"Bahasa Banjar","bm":"bamanankan","bn":"বাংলা","bo":"བོད་ཡིག","bpy":"বিষ্ণুপ্রিয়া মণিপুরী","bqi":"بختياري","br":"brezhoneg","brh":"Bráhuí","bs":"bosanski","bug":"ᨅᨔ ᨕᨘᨁᨗ","bxr":"буряад","ca":"català","cbk-zam":"Chavacano de Zamboanga","cdo":"Mìng-dĕ̤ng-ngṳ̄","ce":"нохчийн","ceb":"Cebuano","ch":"Chamoru","cho":"Choctaw","chr":"ᏣᎳᎩ","chy":"Tsetsêhestâhese","ckb":"کوردی","co":"corsu","cps":"Capiceño","cr":"Nēhiyawēwin \/ ᓀᐦᐃᔭᐍᐏᐣ","crh":"qırımtatarca","crh-latn":"qırımtatarca (Latin)‎","crh-cyrl":"къырымтатарджа (Кирилл)‎","cs":"čeština","csb":
"kaszëbsczi","cu":"словѣньскъ \/ ⰔⰎⰑⰂⰡⰐⰠⰔⰍⰟ","cv":"Чӑвашла","cy":"Cymraeg","da":"dansk","de":"Deutsch","de-at":"Österreichisches Deutsch","de-ch":"Schweizer Hochdeutsch","de-formal":"Deutsch (Sie-Form)‎","diq":"Zazaki","dsb":"dolnoserbski","dtp":"Dusun Bundu-liwan","dv":"ދިވެހިބަސް","dz":"ཇོང་ཁ","ee":"eʋegbe","egl":"Emiliàn","el":"Ελληνικά","eml":"emiliàn e rumagnòl","en":"English","eo":"Esperanto","es":"español","et":"eesti","eu":"euskara","ext":"estremeñu","fa":"فارسی","ff":"Fulfulde","fi":"suomi","fit":"meänkieli","fiu-vro":"Võro","fj":"Na Vosa Vakaviti","fo":"føroyskt","fr":"français","frc":"français cadien","frp":"arpetan","frr":"Nordfriisk","fur":"furlan","fy":"Frysk","ga":"Gaeilge","gag":"Gagauz","gan":"贛語","gan-hans":"赣语（简体）‎","gan-hant":"贛語（繁體）‎","gd":"Gàidhlig","gl":"galego","glk":"گیلکی","gn":"Avañe'ẽ","gom-latn":"Konknni","got":
"\ud800\udf32\ud800\udf3f\ud800\udf44\ud800\udf39\ud800\udf43\ud800\udf3a","grc":"Ἀρχαία ἑλληνικὴ","gsw":"Alemannisch","gu":"ગુજરાતી","gv":"Gaelg","ha":"Hausa","hak":"客家語\/Hak-kâ-ngî","haw":"Hawai`i","he":"עברית","hi":"हिन्दी","hif":"Fiji Hindi","hif-latn":"Fiji Hindi","hil":"Ilonggo","ho":"Hiri Motu","hr":"hrvatski","hsb":"hornjoserbsce","ht":"Kreyòl ayisyen","hu":"magyar","hy":"Հայերեն","hz":"Otsiherero","ia":"interlingua","id":"Bahasa Indonesia","ie":"Interlingue","ig":"Igbo","ii":"ꆇꉙ","ik":"Iñupiak","ike-cans":"ᐃᓄᒃᑎᑐᑦ","ike-latn":"inuktitut","ilo":"Ilokano","inh":"ГӀалгӀай","io":"Ido","is":"íslenska","it":"italiano","iu":"ᐃᓄᒃᑎᑐᑦ\/inuktitut","ja":"日本語","jam":"Patois","jbo":"Lojban","jut":"jysk","jv":"Basa Jawa","ka":"ქართული","kaa":"Qaraqalpaqsha","kab":"Taqbaylit","kbd":"Адыгэбзэ","kbd-cyrl":"Адыгэбзэ","kg":"Kongo","khw":"کھوار","ki":
"Gĩkũyũ","kiu":"Kırmancki","kj":"Kwanyama","kk":"қазақша","kk-arab":"قازاقشا (تٴوتە)‏","kk-cyrl":"қазақша (кирил)‎","kk-latn":"qazaqşa (latın)‎","kk-cn":"قازاقشا (جۇنگو)‏","kk-kz":"қазақша (Қазақстан)‎","kk-tr":"qazaqşa (Türkïya)‎","kl":"kalaallisut","km":"ភាសាខ្មែរ","kn":"ಕನ್ನಡ","ko":"한국어","ko-kp":"한국어 (조선)","koi":"Перем Коми","kr":"Kanuri","krc":"къарачай-малкъар","kri":"Krio","krj":"Kinaray-a","ks":"कॉशुर \/ کٲشُر","ks-arab":"کٲشُر","ks-deva":"कॉशुर","ksh":"Ripoarisch","ku":"Kurdî","ku-latn":"Kurdî (latînî)‎","ku-arab":"كوردي (عەرەبی)‏","kv":"коми","kw":"kernowek","ky":"Кыргызча","la":"Latina","lad":"Ladino","lb":"Lëtzebuergesch","lbe":"лакку","lez":"лезги","lfn":"Lingua Franca Nova","lg":"Luganda","li":"Limburgs","lij":"Ligure","liv":"Līvõ kēļ","lmo":"lumbaart",
"ln":"lingála","lo":"ລາວ","lrc":"لوری","loz":"Silozi","lt":"lietuvių","ltg":"latgaļu","lus":"Mizo ţawng","lv":"latviešu","lzh":"文言","lzz":"Lazuri","mai":"मैथिली","map-bms":"Basa Banyumasan","mdf":"мокшень","mg":"Malagasy","mh":"Ebon","mhr":"олык марий","mi":"Māori","min":"Baso Minangkabau","mk":"македонски","ml":"മലയാളം","mn":"монгол","mo":"молдовеняскэ","mr":"मराठी","mrj":"кырык мары","ms":"Bahasa Melayu","mt":"Malti","mus":"Mvskoke","mwl":"Mirandés","my":"မြန်မာဘာသာ","myv":"эрзянь","mzn":"مازِرونی","na":"Dorerin Naoero","nah":"Nāhuatl","nan":"Bân-lâm-gú","nap":"Napulitano","nb":"norsk bokmål","nds":"Plattdüütsch","nds-nl":"Nedersaksies","ne":"नेपाली","new":"नेपाल भाषा","ng":"Oshiwambo","niu":"Niuē","nl":"Nederlands","nl-informal":"Nederlands (informeel)‎","nn":"norsk nynorsk","no":"norsk bokmål","nov":
"Novial","nrm":"Nouormand","nso":"Sesotho sa Leboa","nv":"Diné bizaad","ny":"Chi-Chewa","oc":"occitan","om":"Oromoo","or":"ଓଡ଼ିଆ","os":"Ирон","pa":"ਪੰਜਾਬੀ","pag":"Pangasinan","pam":"Kapampangan","pap":"Papiamentu","pcd":"Picard","pdc":"Deitsch","pdt":"Plautdietsch","pfl":"Pälzisch","pi":"पालि","pih":"Norfuk \/ Pitkern","pl":"polski","pms":"Piemontèis","pnb":"پنجابی","pnt":"Ποντιακά","prg":"Prūsiskan","ps":"پښتو","pt":"português","pt-br":"português do Brasil","qu":"Runa Simi","qug":"Runa shimi","rgn":"Rumagnôl","rif":"Tarifit","rm":"rumantsch","rmy":"Romani","rn":"Kirundi","ro":"română","roa-rup":"Armãneashce","roa-tara":"tarandíne","ru":"русский","rue":"русиньскый","rup":"Armãneashce","ruq":"Vlăheşte","ruq-cyrl":"Влахесте","ruq-latn":"Vlăheşte","rw":"Kinyarwanda","sa":"संस्कृतम्","sah":"саха тыла","sat":"Santali","sc":"sardu","scn":"sicilianu","sco":"Scots","sd":
"سنڌي","sdc":"Sassaresu","se":"sámegiella","sei":"Cmique Itom","sg":"Sängö","sgs":"žemaitėška","sh":"srpskohrvatski \/ српскохрватски","shi":"Tašlḥiyt\/ⵜⴰⵛⵍⵃⵉⵜ","shi-tfng":"ⵜⴰⵛⵍⵃⵉⵜ","shi-latn":"Tašlḥiyt","si":"සිංහල","simple":"Simple English","sk":"slovenčina","sl":"slovenščina","sli":"Schläsch","sm":"Gagana Samoa","sma":"Åarjelsaemien","sn":"chiShona","so":"Soomaaliga","sq":"shqip","sr":"српски \/ srpski","sr-ec":"српски (ћирилица)‎","sr-el":"srpski (latinica)‎","srn":"Sranantongo","ss":"SiSwati","st":"Sesotho","stq":"Seeltersk","su":"Basa Sunda","sv":"svenska","sw":"Kiswahili","szl":"ślůnski","ta":"தமிழ்","tcy":"ತುಳು","te":"తెలుగు","tet":"tetun","tg":"тоҷикӣ","tg-cyrl":"тоҷикӣ","tg-latn":"tojikī","th":"ไทย","ti":"ትግርኛ","tk":"Türkmençe","tl":"Tagalog","tly":"толышә зывон","tn":"Setswana","to":"lea faka-Tonga",
"tokipona":"Toki Pona","tpi":"Tok Pisin","tr":"Türkçe","tru":"Ṫuroyo","ts":"Xitsonga","tt":"татарча\/tatarça","tt-cyrl":"татарча","tt-latn":"tatarça","tum":"chiTumbuka","tw":"Twi","ty":"Reo Mā`ohi","tyv":"тыва дыл","udm":"удмурт","ug":"ئۇيغۇرچە \/ Uyghurche","ug-arab":"ئۇيغۇرچە","ug-latn":"Uyghurche","uk":"українська","ur":"اردو","uz":"oʻzbekcha","ve":"Tshivenda","vec":"vèneto","vep":"vepsän kel’","vi":"Tiếng Việt","vls":"West-Vlams","vmf":"Mainfränkisch","vo":"Volapük","vot":"Vaďďa","vro":"Võro","wa":"walon","war":"Winaray","wo":"Wolof","wuu":"吴语","xal":"хальмг","xh":"isiXhosa","xmf":"მარგალური","yi":"ייִדיש","yo":"Yorùbá","yue":"粵語","za":"Vahcuengh","zea":"Zeêuws","zh":"中文","zh-classical":"文言","zh-cn":"中文（中国大陆）‎","zh-hans":"中文（简体）‎","zh-hant":"中文（繁體）‎","zh-hk":"中文（香港）‎","zh-min-nan":"Bân-lâm-gú",
"zh-mo":"中文（澳門）‎","zh-my":"中文（马来西亚）‎","zh-sg":"中文（新加坡）‎","zh-tw":"中文（台灣）‎","zh-yue":"粵語","zu":"isiZulu"};mw.loader.load('//commons.wikimedia.org/w/index.php?title=MediaWiki:AnonymousI18N.js&action=raw&ctype=text/javascript');}});mw.hook('wikipage.content').add(function($content){if($content.find('.ui-button').length){mw.loader.load('jquery.ui.button');}});mw.loader.state({"site":"ready"});
/* cache key: wikidatawiki:resourceloader:filter:minify-js:7:c34976a25eef45913395c46b89b7dcb1 */