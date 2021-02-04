-- Based more or less on the public interwiki map from MeatballWiki
-- Default interwiki prefixes...

REPLACE INTO /*$wgDBprefix*/interwiki (iw_prefix,iw_url,iw_local,iw_api) VALUES
('acronym','https://www.acronymfinder.com/~/search/af.aspx?string=exact&Acronym=$1',0,''),
('advogato','https://www.advogato.org/$1',0,''),
('arxiv','https://www.arxiv.org/abs/$1',0,''),
('c2find','https://c2.com/cgi/wiki?FindPage&value=$1',0,''),
('cache','https://www.google.com/search?q=cache:$1',0,''),
('commons','https://commons.wikimedia.org/wiki/$1',0,'https://commons.wikimedia.org/w/api.php'),
('dictionary','https://www.dict.org/bin/Dict?Database=*&Form=Dict1&Strategy=*&Query=$1',0,''),
('docbook','https://wiki.docbook.org/$1',0,''),
('doi','https://dx.doi.org/$1',0,''),
('drumcorpswiki','https://www.drumcorpswiki.com/$1',0,'https://drumcorpswiki.com/api.php'),
('dwjwiki','https://www.suberic.net/cgi-bin/dwj/wiki.cgi?$1',0,''),
('elibre','https://enciclopedia.us.es/index.php/$1',0,'https://enciclopedia.us.es/api.php'),
('emacswiki','https://www.emacswiki.org/cgi-bin/wiki.pl?$1',0,''),
('foldoc','https://foldoc.org/?$1',0,''),
('foxwiki','https://fox.wikis.com/wc.dll?Wiki~$1',0,''),
('freebsdman','https://www.FreeBSD.org/cgi/man.cgi?apropos=1&query=$1',0,''),
('gej','https://www.esperanto.de/dej.malnova/aktivikio.pl?$1',0,''),
('gentoo-wiki','https://gentoo-wiki.com/$1',0,''),
('google','https://www.google.com/search?q=$1',0,''),
('googlegroups','https://groups.google.com/groups?q=$1',0,''),
('hammondwiki','https://www.dairiki.org/HammondWiki/$1',0,''),
('hrwiki','https://www.hrwiki.org/wiki/$1',0,'https://www.hrwiki.org/w/api.php'),
('imdb','https://www.imdb.com/find?q=$1&tt=on',0,''),
('jargonfile','https://sunir.org/apps/meta.pl?wiki=JargonFile&redirect=$1',0,''),
('kmwiki','https://kmwiki.wikispaces.com/$1',0,''),
('linuxwiki','https://linuxwiki.de/$1',0,''),
('lojban','https://www.lojban.org/tiki/tiki-index.php?page=$1',0,''),
('lqwiki','https://wiki.linuxquestions.org/wiki/$1',0,''),
('lugkr','https://www.lug-kr.de/wiki/$1',0,''),
('meatball','https://www.usemod.com/cgi-bin/mb.pl?$1',0,''),
('mediawikiwiki','https://www.mediawiki.org/wiki/$1',0,'https://www.mediawiki.org/w/api.php'),
('mediazilla','https://bugzilla.wikimedia.org/$1',0,''),
('memoryalpha','https://en.memory-alpha.org/wiki/$1',0,'https://en.memory-alpha.org/api.php'),
('metawiki','https://sunir.org/apps/meta.pl?$1',0,''),
('metawikimedia','https://meta.wikimedia.org/wiki/$1',0,'https://meta.wikimedia.org/w/api.php'),
('mozillawiki','https://wiki.mozilla.org/$1',0,'https://wiki.mozilla.org/api.php'),
('mw','https://www.mediawiki.org/wiki/$1',0,'https://www.mediawiki.org/w/api.php'),
('oeis','https://oeis.org/$1',0,''),
('openwiki','https://openwiki.com/ow.asp?$1',0,''),
('ppr','https://c2.com/cgi/wiki?$1',0,''),
('pythoninfo','https://wiki.python.org/moin/$1',0,''),
('rfc','https://www.rfc-editor.org/rfc/rfc$1.txt',0,''),
('s23wiki','https://s23.org/wiki/$1',0,'https://s23.org/w/api.php'),
('seattlewireless','https://seattlewireless.net/$1',0,''),
('senseislibrary','https://senseis.xmp.net/?$1',0,''),
('shoutwiki','https://www.shoutwiki.com/wiki/$1',0,'https://www.shoutwiki.com/w/api.php'),
('sourcewatch','https://www.sourcewatch.org/index.php?title=$1',0,'https://www.sourcewatch.org/api.php'),
('squeak','https://wiki.squeak.org/squeak/$1',0,''),
('tejo','https://www.tejo.org/vikio/$1',0,''),
('tmbw','https://www.tmbw.net/wiki/$1',0,'https://tmbw.net/wiki/api.php'),
('tmnet','https://www.technomanifestos.net/?$1',0,''),
('theopedia','https://www.theopedia.com/$1',0,''),
('twiki','https://twiki.org/cgi-bin/view/$1',0,''),
('uea','https://uea.org/vikio/index.php/$1',0,'https://uea.org/vikio/api.php'),
('uncyclopedia','https://en.uncyclopedia.co/wiki/$1',0,'https://en.uncyclopedia.co/w/api.php'),
('unreal','https://wiki.beyondunreal.com/$1',0,'https://wiki.beyondunreal.com/w/api.php'),
('usemod','https://www.usemod.com/cgi-bin/wiki.pl?$1',0,''),
('webseitzwiki','https://webseitz.fluxent.com/wiki/$1',0,''),
('wiki','https://c2.com/cgi/wiki?$1',0,''),
('wikia','https://www.wikia.com/wiki/$1',0,''),
('wikibooks','https://en.wikibooks.org/wiki/$1',0,'https://en.wikibooks.org/w/api.php'),
('wikif1','https://www.wikif1.org/$1',0,''),
('wikihow','https://www.wikihow.com/$1',0,'https://www.wikihow.com/api.php'),
('wikinfo','https://wikinfo.co/English/index.php/$1',0,''),
('wikimedia','https://wikimediafoundation.org/wiki/$1',0,'https://wikimediafoundation.org/w/api.php'),
('wikinews','https://en.wikinews.org/wiki/$1',0,'https://en.wikinews.org/w/api.php'),
('wikipedia','https://en.wikipedia.org/wiki/$1',0,'https://en.wikipedia.org/w/api.php'),
('wikiquote','https://en.wikiquote.org/wiki/$1',0,'https://en.wikiquote.org/w/api.php'),
('wikisource','https://wikisource.org/wiki/$1',0,'https://wikisource.org/w/api.php'),
('wikispecies','https://species.wikimedia.org/wiki/$1',0,'https://species.wikimedia.org/w/api.php'),
('wikiversity','https://en.wikiversity.org/wiki/$1',0,'https://en.wikiversity.org/w/api.php'),
('wikivoyage','https://en.wikivoyage.org/wiki/$1',0,'https://en.wikivoyage.org/w/api.php'),
('wikt','https://en.wiktionary.org/wiki/$1',0,'https://en.wiktionary.org/w/api.php'),
('wiktionary','https://en.wiktionary.org/wiki/$1',0,'https://en.wiktionary.org/w/api.php')
;
