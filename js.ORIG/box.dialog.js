(function()
{

	//
	const DEFAULT_THROW = true;
		
	//
	Dialog = Box.Dialog = class Dialog extends Box
	{
		constructor(... _args)
		{
			//
			super(... _args);

			//
			this.identifyAs('dialog');
		}
	}
	
	//
	if(! customElements.get('a-dialog'))
	{
		customElements.define('a-dialog', Dialog, { is: 'a-box' });
	}
	
	//
	Object.defineProperty(Dialog, 'INDEX', { get: function()
	{
		const result = [];
		
		for(var i = 0, j = 0; i < Box._INDEX.length; ++i)
		{
			if(is(Box._INDEX[i], 'Dialog'))
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

