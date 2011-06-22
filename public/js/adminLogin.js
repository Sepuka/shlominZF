Ext.onReady(function() {
	var loginFRM = Ext.create('Ext.form.Panel', {
		url: '/admin/login',
		method: 'POST',
		standardSubmit: true,
		defaultType: 'textfield',
		fieldDefaults: {
			msgTarget: 'under'
		},
		defaults: {
			anchor: '95%',
			margin: '5 0 0 5'
		},
		items: [{
			xtype: 'fieldcontainer',
			html: wrongDataMsg,
			cls: 'wrongData'
		},{
			fieldLabel: 'логин',
			name: 'login',
			inputType: 'text',
			allowBlank: false,
			blankText: 'Введите свой логин',
			maxLength: 64
		},{
			fieldLabel: 'пароль',
			name: 'password',
			inputType: 'password',
			allowBlank: false,
			blankText: 'Введите свой пароль'
		},{
			xtype: 'fieldcontainer',
			layout: 'hbox',
			hideEmptyLabel: false,
			items: [{
				fieldLabel: 'запомнить меня',
				xtype: 'checkbox',
				name: 'saveme',
				checked: true,
				margin: '0 10 0 0'
			},{
				xtype: 'button',
				text: 'Вход',
				margin: '0 2 0 5',
				handler: function() {
					form = loginFRM.getForm();
					if (form.isValid()) {
						form.submit();
					}
				}
			},{
				xtype: 'button',
				text: 'Отмена',
				margin: '0 0 0 2',
				handler: function() {
					window.location.href = window.location.protocol + '//' + window.location.host;
				}
			}]
		}]
	});

	var loginWND = Ext.create('Ext.window.Window', {
		width: 400,
		title: 'Вход в панель администрирования',
		closable: false,
		resizable: false,
		items: loginFRM
	});

	loginWND.show();
});