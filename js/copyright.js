(function()
{

	//
	const DEFAULT_CUSTOM_SIZES = true;
	const DEFAULT_BLINK_INTERVAL = 20000;

	//
	//TODO/
	//
	// ..und zwar moechte ich auf der website regulaer die base64-version(en) abbilden,
	// bis ein 'pointerover' den klartext animiert (via blur-in-out-"blink")... somit @
	// 'pointerout' das gegenteil wiederum... ;)~
	//
	// PS: bedenke, es gibt noch user-profile bzw. auch chat-funktionalitaet, etc......
	//
	//
	//
	//
	// copyright klasse o.ae... TODO.
	//
	//

	//
	//todo/(.string)
	//
	copyright = {
		user: 'a3VjaGVu',
		host: 'a2Vrc2UuYml6',
		nick: 'S3VjaGVu',
		first: 'U2ViYXN0aWFu',
		last: 'S3VjaGFyY3p5aw==',
		web: 'aHR0cHM6Ly9saWJqcy5kZS8=',
		user: 'a3VjaGVu',
		host: 'a2Vrc2UuYml6'
	};
	
	//
	copyright.getString = (_name = true, _copyright = null, _brackets = true) => {
		if(DEFAULT_CUSTOM_SIZES)
		{
			if(window.innerWidth >= 1000)
			{
				_name = true;
			}
			else
			{
				_name = false;
			}

			if(_copyright !== null)
			{
				if(window.innerWidth >= 480)
				{
					_copyright = true;
				}
				else
				{
					_copyright = false;
				}
			}

			if(window.innerWidth >= 360)
			{
				_brackets = true;
			}
			else
			{
				_brackets = false;
			}
		}

		return (_copyright ? '<span style="font-size: 1.2em; color: rgb(32, 32, 32); text-shadow: 1px 1px 6px white; margin-left: 4px; margin-right: 2px;">©</span> ' : '<span style="font-size: 1.25em;">') + (_name ? '<span style="font-size: 0.7em; margin-right: 16px;">' + atob(copyright.first) + ' ' + atob(copyright.last) + '</span> ' : '') + '<span style="text-shadow: 1px 1px 6px white;">' + (_brackets ? '&lt;' : '') + '<span style="color: rgb(64, 64, 64); margin-left: 3px; margin-right: 3px; font-size: 0.75em;">' + atob(copyright.user) + '<span style="margin-left: 2px; margin-right: 2px;">@</span>' + atob(copyright.host) + '</span>' + (_brackets ? '&gt;' : '') + '</span>';
	};
	
	//
	window.addEventListener('ready', () => {
		//
		COPYRIGHT.innerHTML = copyright.getString();
		COPYRIGHT.blink();

		//
		if(isInt(DEFAULT_BLINK_INTERVAL) && DEFAULT_BLINK_INTERVAL > 0)
		{
			copyright.blinkInterval = setInterval(() => {
				COPYRIGHT.blink();
			}, DEFAULT_BLINK_INTERVAL);
		}
		else
		{
			copyright.blinkInterval = null;
		}
	}, { once: true });

	//
	var resizeTimeout = null;

	window.addEventListener('resize', () => {
		if(resizeTimeout !== null)
		{
			clearTimeout(resizeTimeout);
		}

		resizeTimeout = setTimeout(() => {
			COPYRIGHT.innerHTML = copyright.getString();
			resizeTimeout = null;
		}, document.getVariable('resize-timeout', true));
	});

	//

})();

