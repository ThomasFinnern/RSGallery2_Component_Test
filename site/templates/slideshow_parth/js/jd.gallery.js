/*
    This file is part of JonDesign's SmoothGallery v2.1beta1.

    JonDesign's SmoothGallery is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    JonDesign's SmoothGallery is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with JonDesign's SmoothGallery; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

    Main Developer: Jonathan Schemoul (JonDesign: http://www.jondesign.net/)
    Contributed code by:
    - Christian Ehret (bugfix)
	- Nitrix (bugfix)
	- Valerio from Mad4Milk for his great help with the carousel scrolling and many other things.
	- Archie Cowan for helping me find a bugfix on carousel inner width problem.
	- Tomocchino from #mootools for the preloader class
	Many thanks to:
	- The mootools team for the great mootools lib, and it's help and support throughout the project.
	- Harald Kirschner (digitarald: http://digitarald.de/) for all his great libs. Some used here as plugins.
*/

/* https://github.com/mootools/mootools-core/wiki/Upgrade-from-1.2-to-1.3-or-1.4 */

/* some quirks to circumvent broken stuff in mt1.2 */
function isBody(element){
	return (/^(?:body|html)$/i).test(element.tagName);
}

Element.implement({
	getPosition: function(relative){
		try {
			if (isBody(this)) 
				return {x: 0, y: 0};
			var el = this, position = {x: 0, y: 0};
			while (el){
				position.x += el.offsetLeft;
				position.y += el.offsetTop;
				el = el.offsetParent;
			}
			var rpos = (relative) ? $(relative).getPosition() : {x: 0, y: 0};
			return {x: position.x - rpos.x, y: position.y - rpos.y};
		}
		catch (err) 
		{
			alert ("parth001:" + err.message); 
			return {x: 0, y: 0};
		}
	}
});

// declaring the class
var gallery = {
	Implements: [Events, Options],
	options: {
		showArrows: true,
		showCarousel: true,
		showInfopane: true,
		embedLinks: true,
		fadeDuration: 500,
		timed: false,
		delay: 9000,
		preloader: true,
		preloaderImage: true,
		preloaderErrorImage: true,
		/* Data retrieval */
		manualData: [],
		populateFrom: false,
		populateData: true,
		destroyAfterPopulate: true,
		elementSelector: "div.imageElement",
		titleSelector: "h3",
		subtitleSelector: "p",
		linkSelector: "a.open",
		imageSelector: "img.full",
		thumbnailSelector: "img.thumbnail",
		defaultTransition: "fade",
		/* InfoPane options */
		slideInfoZoneOpacity: 0.7,
		slideInfoZoneSlide: true,
		/* Carousel options */
		carouselMinimizedOpacity: 0.4,
		carouselMinimizedHeight: 20,
		carouselMaximizedOpacity: 0.9,
		thumbHeight: 75,
		thumbWidth: 100,
		thumbSpacing: 10,
		thumbIdleOpacity: 0.2,
		textShowCarousel: 'Pictures',
		showCarouselLabel: true,
		thumbCloseCarousel: true,
		useThumbGenerator: false,
		thumbGenerator: 'resizer.php',
		useExternalCarousel: false,
		carouselElement: false,
		carouselHorizontal: true,
		activateCarouselScroller: true,
		carouselPreloader: true,
		textPreloadingCarousel: 'Loading...',
		/* CSS Classes */
		baseClass: 'jdGallery',
		withArrowsClass: 'withArrows',
		/* Plugins: HistoryManager */
		useHistoryManager: false,
		customHistoryKey: false,
		/* Plugins: ReMooz */
		useReMooz: false
	},
	initialize: function(element, options) {

		try {
			this.setOptions(options);
			this.fireEvent('onInit');
			this.currentIter = 0;
			this.lastIter = 0;
			this.maxIter = 0;
			this.galleryElement = element;
			this.galleryData = this.options.manualData;
			this.galleryInit = 1;
			this.galleryElements = [];
			this.thumbnailElements = [];
			this.galleryElement.addClass(this.options.baseClass);
			
			if (this.options.useReMooz&&(this.options.defaultTransition=="fade"))
				this.options.defaultTransition="crossfade";

			this.populateFrom = element;
			if (this.options.populateFrom)
				this.populateFrom = this.options.populateFrom;		
			if (this.options.populateData)
				this.populateData();
			element.style.display="block";

			if (this.options.useHistoryManager)
				this.initHistory();
			
			if (this.options.embedLinks||this.options.useReMooz)
			{
				this.currentLink = new Element('a').addClass('open').setProperties({
					href: '#',
					title: ''
				}).inject(element, 'bottom');
				this.currentLink.inject(element, 'bottom');
				if ((!this.options.showArrows) && (!this.options.showCarousel))
					this.galleryElement = element = this.currentLink;
				else
					this.currentLink.setStyle('display', 'none');
			}

			this.constructElements();
			if ((this.galleryData.length>1)&&(this.options.showArrows))
			{
				var leftArrow = new Element('a').addClass('left').addEvent(
					'click',
					this.prevItem.bind(this)
				).inject(element, 'bottom');
				var rightArrow = new Element('a').addClass('right').addEvent(
					'click',
					this.nextItem.bind(this)
				).inject(element, 'bottom');
				this.galleryElement.addClass(this.options.withArrowsClass);
			}

			this.loadingElement = new Element('div').addClass('loadingElement').inject(element, 'bottom');
			if (this.options.showInfopane) this.initInfoSlideshow();
			if (this.options.showCarousel) this.initCarousel();

			this.doSlideShow(1);
		}
		catch (err) 
		{
			alert ("parth005:" + err.message); 
		}
	},
	populateData: function() {

		try {
			currentArrayPlace = this.galleryData.length;
			options = this.options;

			//var data = $A(this.galleryData);
			var data = Array.from(this.galleryData); // Note: Array.from does not always return a new array 

			var populatedGallery = this.populateGallery(this.populateFrom, currentArrayPlace);

			data.append(populatedGallery);
			this.galleryData = data;
			this.fireEvent('onPopulated');
		}
		catch (err) 
		{
			alert ("parth010:" + err.message); 
		}
	},
	populateGallery: function(element, startNumber) {
		var data = [];
		try {
			options = this.options;
			currentArrayPlace = startNumber;
			element.getElements(options.elementSelector).each(function(el) {
				var elementDict = $H({
					image: el.getElement(options.imageSelector).getProperty('src'),
					number: currentArrayPlace,
					transition: this.options.defaultTransition
				});
				if (options.showInfopane || options.showCarousel) {
					elementDict.extend({
						title: el.getElement(options.titleSelector).innerHTML,
						description: el.getElement(options.subtitleSelector).innerHTML
					});
				}
				if (options.embedLinks || options.useReMooz) {
					elementDict.extend({
						link: el.getElement(options.linkSelector).href || false,
						linkTitle: el.getElement(options.linkSelector).title || false,
						linkTarget: el.getElement(options.linkSelector).getProperty('target') || false
					});
				}
				if ((!options.useThumbGenerator) && (options.showCarousel)) {
					elementDict.extend({
						thumbnail: el.getElement(options.thumbnailSelector).getProperty('src')
					});
				}
				else if (options.useThumbGenerator) {
					elementDict.extend({
						thumbnail: options.thumbGenerator + '?imgfile=' + elementDict.image + '&max_width=' + options.thumbWidth + '&max_height=' + options.thumbHeight
					});
				}

				data.append([elementDict]);
				currentArrayPlace++;

				if (this.options.destroyAfterPopulate) {
					el.dispose();
				}
			});
		}
		catch (err) 
		{
			alert ("parth015:" + err.message); 
			return {x: 0, y: 0};
		}
		
		return data;
	},
	constructElements: function() {
		try {
			el = this.galleryElement;
			if (this.options.embedLinks && (!this.options.showArrows))
				el = this.currentLink;
			this.maxIter = this.galleryData.length;
			var currentImg;
			for(i=0;i<this.galleryData.length;i++)
			{
				var currentImg = new Fx.Morph(
					new Element('div').addClass('slideElement').setStyles({
						'position':'absolute',
						'left':'0px',
						'right':'0px',
						'margin':'0px',
						'padding':'0px',
						'backgroundPosition':"center center",
						'opacity':'0'
					}).inject(el, 'bottom'),
					{duration: this.options.fadeDuration}
				);
				if (this.options.preloader)
				{
					currentImg.source = this.galleryData[i].image;
					currentImg.loaded = false;
					currentImg.load = function(imageStyle, i) {
						if (!imageStyle.loaded)	{
							this.galleryData[i].imgloader = new Asset.image(imageStyle.source, {
										'onload'  : function(img, i){
														img.element.setStyle(
														'backgroundImage',
														"url('" + img.source + "')")
														img.loaded = true;
														img.width = this.galleryData[i].imgloader.width;
														img.height = this.galleryData[i].imgloader.height;
													}.pass([imageStyle, i], this)
							});
						}
					}.pass([currentImg, i], this);
				} else {
					currentImg.element.setStyle('backgroundImage',
										"url('" + this.galleryData[i].image + "')");
				}
				this.galleryElements[parseInt(i)] = currentImg;
			}
		}
		catch (err) 
		{
			alert ("parth020:" + err.message); 
		}
	},
	destroySlideShow: function(element) {
		try {
			var myClassName = element.className;
			var newElement = new Element('div').addClass('myClassName');
			element.parentNode.replaceChild(newElement, element);
		}
		catch (err) 
		{
			alert ("parth025:" + err.message); 
		}
	},
	startSlideShow: function() {
		try {
			this.fireEvent('onStart');
			this.loadingElement.style.display = "none";
			this.lastIter = this.maxIter - 1;
			this.currentIter = 0;
			this.galleryInit = 0;
			this.galleryElements[parseInt(this.currentIter)].set({opacity: 1});
			if (this.options.showInfopane)
				this.showInfoSlideShow.delay(1000, this);
			if (this.options.useReMooz)
				this.makeReMooz.delay(1000, this);
			var textShowCarousel = formatString(this.options.textShowCarousel, this.currentIter+1, this.maxIter);
			if (this.options.showCarousel&&(!this.options.carouselPreloader)&&(!this.options.useExternalCarousel))
				this.carouselBtn.set('html', textShowCarousel).setProperty('title', textShowCarousel);
			this.prepareTimer();
			if (this.options.embedLinks)
				this.makeLink(this.currentIter);
		}
		catch (err) 
		{
			alert ("parth030:" + err.message); 
		}
	},
	nextItem: function() {
		try {
			this.fireEvent('onNextCalled');
			this.nextIter = this.currentIter+1;
			if (this.nextIter >= this.maxIter)
				this.nextIter = 0;
			this.galleryInit = 0;
			this.goTo(this.nextIter);
		}
		catch (err) 
		{
			alert ("parth035:" + err.message); 
		}
	},
	prevItem: function() {
		try {
			this.fireEvent('onPreviousCalled');
			this.nextIter = this.currentIter-1;
			if (this.nextIter <= -1)
				this.nextIter = this.maxIter - 1;
			this.galleryInit = 0;
			this.goTo(this.nextIter);
		}
		catch (err) 
		{
			alert ("parth040:" + err.message); 
		}
	},
	goTo: function(num) {
		try {
			this.clearTimer();
			if(this.options.preloader)
			{
				this.galleryElements[num].load();
				if (num==0)
					this.galleryElements[this.maxIter - 1].load();
				else
					this.galleryElements[num - 1].load();
				if (num==(this.maxIter - 1))
					this.galleryElements[0].load();
				else
					this.galleryElements[num + 1].load();
					
			}
			if (this.options.embedLinks)
				this.clearLink();
			if (this.options.showInfopane)
			{
				this.slideInfoZone.clearChain();
				this.hideInfoSlideShow().chain(this.changeItem.pass(num, this));
			} else
				this.currentChangeDelay = this.changeItem.delay(500, this, num);
			if (this.options.embedLinks)
				this.makeLink(num);
			this.prepareTimer();
			/*if (this.options.showCarousel)
				this.clearThumbnailsHighlights();*/
		}
		catch (err) 
		{
			alert ("parth045:" + err.message); 
		}
	},
	changeItem: function(num) {
		try {
			this.fireEvent('onStartChanging');
			this.galleryInit = 0;
			if (this.currentIter != num)
			{
				for(i=0;i<this.maxIter;i++)
				{
					if ((i != this.currentIter)) this.galleryElements[i].set({opacity: 0});
				}
				gallery.Transitions[this.galleryData[num].transition].pass([
					this.galleryElements[this.currentIter],
					this.galleryElements[num],
					this.currentIter,
					num], this)();
				this.currentIter = num;
				if (this.options.useReMooz)
					this.makeReMooz();
			}
			var textShowCarousel = formatString(this.options.textShowCarousel, num+1, this.maxIter);
			if ((this.options.showCarousel)&&(!this.options.useExternalCarousel))
				this.carouselBtn.set('html', textShowCarousel).setProperty('title', textShowCarousel);
			this.doSlideShow.bind(this)();
			this.fireEvent('onChanged');
		}
		catch (err) 
		{
			alert ("parth050:" + err.message); 
		}
	},
	clearTimer: function() {
		try {
			if (this.options.timed)
				clearInterval(this.timer);
		}
		catch (err) 
		{
			alert ("parth055:" + err.message); 
		}
	},
	prepareTimer: function() {
		try {
			if (this.options.timed)
				this.timer = this.nextItem.delay(this.options.delay, this);
		}
		catch (err) 
		{
			alert ("parth060:" + err.message); 
		}
	},
	doSlideShow: function(position) {
		try {
			if (this.galleryInit == 1)
			{
				imgPreloader = new Image();
				imgPreloader.onload=function(){
					this.startSlideShow.delay(10, this);
				}.bind(this);
				imgPreloader.src = this.galleryData[0].image;
				if(this.options.preloader)
					this.galleryElements[0].load();
			} else {
				if (this.options.showInfopane)
				{
					if (this.options.showInfopane)
					{
						this.showInfoSlideShow.delay((500 + this.options.fadeDuration), this);
					} else
						if ((this.options.showCarousel)&&(this.options.activateCarouselScroller))
							this.centerCarouselOn(position);
				}
			}
		}
		catch (err) 
		{
			alert ("parth065:" + err.message); 
		}
	},
	createCarousel: function() {
		try {
			var carouselElement;
			if (!this.options.useExternalCarousel)
			{
				var carouselContainerElement = new Element('div').addClass('carouselContainer').inject(this.galleryElement, 'bottom');
				this.carouselContainer = new Fx.Morph(carouselContainerElement, {transition: Fx.Transitions.Expo.easeOut});
				this.carouselContainer.normalHeight = carouselContainerElement.offsetHeight;
				this.carouselContainer.set({'opacity': this.options.carouselMinimizedOpacity, 'top': (this.options.carouselMinimizedHeight - this.carouselContainer.normalHeight)});
				this.carouselBtn = new Element('a').addClass('carouselBtn').setProperties({
					title: this.options.textShowCarousel
				}).inject(carouselContainerElement, 'bottom');
				if(this.options.carouselPreloader)
					this.carouselBtn.set('html', this.options.textPreloadingCarousel);
				else
					this.carouselBtn.set('html', this.options.textShowCarousel);
				this.carouselBtn.addEvent(
					'click',
					function () {
						this.carouselContainer.cancel();
						this.toggleCarousel();
					}.bind(this)
				);
				this.carouselActive = false;
		
				carouselElement = new Element('div').addClass('carousel').inject(carouselContainerElement, 'bottom');
				this.carousel = new Fx.Morph(carouselElement);
			} else {
				carouselElement = $(this.options.carouselElement).addClass('jdExtCarousel');
			}
			this.carouselElement = new Fx.Morph(carouselElement, {transition: Fx.Transitions.Expo.easeOut});
			this.carouselElement.normalHeight = carouselElement.offsetHeight;
			if (this.options.showCarouselLabel)
				this.carouselLabel = new Element('p').addClass('label').inject(carouselElement, 'bottom');
			carouselWrapper = new Element('div').addClass('carouselWrapper').inject(carouselElement, 'bottom');
			this.carouselWrapper = new Fx.Morph(carouselWrapper, {transition: Fx.Transitions.Expo.easeOut});
			this.carouselWrapper.normalHeight = carouselWrapper.offsetHeight;
			this.carouselInner = new Element('div').addClass('carouselInner').inject(carouselWrapper, 'bottom');
			if (this.options.activateCarouselScroller)
			{
				this.carouselWrapper.scroller = new Scroller(carouselWrapper, {
					area: 100,
					velocity: 0.2
				})
				
				this.carouselWrapper.elementScroller = new Fx.Scroll(carouselWrapper, {
					duration: 400,
					onStart: this.carouselWrapper.scroller.stop.bind(this.carouselWrapper.scroller),
					onComplete: this.carouselWrapper.scroller.start.bind(this.carouselWrapper.scroller)
				});
			}
		}
		catch (err) 
		{
			alert ("parth070:" + err.message); 
		}
	},
	fillCarousel: function() {
		try {
			this.constructThumbnails();
			this.carouselInner.normalWidth = ((this.maxIter * (this.options.thumbWidth + this.options.thumbSpacing + 2))+this.options.thumbSpacing) + "px";
			if (this.options.carouselHorizontal)
				this.carouselInner.style.width = this.carouselInner.normalWidth;
		}
		catch (err) 
		{
			alert ("parth075:" + err.message); 
		}
	},
	initCarousel: function () {
		try {
			this.createCarousel();
			this.fillCarousel();
			if (this.options.carouselPreloader)
				this.preloadThumbnails();
		}
		catch (err) 
		{
			alert ("parth080:" + err.message); 
			return {x: 0, y: 0};
		}
	},
	flushCarousel: function() {
		try {
			this.thumbnailElements.each(function(myFx) {
				myFx.element.dispose();
				myFx = myFx.element = null;
			});
			this.thumbnailElements = [];
		}
		catch (err) 
		{
			alert ("parth085:" + err.message); 
		}
	},
	toggleCarousel: function() {
		try {
			if (this.carouselActive)
				this.hideCarousel();
			else
				this.showCarousel();
		}
		catch (err) 
		{
			alert ("parth090:" + err.message); 
		}
	},
	showCarousel: function () {
		try {
			this.fireEvent('onShowCarousel');
			this.carouselContainer.start({
				'opacity': this.options.carouselMaximizedOpacity,
				'top': 0
			}).chain(function() {
				this.carouselActive = true;
				this.carouselWrapper.scroller.start();
				this.fireEvent('onCarouselShown');
				this.carouselContainer.options.onComplete = null;
			}.bind(this));
		}
		catch (err) 
		{
			alert ("parth095:" + err.message); 
		}
	},
	hideCarousel: function () {
		try {
			this.fireEvent('onHideCarousel');
			var targetTop = this.options.carouselMinimizedHeight - this.carouselContainer.normalHeight;
			this.carouselContainer.start({
				'opacity': this.options.carouselMinimizedOpacity,
				'top': targetTop
			}).chain(function() {
				this.carouselActive = false;
				this.carouselWrapper.scroller.stop();
				this.fireEvent('onCarouselHidden');
				this.carouselContainer.options.onComplete = null;
			}.bind(this));
		}
		catch (err) 
		{
			alert ("parth100:" + err.message); 
		}
	},
	constructThumbnails: function () {
		try {
			element = this.carouselInner;
			for(i=0;i<this.galleryData.length;i++)
			{
				var currentImg = new Fx.Morph(new Element ('div').addClass("thumbnail").setStyles({
						backgroundImage: "url('" + this.galleryData[i].thumbnail + "')",
						backgroundPosition: "center center",
						backgroundRepeat: 'no-repeat',
						marginLeft: this.options.thumbSpacing + "px",
						width: this.options.thumbWidth + "px",
						height: this.options.thumbHeight + "px"
					}).inject(element, 'bottom'), {duration: 200}).start({
						'opacity': this.options.thumbIdleOpacity
					});
				currentImg.element.addEvents({
					'mouseover': function (myself) {
						myself.cancel();
						myself.start({'opacity': 0.99});
						if (this.options.showCarouselLabel)
							$(this.carouselLabel).set('html', '<span class="number">' + (myself.relatedImage.number + 1) + "/" + this.maxIter + ":</span> " + myself.relatedImage.title);
					}.pass(currentImg, this),
					'mouseout': function (myself) {
						myself.cancel();
						myself.start({'opacity': this.options.thumbIdleOpacity});
					}.pass(currentImg, this),
					'click': function (myself) {
						this.goTo(myself.relatedImage.number);
						if (this.options.thumbCloseCarousel&&(!this.options.useExternalCarousel))
							this.hideCarousel();
					}.pass(currentImg, this)
				});
				
				currentImg.relatedImage = this.galleryData[i];
				this.thumbnailElements[parseInt(i)] = currentImg;
			}
		}
		catch (err) 
		{
			alert ("parth105:" + err.message); 
		}
	},
	log: function(value) {
		try {
			if(console.log)
				console.log(value);
		}
		catch (err) 
		{
			alert ("parth110:" + err.message); 
		}
	},
	preloadThumbnails: function() {
		try {
			var thumbnails = [];
			for(i=0;i<this.galleryData.length;i++)
			{
				thumbnails[parseInt(i)] = this.galleryData[i].thumbnail;
			}
			this.thumbnailPreloader = new Preloader();
			if (!this.options.useExternalCarousel)
				this.thumbnailPreloader.addEvent('onComplete', function() {
					var textShowCarousel = formatString(this.options.textShowCarousel, this.currentIter+1, this.maxIter);
					this.carouselBtn.set('html', textShowCarousel).setProperty('title', textShowCarousel);
				}.bind(this));
			this.thumbnailPreloader.load(thumbnails);
		}
		catch (err) 
		{
			alert ("parth110:" + err.message); 
		}
	},
	clearThumbnailsHighlights: function() {
		try {
			for(i=0;i<this.galleryData.length;i++)
			{
				this.thumbnailElements[i].cancel();
				this.thumbnailElements[i].start(0.2);
			}
		}
		catch (err) 
		{
			alert ("parth115:" + err.message); 
		}
	},
	changeThumbnailsSize: function(width, height)	{
		try {
			for(i=0;i<this.galleryData.length;i++)
			{
				this.thumbnailElements[i].cancel();
				this.thumbnailElements[i].element.setStyles({
					'width': width + "px",
					'height': height + "px"
				});
			}
		}
		catch (err) 
		{
			alert ("parth120:" + err.message); 
		}
	},
	centerCarouselOn: function(num) {
		try {
			if (!this.carouselWallMode)
			{
				var carouselElement = this.thumbnailElements[num];
				var position = carouselElement.element.offsetLeft + (carouselElement.element.offsetWidth / 2);
				var carouselWidth = this.carouselWrapper.element.offsetWidth;
				var carouselInnerWidth = this.carouselInner.offsetWidth;
				var diffWidth = carouselWidth / 2;
				var scrollPos = position-diffWidth;
				this.carouselWrapper.elementScroller.start(scrollPos,0);
			}
		}
		catch (err) 
		{
			alert ("parth125:" + err.message); 
		}
	},
	initInfoSlideshow: function() {
		try {
			/*if (this.slideInfoZone.element)
				this.slideInfoZone.element.remove();*/
			this.slideInfoZone = new Fx.Morph(new Element('div').addClass('slideInfoZone').inject($(this.galleryElement, 'bottom'))).set({'opacity':0});
			var slideInfoZoneTitle = new Element('h2').inject(this.slideInfoZone.element, 'bottom');
			var slideInfoZoneDescription = new Element('p').inject(this.slideInfoZone.element, 'bottom');
			this.slideInfoZone.normalHeight = this.slideInfoZone.element.offsetHeight;
			this.slideInfoZone.element.setStyle('opacity',0);
		}
		catch (err) 
		{
			alert ("parth130:" + err.message); 
		}
	},
	changeInfoSlideShow: function()
	{
		try {
			this.hideInfoSlideShow.delay(10, this);
			this.showInfoSlideShow.delay(500, this);
		}
		catch (err) 
		{
			alert ("parth135:" + err.message); 
		}
	},
	showInfoSlideShow: function() {
		try {
			this.fireEvent('onShowInfopane');
			this.slideInfoZone.cancel();
			element = this.slideInfoZone.element;
			element.getElement('h2').set('html', this.galleryData[this.currentIter].title);
			element.getElement('p').set('html', this.galleryData[this.currentIter].description);
			if(this.options.slideInfoZoneSlide)
				this.slideInfoZone.start({'opacity': [0, this.options.slideInfoZoneOpacity], 'height': [0, this.slideInfoZone.normalHeight]});
			else
				this.slideInfoZone.start({'opacity': [0, this.options.slideInfoZoneOpacity]});
			if (this.options.showCarousel)
				this.slideInfoZone.chain(this.centerCarouselOn.pass(this.currentIter, this));
			return this.slideInfoZone;
		}
		catch (err) 
		{
			alert ("parth140:" + err.message); 
			return {x: 0, y: 0};
		}
	},
	hideInfoSlideShow: function() {
		try {
			this.fireEvent('onHideInfopane');
			this.slideInfoZone.cancel();
			if(this.options.slideInfoZoneSlide)
				this.slideInfoZone.start({'opacity': 0, 'height': 0});
			else
				this.slideInfoZone.start({'opacity': 0});
		}
		catch (err) 
		{
			alert ("parth145:" + err.message); 
		}
		return this.slideInfoZone;
	},
	makeLink: function(num) {
		try {
			this.currentLink.setProperties({
				href: this.galleryData[num].link,
				title: this.galleryData[num].linkTitle
			})
			if (!((this.options.embedLinks) && (!this.options.showArrows) && (!this.options.showCarousel)))
				this.currentLink.setStyle('display', 'block');
		}
		catch (err) 
		{
			alert ("parth150:" + err.message); 
		}
	},
	clearLink: function() {
		try {
			this.currentLink.setProperties({href: '', title: ''});
			if (!((this.options.embedLinks) && (!this.options.showArrows) && (!this.options.showCarousel)))
				this.currentLink.setStyle('display', 'none');
		}
		catch (err) 
		{
			alert ("parth155:" + err.message); 
		}
	},
	makeReMooz: function() {
		try {
			this.currentLink.setProperties({
				href: '#'
			});
			this.currentLink.setStyles({
				'display': 'block'
			});
			
			this.galleryElements[this.currentIter].element.set('title', this.galleryData[this.currentIter].title + ' :: ' + this.galleryData[this.currentIter].description);
			this.ReMooz = new ReMooz(this.galleryElements[this.currentIter].element, {
				link: this.galleryData[this.currentIter].link,
				shadow: false,
				dragging: false,
				addClick: false,
				resizeOpacity: 1
			});
			var img = this.galleryElements[this.currentIter];
			var coords = img.element.getCoordinates();
			delete coords.right;
			delete coords.bottom;
			
			widthDiff = coords.width - img.width;
			heightDiff = coords.height - img.height;
			
			coords.width = img.width;
			coords.height = img.height;
			
			coords.left += Math.ceil(widthDiff/2)+1;
			coords.top += Math.ceil(heightDiff/2)+1;
			
			this.ReMooz.getOriginCoordinates = function(coords) {
				return coords;
			}.bind(this, coords);
			this.currentLink.onclick = function () {
				this.ReMooz.open.bind(this.ReMooz)();
				return false;
			}.bind(this);
		}
		catch (err) 
		{
			alert ("parth160:" + err.message); 
		}
	},
	/* To change the gallery data, those two functions : */
	flushGallery: function() {
		try {
			this.galleryElements.each(function(myFx) {
				myFx.element.dispose();
				myFx = myFx.element = null;
			});
			this.galleryElements = [];
		}
		catch (err) 
		{
			alert ("parth163:" + err.message); 
		}
	},
	changeData: function(data) {
		try {
			this.galleryData = data;
			this.clearTimer();
			this.flushGallery();
			if (this.options.showCarousel) this.flushCarousel();
			this.constructElements();
			if (this.options.showCarousel) this.fillCarousel();
			if (this.options.showInfopane) this.hideInfoSlideShow();
			this.galleryInit=1;
			this.lastIter=0;
			this.currentIter=0;
			this.doSlideShow(1);
		}
		catch (err) 
		{
			alert ("parth165:" + err.message); 
		}
	},
	/* Plugins: HistoryManager */
	initHistory: function() {
		try {
			this.fireEvent('onHistoryInit');
			this.historyKey = this.galleryElement.id + '-picture';
			if (this.options.customHistoryKey)
				this.historyKey = this.options.customHistoryKey;
			
			this.history = new History.Route({
				defaults: [1],
				pattern: this.historyKey + '\\((\\d+)\\)',
				generate: function(values) {
					return [this.historyKey, '(', values[0], ')'].join('')
				}.bind(this),
				onMatch: function(values, defaults) {
					if (parseInt(values[0])-1 < this.maxIter)
						this.goTo(parseInt(values[0])-1);
				}.bind(this)
			});
			this.addEvent('onChanged', function(){
				this.history.setValue(0, this.currentIter+1);
				this.history.defaults=[this.currentIter+1];
			}.bind(this));
			this.fireEvent('onHistoryInited');
		}
		catch (err)
		{
			alert ("parth165:" + err.message);
		}
	}
};
gallery = new Class(gallery);

gallery.Transitions = new Hash ({
	fade: function(oldFx, newFx, oldPos, newPos){
		try {
			oldFx.options.transition = newFx.options.transition = Fx.Transitions.linear;
			oldFx.options.duration = newFx.options.duration = this.options.fadeDuration;
			if (newPos > oldPos) newFx.start({opacity: 1});
			else
			{
				newFx.set({opacity: 1});
				oldFx.start({opacity: 0});
			}
		}
		catch (err) 
		{
			alert ("parth170:" + err.message); 
		}
	},
	crossfade: function(oldFx, newFx, oldPos, newPos){
		try {
			oldFx.options.transition = newFx.options.transition = Fx.Transitions.linear;
			oldFx.options.duration = newFx.options.duration = this.options.fadeDuration;
			newFx.start({opacity: 1});
			oldFx.start({opacity: 0});
		}
		catch (err) 
		{
			alert ("parth175:" + err.message); 
		}
	},
	fadebg: function(oldFx, newFx, oldPos, newPos){
		try {
			oldFx.options.transition = newFx.options.transition = Fx.Transitions.linear;
			oldFx.options.duration = newFx.options.duration = this.options.fadeDuration / 2;
			oldFx.start({opacity: 0}).chain(newFx.start.pass([{opacity: 1}], newFx));
		}
		catch (err) 
		{
			alert ("parth180:" + err.message); 
		}
	}
});

/* All code copyright 2007 Jonathan Schemoul */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Follows: Preloader (class)
 * Simple class for preloading images with support for progress reporting
 * Copyright 2007 Tomocchino.
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

var Preloader = new Class({
  
  Implements: [Events, Options],

  options: {
    root        : '',
    period      : 100
  },
  
  initialize: function(options){
	try {
		this.setOptions(options);
	}
	catch (err) 
	{
		alert ("parth185:" + err.message); 
	}
 },
  
  load: function(sources) {
	try {
		this.index = 0;
		this.images = [];
		this.sources = this.temps = sources;
		this.total = this. sources.length;
		
		this.fireEvent('onStart', [this.index, this.total]);
		this.timer = this.progress.periodical(this.options.period, this);
		
		this.sources.each(function(source, index){
		  this.images[index] = new Asset.image(this.options.root + source, {
			'onload'  : function(){ this.index++; if(this.images[index]) this.fireEvent('onLoad', [this.images[index], index, source]); }.bind(this),
			'onerror' : function(){ this.index++; this.fireEvent('onError', [this.images.splice(index, 1), index, source]); }.bind(this),
			'onabort' : function(){ this.index++; this.fireEvent('onError', [this.images.splice(index, 1), index, source]); }.bind(this)
		  });
		}, this);
	}
	catch (err) 
	{
		alert ("parth190:" + err.message); 
	}
  },
  
  progress: function() {
	try {
		this.fireEvent('onProgress', [Math.min(this.index, this.total), this.total]);
		if(this.index >= this.total) 
			this.complete();
	}
	catch (err) 
	{
		alert ("parth200:" + err.message); 
		return {x: 0, y: 0};
	}
  },
  
  complete: function(){
	try {
		// $clear => use the native clearTimeout when using fn.delay, use clearInterval when using fn.periodical.
		clearTimeout(this.timer);
		this.fireEvent('onComplete', [this.images]);
	}
	catch (err) 
	{
		alert ("parth205:" + err.message); 
	}
  },
  
  cancel: function(){
	try {
		clearInterval(this.timer);
	}
	catch (err) 
	{
		alert ("parth210:" + err.message); 
	}
  }  
});

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Follows: formatString (function)
 * Original name: Yahoo.Tools.printf
 * Copyright Yahoo.
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

function formatString() {
/* Mootools 1.4.5
Function: $arguments: This method has been deprecated and will have no equivalent in MooTools 1.3.
If you really need this function you can implement it like so:
Example:
var $arguments = function(i){
    return function(){
        return arguments[i];
    };
};
-> 18.01.2015 not fixed !!
*/
	var oStr = arguments[0];
	try {
		var num = arguments.length;
		for (var i = 1; i < num; i++) {
			var pattern = "\\{" + (i-1) + "\\}"; 
			var re = new RegExp(pattern, "g");
			oStr = oStr.replace(re, arguments[i]);
		}
	}
	catch (err) 
	{
		alert ("parth215:" + err.message); 
	}
	return oStr; 
}