(function()
{

	//
	Context = Box.Context = class Context extends Box
	{
		constructor(... _args)
		{
			//
			super(... _args);

			//
			this.identifyAs('context');
		}
	}
	
	//
	if(! customElements.get('a-context'))
	{
		customElements.define('a-context', Context, { is: 'a-box' });
	}
	
	//
	Object.defineProperty(Context, 'INDEX', { get: function()
	{
		const result = [];
		
		for(var i = 0, j = 0; i < Box._INDEX.length; ++i)
		{
			if(is(Box._INDEX[i], 'Context'))
			{
				result[j++] = Box._INDEX[i];
			}
		}
		
		return result;
	}});

	//
	Context.ROOT = null;//todo/
	
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

