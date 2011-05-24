<script type="text/javascript" src="js/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="js/ext-all.js"></script>
<script type="text/javascript" src="js/prettify/prettify.js"></script>
<script type="text/javascript">
Ext.onReady(function() {
    var mainContent = new Ext.Panel({
        autoScroll: true,
        items: [
            {
                xtype: 'container',
                style: {
                    marginLeft: '4px',
                    marginRight: '2px',
                    // http://code.google.com/webfonts/family?family=PT+Sans&subset=cyrillic#specimen
                    fontFamily: 'PT Sans, arial, serif',
                    fontSize: '13px'
                }
            }
        ],
        bbar: {
            buttonAlign: 'right',
            items: [
                {
                    xtype: 'tbitem',
                    id: 'timePublish'
                },{
                    xtype: 'tbspacer'
                },{
                    xtype: 'tbseparator'
                },{
                    xtype: 'tbbutton',
                    text: 'Комментировать',
                    disabled: true,
                    id: 'commentButton',
                    handler: function() {
                        alert('Действие не реализовано')
                    }
                }
            ]
        }
    });

    var loadMask = new Ext.LoadMask(
        Ext.getBody(),
        {
            msg: 'Пожалуйста, подождите. Идет загрузка страницы ...'
        }
    );

    function getPage(id) {
        loadMask.show();
        Ext.Ajax.request({
            url: 'index.php',
            method: 'GET',
            params: {
                articleID: id,
                id: 'loadContent'
            },
            success: function(answer) {
                // Ответ приходит в виде JSON-массива
                answer = Ext.util.JSON.decode(answer.responseText);
                mainContent.items.itemAt(0).update(answer['content'], true, viewport.items.item(2).setTitle(answer['headline']));
                mainContent.getBottomToolbar().items.itemAt(0).update(answer['date']);
                // Включаем/выключаем кнопку комментариев
                if (answer['error'] == 'false') {
                    Ext.getCmp('commentButton').setDisabled(false)
                } else {
                    Ext.getCmp('commentButton').setDisabled(true)
                }
            },
            failure: function() {
                Ext.Msg.alert("Ошибка AJAX", "Server communication failure")
            },
            callback: function() {
                // Прячем маску загрузки
                loadMask.hide();
                // Раскраска синтаксиса
                prettyPrint();
            }
        })
    };

    // Функция вызываемая в конце прорисовки страницы
    function postLoad() {
		// правим высоту
		mainContent.setHeight(viewport.items.item(2).getHeight() - viewport.items.item(0).getHeight() + 5);
		// Загружаем запрошенный контент по явному URL
		query = Ext.urlDecode(location.search);
		if (typeof(query.id) !== 'undefined' && query.id != null) {
		    getPage(query.id);
		}
    }

    // Дерево навигации
    var menuTree = new Ext.tree.TreePanel({
    useArrows: true,
    autoScroll: true,
    animate: true,
    enableDD: false,
    containerScroll: true,
    border: false,
    loader: new Ext.tree.TreeLoader({
        requestMethod:'GET',
        dataUrl:'index.php?id=parentCategories'
    }),
    listeners: {
        click: function(n) {
            loadMask.show();
            Ext.Ajax.request({
                url: 'index.php',
                method: 'GET',
                params: {
                    articleID: n.attributes.id,
                    id: 'loadContent'
                },
                success: function(answer) {
                    // Ответ приходит в виде JSON-массива
                    answer = Ext.util.JSON.decode(answer.responseText);
                    mainContent.items.itemAt(0).update(answer['content'], true, viewport.items.item(2).setTitle(answer['headline']));
                    mainContent.getBottomToolbar().items.itemAt(0).update(answer['date']);
                    // Включаем/выключаем кнопку комментариев
                    if (answer['error'] == 'false') {
                        Ext.getCmp('commentButton').setDisabled(false)
                    } else {
                        Ext.getCmp('commentButton').setDisabled(true)
                    }
                },
                failure: function() {
                    Ext.Msg.alert("Ошибка AJAX", "Server communication failure")
                },
                callback: function() {
                    // Прячем маску загрузки
                    loadMask.hide();
                    // Раскраска синтаксиса
                    prettyPrint();
                }
            })
        }
    },
    root: {
        nodeType: 'async',
        text: 'Главное меню',
        draggable: false
    }
    });

    var viewport = new Ext.Viewport({
    layout: 'border',
    autoScroll: true,
    items: [
        new Ext.BoxComponent({
            region: 'north',
            height: 32,
            html: 'Сайт находится в разработке'
        }),
        {
            region: 'west',
            items: [menuTree],
            collapsible: false,
            split: true,
            title: '<a href="map.php" target="blank">Навигация по сайту</a>',
            width: 250
        },{
            region: 'center',
            title: 'Бложек',
            items: [mainContent]
        }]
    });
    postLoad();
});
</script>