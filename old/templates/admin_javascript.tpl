<script type="text/javascript" src="../js/adapter/ext/ext-base.js"></script>
<script src="../js/ext-all.js"></script>

<script type="text/javascript">
Ext.onReady(function() {
    var window = new Ext.Window({
        title: 'Выбор раздела для редактирования',
        closable: false,
        width: 600,
        height: 400,
        minWidth: 300,
        minHeight: 200,
        plain: true,
        bodyStyle: 'padding:5px;',
        style: {
            // Тень
            margin: '10px'
        }
    });

    window.show();
    window.load({
        url: 'index.php',
        method: 'GET',
        params: {
            id: 'choose_section'
        },
        text: 'wait please, loading data ...',
        scripts: true
    });
});
</script>