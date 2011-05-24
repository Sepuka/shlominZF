<html>
<head>
<title>{TITLE}</title>
{METATAGS}
{COUNTERS}
<script type="text/javascript">
function parseGET() {
var tmp = new Array();
var tmp2 = new Array();
get = new Array();
var url = location.search;
if(url != '') {
	tmp = (url.substr(1)).split('&');
	for(var i=0; i < tmp.length; i++) {
		tmp2 = tmp[i].split('=');
		get[tmp2[0]] = tmp2[1];
	}
}
};
parseGET();
<!-- Браузеры получат редирект, а поисковики увидят исходник-->
if (get['id'] != '') {
	window.location = 'index.php?act=page&id=' + get['id'];
}
</script>
</head>
<body>
{SOURCE}
</body>
</html>