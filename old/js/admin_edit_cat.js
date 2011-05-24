<script type="text/javascript" src="../js/adapter/ext/ext-base.js"></script>
<script src="../js/ext-all.js"></script>

<script type="text/javascript">
Ext.onReady(function() {
    // shorthand alias
    var fm = Ext.form;
    var currentRowGrid = 0;

    // Функция отправки данных для удаления
    function confirmDelete(btn) {
        if (btn == 'yes') {
            loadMaskGrid.show();
            Ext.Ajax.request({
                url: 'index.php?id=deleteCategory',
                method: 'POST',
                params: {
                    id: currentRowGrid
                },
                success: function(answer) {
                    alert(answer.responseText);
                    loadMaskGrid.hide();
                    store.reload();
                    currentRowGrid = 0;
                },
                failure: function() {
                    Ext.Msg.alert("Ошибка AJAX", "Server communication failure");
                }
            })
        }
    }

    // форматирование отображения комбобокса "Папка"
    function folderFormat(f) {
        if (f == 0) return 'файл/статья';
        if (f == 1) return 'папка';
    }

    var loadMaskGrid = new Ext.LoadMask(
    Ext.getBody(),
    {
        msg: 'Пожалуйста, подождите. Идет загрузка ...'
    });

    // Окно создания нового ресурса
    var createNewCategory = new Ext.Window({
        closable: false,
        title: "Создание новой категории или статьи",
        layout: 'form',
        defaultType: 'textfield',
        width: 400,
        height: 220,
        labelWidth: 180,
        items: [
            {
                xtype: 'combo',
                fieldLabel: 'категория (родитель)',
                store: {COMBO_PARENTS},
                width: 200
            },{
                fieldLabel: 'подкатегория (потомок)',
                width: 200
            },{
                xtype: 'combo',
                fieldLabel: 'тип ресурса',
                mode: 'local',
                emptyText: 'файл/статья',
                store: [["0","файл/статья"],["1","папка"]],
                width: 200
            },{
                xtype: 'container',
                html: 'Чтобы создать КОРНЕВУЮ директорию, напишите ее имя в поле "подкатегория (потомок)", а поле "категория (родитель)" оставьте пустым. Вы также можете создать категорию-потомок от не существующей категории-родителя, впишите их имена в соответствующие поля и они будут созданы за одно действие.'
            }],
        buttons:
        [
            {
                text: 'Создать новый ресурс',
                handler: function() {
                    createNewCategory.hide();
                    loadMaskGrid.show();
                    Ext.Ajax.request({
                        url: 'index.php?id=addCategory',
                        method: 'POST',
                        params: {
                            parent: createNewCategory.items.itemAt(0).getValue(),
                            name: createNewCategory.items.itemAt(1).getValue(),
                            folder: createNewCategory.items.itemAt(2).getValue()
                        },
                        success: function(answer) {
                            //alert(answer.responseText);
                            loadMaskGrid.hide();
                            store.reload();
                        },
                        failure: function() {
                            Ext.Msg.alert("Ошибка AJAX", "Server communication failure");
                        }
                    })
                }
            },{
                text: 'Закрыть окно',
                handler: function() {
                    createNewCategory.hide();
                }
            }
        ]
    });

    // Описание колонок
    var columnsModel = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [
            {header:'ID', dataIndex:'id', align:'right', width:40},
            {header:'Порядок', dataIndex:'sequence', align:'right', width:40, editor:new fm.TextField({allowBlank: false})},
            {header:'Тип данных', dataIndex:'folder', align:'center', width:50, renderer:folderFormat, editor:new fm.ComboBox({store: [["0","файл/статья"],["1","папка"]]})},
            {header:'Родитель', dataIndex:'parent', align:'right', editor:new fm.TextField({allowBlank: false})},
            {header:'Имя',  dataIndex:'name', align:'right', editor:new fm.TextField({allowBlank: false})}
        ]
    });

    // Хранилище
    var store = new Ext.data.Store({
        reader: new Ext.data.JsonReader({
            totalProperty: 'total',
            root: 'categories',
            fields: [
               {name: 'id', type: 'integer'},
               {name: 'sequence', type: 'integer'},
               {name: 'folder', type: 'string'},
               {name: 'parent', type: 'string'},
               {name: 'name', type: 'string'}
            ]
        }),
        proxy: new Ext.data.HttpProxy({
            url: 'index.php?id=getCategoriesList&content=getCategoriesListJSON',
            method: 'GET'
        }),
        autoSave: true,
        listeners: {
            'update' : (function(store, record, oper) {
                Ext.Ajax.request({
                    url: 'index.php?id=commitCategories',
                    method: 'POST',
                    params: {
                        id: record.id,
                        sequence: record.data['sequence'],
                        folder: record.data['folder'],
                        parent: record.data['parent'],
                        name: record.data['name']
                    },
                    success: function(answer) {
                        alert(answer.responseText);
                    },
                    failure: function() {
                        Ext.Msg.alert("Ошибка AJAX", "Server communication failure");
                    }
                });
            })
        }
    });
    store.load();

    var grid = new Ext.grid.EditorGridPanel({
        store: store,
        cm: columnsModel,
        title: 'Редактирование категорий/подкатегорий сайта',
        viewConfig: {
            forceFit: true,
            autoFill: true
        },
        autoExpandColumn: 'id',
        singleSelect: true,
        stripeRows: true,
        columnLines: true,
        loadMask: loadMaskGrid,
        buttons: [
        {
            text: 'Создать новый ресурс',
            handler: function() {
                createNewCategory.show();
            }
        },{
            text: 'Удалить выбранный ресурс',
            handler: function() {
                if (currentRowGrid == 0)
                    Ext.MessageBox.alert('Ошибка', 'Необходимо выбрать строку для удаления!');
                else {
                    currentRowGrid = grid.getStore().getAt(currentRowGrid).data.id;
                    Ext.MessageBox.confirm('Подтверждение выполнения', 'Вы действительно хотите удалить выделенную запись?', confirmDelete);
                }
            }
        },{
            text: 'Показать все',
            handler: function() {
                store.proxy.setUrl('index.php?id=getCategoriesList&content=getCategoriesListJSON')
                store.load();
            }
        }]
    });

    grid.on('cellclick', function(grid, row, col, e) {
        currentRowGrid = row;
    });
    
    var menu = new Ext.Panel({
        title: 'Навигация по спискам',
        items: [
            new Ext.form.Label({
                html: 'список корневых категорий',
                ctCls: 'LeftNavigPanel'
            }),
            new Ext.form.ComboBox({
                store: {ROOT_CATS_LIST},
                width: 190,
                style: {
                    marginBottom: '20px'
                },
                listeners: {
                    'select' : function (field, record, index) {
                        store.proxy.setUrl('index.php?id=getCategoriesList&content=getChildren&parent=' + field.getValue());
                        store.load();
                    }
                }
            }),
            new Ext.form.Label({
                html: 'список всех категорий',
                ctCls: 'LeftNavigPanel'
            }),
            new Ext.form.ComboBox({
                store: {ALL_CATS_LIST},
                width: 190,
                style: {
                    marginBottom: '20px'
                },
                listeners: {
                    'select' : function (field, record, index) {
                        store.proxy.setUrl('index.php?id=getCategoriesList&content=getChildren&parent=' + field.getValue());
                        store.load();
                    }
                }
            })
        ]
    });

    var viewport = new Ext.Viewport({
        layout: 'border',
        renderTo: Ext.getBody(),
        xtype: 'panel',
        items: [{
            region: 'north',
            height: 50,
            split: false
        },{
            region: 'center',
            items: [grid]
        },{
            region: 'west',
            width: 200,
            split: false,
            items: [menu]
        }]
    });

    grid.setHeight(viewport.items.itemAt(1).getHeight());
});
</script>