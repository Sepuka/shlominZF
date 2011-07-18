var navigation = Ext.create('Ext.panel.Panel', {
	title: 'навигация',
	items: [{
		html: '<a href="/">На сайт<a>'
	},{
		html: '<a href="/admin">В админку</a>'
	},{
		html: '<a href="/admin/logout">Выйти (logout)</a>'
	}]
});