(function ($){
	"use strict";
	
	var $demoWrap = $('.demo-wrap'),
		$demoFram = $('.demo-frame-block'),
		$demoSelectBtn = $('#demo_select'),
		$viewports = $('.viewports'),
		$purchaseBtn = $('.buy-btn'),
		$closeBtn = $('.close-frame-btn'),
		$demoCarousel = $('.demo-switcher-carousel-block .carousel-block-inner'),
		$pageSelectBtn = $('#page_select'),
		$pageSelectBtnInner = $('#page_select span'),
		$pageList = $('#page_list'),
		
		hasPushState = !!(window.history && history.pushState);
	
	
	function urlParameters(arg){
		var $value = window.location.search.substring(1),
			$singleValue = $value.split("&");
			for(var $i=0;$i<$singleValue.length;$i++){
				var $portion = $singleValue[$i].split('=');
				if($portion[0] === arg){
					return $portion[1];
				}
			}
			return false;
	}
	
	var $currentItem =  urlParameters('item');
	
	if (!($currentItem in $items) || $currentItem === '') {
		$currentItem = $defaultItem;
	}
	function viewportBtns(){
		if('undefined' !== typeof $items[$currentItem].responsive && $items[$currentItem].responsive === false ){
			$viewports.addClass('hidden');
		}else{
			$viewports.removeClass('hidden');
		}
	}
	
	$closeBtn.on('click',function(){
		event.preventDefault();
		if($currentItem in $items){
			top.location.href = $items[$currentItem].url;
		}
		return false;
	});
	
	$purchaseBtn.on('click',function(){
		event.preventDefault();
		if($currentItem in $items){
			top.location.href = $items[$currentItem].purchase;
		}
		return false;
	});
	
	$demoSelectBtn.on('click', function (event) {
		  event.preventDefault();
		  $(this).toggleClass('active');
		  $('.demo-switcher-carousel-block').toggleClass('active');
	});
	
	$pageSelectBtn.on('click', function (event) {
		  event.preventDefault();
		  $(this).toggleClass('active');
		  $('.page-holder').toggleClass('active');
	});
	
	$viewports.each(function () {
		var $this = $(this);

		$this.on('click', 'li > a', function (event) {
			event.preventDefault();
			var $this = $(this),
			$size = $this.data('size');

		if ($size) {
			$demoWrap.css('width', $size);
			$this.parent('li').addClass('active').siblings('li').removeClass('active');
		}
	  });
	});
	
	$.each($items, function(key, object) {
		var $tooltip;
		if ('tooltip' in object) {
			$tooltip = 'title="' + object.tooltip.replace( /"/g, '\'' ) + '"';
		}
		var $tagFlag;
		if(object.tag == 'Wordpress'){ 
			$tagFlag  = '<i class="fa fa-wordpress" aria-hidden="true"></i>';
		}else{
			$tagFlag = '<i class="fa fa-html5" aria-hidden="true"></i>';
		}
		$demoCarousel.append(
			'<a class="item item-block" data-id="' + key + '" ' + $tooltip + '>' +
			'<div class="top-title-block">'+
			'<span class="title">' + object.name + '</span>' +
			'<span class="badge">' + $tagFlag + '</span>' +
			'</div>'+
			'<div class="item-thumb">'+
			'<img src="' + object.img + '" alt="' + object.name + '">' +
			'</div>'+
			'</a>'
		);
	});	
	function itemDemo(){
		if(typeof $items[$currentItem] !='undefined'){
			$demoFram.attr('src', $items[$currentItem].url);
			document.title = $items[$currentItem].name + ' ' + $items[$currentItem].tag + ' - ' + $('.logo-block').attr('title');
			$demoSelectBtn.html($items[$currentItem].name);
			$pageSelectBtnInner.html($items[$currentItem].name +' pages');
			viewportBtns();
		}
	}
	itemDemo();
	function itemDemoDetails($pageUrl){
		$demoFram.attr('src', $pageUrl);
	}
	$demoCarousel.owlCarousel({
		nav: true,
        navText: ['<i class="fa fa-angle-left" aria-hidden="true"></i>','<i class="fa fa-angle-right" aria-hidden="true"></i>'],
        dots: false,
        loop: true,
        autoplay: false,
        responsiveClass: true,
		items: 4,
		margin: 20,
		slideSpeed: 500,
        responsive: {
            0: {
                items: 3
            },
            1200: {
                items: 4
            }
        }
	});
	if(typeof $items[$currentItem] !='undefined'){
		var $tempObject = $items[$currentItem];
		for(var i=0;i<$tempObject.pages.title.length;i++){
			var $tempFlag = $tempObject.pages.title[i];
			var $tempLinks = $tempObject.pages.links[i];
			$pageList.append(
				'<li> <a href="' + $tempLinks + '">'+ $tempFlag.replace(/-/g,' ') +'</a></li>'
			);
		}
	}
	
	
	$(document).on('click','#page_list li a', function (e){
		var $pageUrl = $(this).attr('href');
		if (hasPushState) {
			itemDemoDetails($pageUrl);
			$demoWrap.css('width', '100%');
			$('.switcher-carousel').removeClass('active');
		}else{
			top.location.href = '?item=' + $currentItem;
		}
	});
	
	$demoCarousel.find('.item').on('click', function (){
		$('.demo-switcher-carousel-block').removeClass('active');
		$('.page-holder').removeClass('active');
		$('.page-select-block').removeClass('active');
		$demoSelectBtn.removeClass('active');
		$currentItem = $(this).data('id');
		$pageList.html("");
		if ($currentItem in $items){
			var $tempObject = $items[$currentItem];
			if('pages' in $tempObject){
				for(var i=0;i<$tempObject.pages.title.length;i++){
					var $tempFlag = $tempObject.pages.title[i];
					var $tempLinks = $tempObject.pages.links[i];
					$pageList.append(
						'<li> <a href="' + $tempLinks + '">'+ $tempFlag.replace(/-/g,' ') +'</a></li>'
					);
				}
			}
			if (hasPushState) {
				itemDemo();
				$demoWrap.css('width', '100%');
				$('.switcher-carousel').removeClass('active');

				history.pushState({id: $currentItem}, '', '?item=' + $currentItem);
			} else {
				top.location.href = '?item=' + $currentItem;
			}
		}
		return false;
	});	
}(jQuery));