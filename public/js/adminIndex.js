Ext.onReady(function() {
	Ext.create('Ext.container.Viewport', {
		layout: 'border',
		renderTo: Ext.getBody(),
		items: [{
			region: 'north',
			title: 'Панель администрирования сайта v 0.1',
			html: 'Тут будут подсказки',
			height: 150
		},{
			region: 'center',
			title: 'Разделы панели администрирования',
			html: 'Тут будет содержимое',
		}]
	})
});