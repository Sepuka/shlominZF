<script type="text/javascript" src="../js/adapter/ext/ext-base.js"></script>
<script src="../js/ext-all.js"></script>

<script type="text/javascript">
Ext.onReady(function() {
    // переменная хранит идентификатор текущей статьи
    var articleID = 0;

    var htmlEditor = new Ext.form.HtmlEditor({
        autoScroll: true
    });

    var loadMask = new Ext.LoadMask(
        Ext.getBody(),
        {
            msg: 'Пожалуйста, подождите. Идет загрузка ...'
        }
    );

    // Загрузка контента
    function loadArticle(num) {
        loadMask.show();
        if (Ext.type(num) == 'object') {
            num = num.attributes.id;
            articleID = num;
        }
        Ext.Ajax.request({
        url: 'index.php',
        method: 'GET',
        params: {
            articleID: num,
            id: 'getArticle'
        },
        success: function(answer) {
            // Ответ приходит в виде JSON-массива
            answer = Ext.util.JSON.decode(answer.responseText);
            viewport.items.item(1).setTitle(answer['headline']);
            htmlEditor.setValue(answer['content']);
            toolBar.items.item(1).setValue(answer['headline']);
        },
        failure: function() {
            Ext.Msg.alert("Ошибка AJAX", "Server communication failure");
        },
        callback: function() {
            loadMask.hide();
            toolBar.items.item(1).setDisabled(false);
        }
        })
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
        requestMethod: 'GET',
        dataUrl:'../index.php?id=parentCategories'
    }),
    listeners: {
        click: function(n) {
            loadArticle(n);
        }
    },
    root: {
        nodeType: 'async',
        text: 'Главное меню',
        draggable: false
    }
    });

    var toolBar = new Ext.Toolbar({
        items: [
        {
            xtype: 'tbtext',
            text: 'заголовок'
        },{
            xtype: 'textfield',
            width: '300',
            disabled: true
        },{
            xtype: 'tbseparator'
        },{
            xtype: 'button',
            text: 'сохранить',
            handler: function() {
                if (articleID > 0) {
                    loadMask.show();
                    Ext.Ajax.request({
                        url: 'index.php?id=saveArticle',
                        method: 'POST',
                        params: {
                            articleID: articleID,
                            articleHeadline: toolBar.items.item(1).getValue(),
                            articleText: htmlEditor.getValue()
                        },
                        success: function(answer) {
                            Ext.Msg.alert('Сохранение изменений', answer.responseText);
                        },
                        failure: function() {
                            Ext.Msg.alert("Ошибка AJAX", "Server communication failure");
                        },
                        callback: function() {
                            loadMask.hide();
                        }
                    })
                } else
                    Ext.Msg.alert('Ошибка', 'Выберите статью для редактирования!');
            }
        },{
            xtype: 'button',
            text: 'перезагрузить страницу',
            handler: function() {
                if (articleID > 0)
                    loadArticle(articleID);
                else
                    Ext.Msg.alert('Ошибка', 'Выберите статью для редактирования!');
            }
        }]
    });

    var viewport = new Ext.Viewport({
    layout: 'border',
    autoScroll: true,
    items: [
        {
        region: 'west',
        items: menuTree,
        collapsible: false,
        split: true,
        title: 'Выбор статьи для редактирования',
        width: 250
        },{
        region: 'center',
        title: 'Редактор',
        xtype: 'panel',
        bbar: toolBar,
        items: htmlEditor
        }]
    });

    // Установим высоту содержимого главного окна равным центральному объекту viewport минус высота нижнего и верхнего тулбаров
    htmlEditor.setHeight(viewport.items.itemAt(1).getHeight() - toolBar.getHeight() * 2);
    htmlEditor.setWidth(viewport.items.itemAt(1).getWidth());
})
</script>