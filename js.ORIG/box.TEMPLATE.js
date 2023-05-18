(function()
{

	//
	const DEFAULT_THROW = true;
		
	//
	Template = Box.Template = class Template extends Box
	{
		constructor(... _args)
		{
			//
			super(... _args);

			//
			this.identifyAs('template');
		}
	}
	
	//
	if(! customElements.get('a-template'))
	{
		customElements.define('a-template', Template, { is: 'a-box' });
	}
	
	//
	Object.defineProperty(Template, 'INDEX', { get: function()
	{
		const result = [];
		
		for(var i = 0, j = 0; i < Box._INDEX.length; ++i)
		{
			if(is(Box._INDEX[i], 'Template'))
			{
				result[j++] = Box._INDEX[i];
			}
		}
		
		return result;
	}});
	
	//
	/*const on = {};

	on.pointermove = Template.onpointermove.bind(Template);
	on.pointerup = Template.onpointerup.bind(Template);
	on.keydown = Template.onkeydown.bind(Template);
	on.keyup = Template.onkeyup.bind(Template);

	for(const idx in on)
	{
		window.addEventListener(idx, on[idx], { passive: false, capture: true });
	}*/

	//
	
})();

