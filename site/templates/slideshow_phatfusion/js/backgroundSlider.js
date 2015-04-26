
/**************************************************************

	Script		: Background Slider
	Version		: 1.3
	Authors		: Samuel Birch
	Desc		: Slides a layer to a given elements position and dimensions.
	Licence		: Open Source MIT Licence

**************************************************************/

var backgroundSlider = new Class({

	/**
	 *
	 * @returns {{duration: number, wait: number, transition: easeInOut, className: null, fixHeight: null, fixWidth: null, start: number, id: null, padding: {top: number, right: number, bottom: number, left: number}, _onClick: Function, mouseOver: boolean}}
	 */
	getOptions: function(){
		
//		alert ('MooTools.version: ' + MooTools.version);
		console.log("Back:getOptions start/exit");

		return {
			duration: 300,
			wait: 500,
			transition: Fx.Transitions.Sine.easeInOut,
			className: null,
			fixHeight: null,
			fixWidth: null,
			start: 1,
			id: null,
			padding: {top:0,right:0,bottom:0,left:0},
			_onClick: function(){},
			mouseOver: true
		};
	},

	/**
	 *
	 * @param elements
	 * @param options
	 */
	initialize: function(elements, options){
		try {
			console.log("Back:initialize");
			this.setOptions(this.getOptions(), options);
			
			this.elements = $$(elements);
			this.timer = 0;
			
			if(this.options.id){
				this.bg = $(this.options.id);
			}else{
				this.bg = new Element('div').setProperty('id','BgSlider_'+new Date().getTime()).inject(document.body, 'bottom');
				if(this.options.className){
					this.bg.addClass(this.options.className);	
				}
			}
			
			this.effects = new Fx.Morph(this.bg, {duration: this.options.duration, transition: this.options.transition});
			
			this.elements.each(function(el,i){
				if(this.options.mouseOver){
					el.addEvent('mouseover', this.move.bind(this,el));
					el.addEvent('mouseout', this.delayReset.bind(this));
				}
				el.addEvent('click', this.setStart.bind(this, el));
			},this);
			
			this.set(this.elements[this.options.start-1]);

			this.mouseOver = false;
			this.bg.addEvent('mouseover', function(){this.mouseOver = true;}.bind(this));
			this.bg.addEvent('mouseout', function(){this.mouseOver = false; this.reset();}.bind(this));
			this.bg.addEvent('click', this.setStart.bind(this,false));

			window.addEvent('resize',function(e){
				this.move(this.startElement);
			}.bind(this));
			
			console.log("Back:initialize exit");
		}
		catch (err)
		{
			alert ("phat115:" + err.message); 
		}
	},
	
	setStart: function(el){
		try {
			console.log("Back:setStart");
			if(el){
				this.startElement = el;
			}else{
				this.startElement = this.currentElement;
			}
			this.options._onClick(this.startElement);

			console.log("Back:setStart exit");
		}
		catch (err)
		{
			alert ("phat125:" + err.message); 
		}
	},
	
	set: function(el){
		try {
			console.log("Back:set");
			this.setStart(el);
			var pos = el.getCoordinates();
			
			if(this.options.id){
				this.options.padding.top = this.bg.getStyle('paddingTop').toInt();
				this.options.padding.right = this.bg.getStyle('paddingRight').toInt();
				this.options.padding.bottom = this.bg.getStyle('paddingBottom').toInt();
				this.options.padding.left = this.bg.getStyle('paddingLeft').toInt();
				this.bg.setStyle('padding','0px');
			}
			
			var obj = {};
			obj.position = 'absolute';
			obj.top = (pos.top-this.options.padding.top-1)+'px';
			obj.left = (pos.left-this.options.padding.left-1)+'px';
			if(!this.options.fixHeight){
				obj.height = (pos.height+this.options.padding.top+this.options.padding.bottom)+'px';
			}
			if(!this.options.fixWidth){
				obj.width = (pos.width+this.options.padding.left+this.options.padding.right)+'px';
			}
			
			this.bg.setStyles(obj);

			console.log("Back:set");
		}
		catch (err)
		{
			alert ("phat130:" + err.message); 
		}
	},
	
	delayReset: function(){
		try {
			console.log("Back:delayReset");
			this.reset.delay(500, this);
			console.log("Back:delayReset exit");
		}
		catch (err)
		{
			alert ("phat133:" + err.message); 
		}
	},
	
	reset: function(){
		try {
			console.log("Back:reset");
			// $clear => use the native clearTimeout when using fn.delay, use clearInterval when using fn.periodical.
			//$clear(this.timer);
			clearTimeout(this.timer);
			if(!this.mouseOver){
				if(this.options.wait){
					this.timer = this.move.delay(this.options.wait, this, this.startElement);
				}
			}
			console.log("Back:reset exit");
		}
		catch (err)
		{
			alert ("phat135:" + err.message); 
		}
	},
	
	move: function(el){
		try {
			console.log("Back:move");
			// $clear => use the native clearTimeout when using fn.delay, use clearInterval when using fn.periodical.
			// this.clearTimeout(this.timer);
			//$clear(this.timer);
			clearTimeout(this.timer);
			var pos = el.getCoordinates();
		    this.effects.cancel();
		    this.currentElement = el;
						
			var obj = {};
			obj.top = pos.top-this.options.padding.top-1;
			obj.left = pos.left-this.options.padding.left-1;
			if(!this.options.fixHeight) {
				obj.height = pos.height+this.options.padding.top+this.options.padding.bottom;
			}
			if(!this.options.fixWidth){
				obj.width = pos.width+this.options.padding.left+this.options.padding.right;
			}
			
			this.effects.start(obj);
			console.log("Back:move exit");
		}
		catch (err)
		{
			alert ("phat140:" + err.message); 
		}
	}

});
backgroundSlider.implement(new Options);
backgroundSlider.implement(new Events);

/*************************************************************/
