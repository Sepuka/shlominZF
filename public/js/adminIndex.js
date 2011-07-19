Ext.onReady(function() {
	var AbstractIcon = {
		height: 200,
		width: 200,
		autoShow: true,
		closable: false,
		resizable: false,
		xtype: 'window',
		firstShow: 1
	};
	var CategoriesIcon = new Ext.create('Ext.window.Window', AbstractIcon);
	CategoriesIcon.setTitle('Разделы сайта');
	CategoriesIcon.update('Редактирование разделов (категорий) сайта');
	CategoriesIcon.addListener('activate', function(self, opt) {
		if (self.firstShow) {
			self.firstShow--;
		} else {
			window.location = '/admin/categories';
		}
	});

	var ArticlesIcon = new Ext.create('Ext.window.Window', AbstractIcon);
	ArticlesIcon.setTitle('Статьи');
	ArticlesIcon.update('Редактирование статей сайта');
	ArticlesIcon.addListener('activate', function(self, opt) {
		if (self.firstShow) {
			self.firstShow--;
		} else {
			window.location = '/admin/articles';
		}
	});

	var BackupIcon = new Ext.create('Ext.window.Window', AbstractIcon);
	BackupIcon.setTitle('Настройка архивирования');
	BackupIcon.update('Создание и редактирование правил для работы с бекапами');

	var viewport = new Ext.create('Ext.container.Viewport', {
		layout: 'border',
		renderTo: Ext.getBody(),
		items: [{
			region: 'north',
			itemId: 'headerArea',
			title: 'Панель администрирования сайта v 0.1',
			html: 'Тут будут подсказки',
			height: 150
		},{
			region: 'center',
			itemId: 'contentArea',
			title: 'Разделы панели администрирования',
			items: [
				CategoriesIcon,
				ArticlesIcon,
				BackupIcon
			]
		}]
	});

	var coordinateX = null;
	var coordinateY = viewport.getComponent('headerArea').getHeight() + 30;
	var ScreenWidth = viewport.getWidth();
	function getCoordinateX() {
		if (coordinateX == null)
			coordinateX = 10;
		else {
			coordinateX += 200 + 40;
			if (coordinateX + 200 > ScreenWidth) {
				coordinateY += 200 + 40;
				coordinateX = 10;
			}
		}
		return coordinateX;
	};
	// Установка координат для окон
	CategoriesIcon.setPosition(getCoordinateX(), coordinateY, false);
	ArticlesIcon.setPosition(getCoordinateX(), coordinateY, false);
	BackupIcon.setPosition(getCoordinateX(), coordinateY, false);
});