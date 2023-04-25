(function()
{

	//
	const DEFAULT_COOKIE = 'uptime';
	const DEFAULT_COOKIE_HOURS = (24 * 31 * 12);

	//
	const uptimeSecond = () => {
		document.setCookie(DEFAULT_COOKIE, ++uptime, DEFAULT_COOKIE_HOURS);
	};

	uptime = null;

	//
	window.addEventListener('ready', () => {
		if(document.isNumericCookie(DEFAULT_COOKIE))
		{
			uptime = document.getCookie(DEFAULT_COOKIE);
		}
		else
		{
			document.setCookie(DEFAULT_COOKIE, uptime = 0, DEFAULT_COOKIE_HOURS);
		}

		timing.add(uptimeSecond, 1);
	}, { once: true });

})();

