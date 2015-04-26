
/**************************************************************

	Script		: SlideShow
	Version		: 1.3
	Authors		: Samuel Birch
	Desc		: 
	Licence		: Open Source MIT Licence

**************************************************************/

var SlideShow = new Class({

	/**
	 *
	 * @returns {{effect: string, duration: number, transition: Fx.Transitions.linear, direction: string, color: boolean, wait: number, loop: boolean, thumbnails: boolean, thumbnailCls: string, backgroundSlider: boolean, loadingCls: string, onClick: boolean}}
	 */
	getOptions: function(){
		console.log("Slide:getOptions (start/exit)");
		return {
			effect: 'fade', //fade|wipe|slide|random
			duration: 2000,
			transition: Fx.Transitions.linear,
			direction: 'right', //top|right|bottom|left|random
			color: false,
			wait: 5000,
			loop: false,
			thumbnails: false,
			thumbnailCls: 'outline',
			backgroundSlider: false,
			loadingCls: 'loading',
			onClick: false
		};
	},

	/**
	 *
	 * @param container
	 * @param images
	 * @param options
	 */
	initialize: function(container, images, options){
		try {
			console.log("Slide:initialize");
			this.setOptions(this.getOptions(), options);
			
			this.container = $(container);
			this.container.setStyles({
				position: 'relative',
				overflow: 'hidden'
			});
			if(this.options.onClick){
				this.container.addEvent('click', function(){
					this.options.onClick(this.imageLoaded);
				}.bind(this));
			}
			
			console.log("Slide:initialize.01");
			
			this.imagesHolder = new Element('div').setStyles({
				position: 'absolute',
				overflow: 'hidden',
				top: this.container.getStyle('height'),
				left: 0,
				width: '0px',
				height: '0px',
				display: 'none'
			}).inject(this.container, 'bottom');  
			
			console.log("Slide:initialize.02");
			if(typeof(images) == 'string' && !this.options.thumbnails){
				var imageList = [];
				$$('.'+images).each(function(el){
					imageList.push(el.src);
				el.injectInside(this.imagesHolder);
				},this);
				this.images = imageList;
				
			}else if(typeof(images) == 'string' && this.options.thumbnails){
				var imageList = [];
				var srcList = [];
				this.thumbnails = $$('.'+images);
				this.thumbnails.each(function(el,i){
					srcList.push(el.href);
					imageList.push(el.getElement('img'));
					el.href = 'javascript:;';
					el.addEvent('click',function(){
						this.stop();
						this.play(i);				 
					}.bind(this,el,i));
				},this);
				this.images = srcList;
				this.thumbnailImages = imageList;
				
				if(this.options.backgroundSlider){
					this.bgSlider = new backgroundSlider(this.thumbnailImages,{mouseOver: false, duration: this.options.duration, className: this.options.thumbnailCls, padding:{top:0,right:-2,bottom:-2,left:0}});
					this.bgSlider.set(this.thumbnailImages[0]);
				}
			
			}else{
				this.images = images;
			}
			
			console.log("Slide:initialize.03");
			this.loading = new Element('div').addClass(this.options.loadingCls).setStyles({
				position: 'absolute',
				top: 0,
				left: 0,
				zIndex: 3,
				display: 'none',
				width: this.container.getStyle('width'),
				height: this.container.getStyle('height')
			}).inject(this.container, 'bottom');
			
			console.log("Slide:initialize.04");
			this.oldImage = new Element('div').setStyles({
				position: 'absolute',
				overflow: 'hidden',
				top: 0,
				left: 0,
				opacity: 0,
				width: this.container.getStyle('width'),
				height: this.container.getStyle('height')
			}).inject(this.container, 'bottom');
			
			console.log("Slide:initialize.05");
			this.newImage = this.oldImage.clone();
			this.newImage.inject(this.container, 'bottom');
			
			this.timer = 0;
			this.image = -1;
			this.imageLoaded = 0;
			this.stopped = true;
			this.started = false;
			this.animating = false;

			console.log("Slide:initialize exit");
		}
		catch (err) 
		{
			alert ("phat015:" + err.message);  
		}
	},
	
	load: function(){
		try {
			console.log("Slide:load");
			// $clear => use the native clearTimeout when using fn.delay, use clearInterval when using fn.periodical.
			//this.clearTimeout(this.timer);
			//$clear(this.timer);
			clearTimeout(this.timer);
			this.loading.setStyle('display','block');
			this.image++;
			var img = this.images[this.image];
			delete this.imageObj;
			
			var doLoad = true;
			this.imagesHolder.getElements('img').each(function(el){
				var src = this.images[this.image];
				if(el.src == src){
					this.imageObj = el;
					doLoad = false;
					this.add = false;
					this.show();
				}
			},this);
			
			if(doLoad){
				this.add = true;
				this.imageObj = new Asset.image(img, {onload: this.show.bind(this)});
			}
			console.log("Slide:load exit");
		}
		catch (err) 
		{
			alert ("phat020:" + err.message);  
		}
	},

	show: function(add){
		try {
			console.log("Slide:show");
			if(this.add){
				this.imageObj.inject(this.imagesHolder, 'bottom');
			}
			
			this.newImage.setStyles({
				zIndex: 1,
				opacity: 0
			});
			var img = this.newImage.getElement('img');
			if(img){
				img.replaceWith(this.imageObj.clone());
			}else{
				var obj = this.imageObj.clone();
				obj.inject(this.newImage, 'bottom');
			}
			this.imageLoaded = this.image;
			this.loading.setStyle('display','none');
			if(this.options.thumbnails){
				
				if(this.options.backgroundSlider){
					var elT = this.thumbnailImages[this.image];
					this.bgSlider.move(elT);
					this.bgSlider.setStart(elT);
				}else{
					this.thumbnails.each(function(el,i){
						el.removeClass(this.options.thumbnailCls);
						if(i == this.image){
							el.addClass(this.options.thumbnailCls);
						}
					},this);
				}
			}
			this.effect();

			console.log("Slide:show exit");
		}
		catch (err) 
		{
			alert ("phat025:" + err.message);  
		}
	},
	
	wait: function(){
		try {
			console.log("Slide:wait");
			this.timer = this.load.delay(this.options.wait,this);
			console.log("Slide:wait exit");
		}
		catch (err) 
		{
			alert ("phat030:" + err.message);  
		}
	},
	
	play: function(num){
		try {
			console.log("Slide:play");
			if(this.stopped){
				if(num > -1)
					{this.image = num-1;}
				if(this.image < this.images.length){
					this.stopped = false;
					if(this.started){
						this.next();
					}else{
						this.load();
					}
					this.started = true;
				}
			}
			console.log("Slide:play exit");
		}
		catch (err) 
		{
			alert ("phat035:" + err.message);  
		}
	},
	
	stop: function(){
		try {
			console.log("Slide:stop");
			// $clear => use the native clearTimeout when using fn.delay, use clearInterval when using fn.periodical.
			//this.clearTimeout(this.timer);
			//$clear(this.timer);
			clearTimeout(this.timer);
			this.stopped = true;
			console.log("Slide:stop exit");
		}
		catch (err) 
		{
			alert ("phat040:" + err.message);  
		}
	},
	
	next: function(wait){
		try {
			console.log("Slide:next");
			var doNext = true;
			if(wait && this.stopped){
				doNext = false;
			}
			if(this.animating){
				doNext = false;
			}
			if(doNext){
				this.cloneImage();
				// $clear => use the native clearTimeout when using fn.delay, use clearInterval when using fn.periodical.
				// this.clearTimeout(this.timer);
				//$clear(this.timer);
				clearTimeout(this.timer);
				if(this.image < this.images.length-1){
					if(wait){
						this.wait();
					}else{
						this.load();	
					}
				}else{
					if(this.options.loop){
						this.image = -1;
						if(wait){
							this.wait();
						}else{
							this.load();	
						}
					}else{
						this.stopped = true;
					}
				}
			}
			console.log("Slide:next exit");
		}
		catch (err) 
		{
			alert ("phat045:" + err.message);  
		}
	},
	
	previous: function(){
		try {
			console.log("Slide:previous");
			if(this.imageLoaded == 0){
				this.image = this.images.length-2;	
			}else{
				this.image = this.imageLoaded-2;
			}
			this.next();
			console.log("Slide:previous exit");
		}
		catch (err) 
		{
			alert ("phat050:" + err.message);  
		}
	},
	
	cloneImage: function(){
		try {
			console.log("Slide:cloneImage");
			var img = this.oldImage.getElement('img');
			if(img){
				img.replaceWith(this.imageObj.clone());
			}else{
				var obj = this.imageObj.clone();
				obj.inject(this.oldImage, 'bottom');
			}
			
			this.oldImage.setStyles({
				zIndex: 0,
				top: 0,
				left: 0,
				opacity: 1
			});
			
			this.newImage.setStyles({opacity:0});
			console.log("Slide:cloneImage exit");
		}
		catch (err) 
		{
			alert ("phat055:" + err.message);  
		}
	},
	
	
	effect: function(){
		try {
			console.log("Slide:effect");
			this.animating = true;
// ToDo: effects should be set in bnackgroundslide but it iss empty -> have an idea !
			//this.effectObj = this.newImage.effects({
			this.effectObj = this.newImage.effects.set({
				duration: this.options.duration,
				transition: this.options.transition
			});
			
			var myFxTypes = ['fade','wipe','slide'];
			var myFxDir = ['top','right','bottom','left'];
			
			if(this.options.effect == 'fade'){
				this.fade();
				
			}else if(this.options.effect == 'wipe'){
				if(this.options.direction == 'random'){
					this.setup(myFxDir[Math.floor(Math.random()*(3+1))]);
				}else{
					this.setup(this.options.direction);
				}
				this.wipe();
				
			}else if(this.options.effect == 'slide'){
				if(this.options.direction == 'random'){
					this.setup(myFxDir[Math.floor(Math.random()*(3+1))]);
				}else{
					this.setup(this.options.direction);
				}
				this.slide();
				
			}else if(this.options.effect == 'random'){
				var type = myFxTypes[Math.floor(Math.random()*(2+1))];
				if(type != 'fade'){
					var dir = myFxDir[Math.floor(Math.random()*(3+1))];
					if(this.options.direction == 'random'){
						this.setup(dir);
					}else{
						this.setup(this.options.direction);
					}
				}else{
					this.setup();
				}
				this[type]();
			}
			console.log("Slide:effect exit");
		}
		catch (err) 
		{
			alert ("phat060:" + err.message);  
		}
	},
	
	setup: function(dir){
		try {
			console.log("Slide:setup");
			if(dir == 'top'){
				this.top = -this.container.getStyle('height').toInt();
				this.left = 0;
				this.topOut = this.container.getStyle('height').toInt();
				this.leftOut = 0;
				
			}else if(dir == 'right'){
				this.top = 0;
				this.left = this.container.getStyle('width').toInt();
				this.topOut = 0;
				this.leftOut = -this.container.getStyle('width').toInt();
				
			}else if(dir == 'bottom'){
				this.top = this.container.getStyle('height').toInt();
				this.left = 0;
				this.topOut = -this.container.getStyle('height').toInt();
				this.leftOut = 0;
				
			}else if(dir == 'left'){
				this.top = 0;
				this.left = -this.container.getStyle('width').toInt();
				this.topOut = 0;
				this.leftOut = this.container.getStyle('width').toInt();
				
			}else{
				this.top = 0;
				this.left = 0;
				this.topOut = 0;
				this.leftOut = 0;
			}
			console.log("Slide:setup exit");
		}
		catch (err) 
		{
			alert ("phat065:" + err.message);  
		}
	},
	
	fade: function(){
		try {
			console.log("Slide:fade");
			this.effectObj.start({
				opacity: [0,1]
			});
			this.resetAnimation.delay(this.options.duration+90,this);
			if(!this.stopped){
			this.next.delay(this.options.duration+100,this,true);
			}
			console.log("Slide:fade exit");
		}
		catch (err) 
		{
			alert ("phat070:" + err.message);  
		}
	},
	
	wipe: function(){
		try {
			console.log("Slide:wipe");
			this.oldImage.effects({
				duration: this.options.duration,
				transition: this.options.transition
			}).start({
				top: [0,this.topOut],
				left: [0, this.leftOut]
			});
			this.effectObj.start({
				top: [this.top,0],
				left: [this.left,0],
				opacity: [1,1]
			},this);
			this.resetAnimation.delay(this.options.duration+90,this);
			if(!this.stopped){
			this.next.delay(this.options.duration+100,this,true);
			}
			console.log("Slide:wipe exit");
		}
		catch (err) 
		{
			alert ("phat075:" + err.message);  
		}
	},
	
	slide: function(){
		try {
			console.log("Slide:slide");
			this.effectObj.start({
				top: [this.top,0],
				left: [this.left,0],
				opacity: [1,1]
			},this);
			this.resetAnimation.delay(this.options.duration+90,this);
			if(!this.stopped){
			this.next.delay(this.options.duration+100,this,true);
			}
			console.log("Slide:slide exit");
		}
		catch (err) 
		{
			alert ("phat080:" + err.message);  
		}
	},
	
	resetAnimation: function(){
		try {
			console.log("Slide:resetAnimation");
			this.animating = false;
			console.log("Slide:resetAnimation exit");
		}
		catch (err) 
		{
			alert ("phat085:" + err.message);  
		}
	}
	
});
SlideShow.implement(new Options);
SlideShow.implement(new Events);


/*************************************************************/

