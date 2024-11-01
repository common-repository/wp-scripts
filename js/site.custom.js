// site.custom example
/* 
For MooTools 
*/
/*
function mySidebarLoader() {
	$$('.collapse-body').each(function(el){
		var tid = el.id+'_tgl';
		tid = $(tid);
		tid.onclick = function(){Spoiler.Collapse(el, 'blind', tid, {duration: 400}); return false;};	
		tid.addClassName('effcollapse');
		if(el.hasClassName('hide_first')) {
			Spoiler.Collapse(el, 'blind', tid, {duration: 600});
		}
	}, this);
}
window.addEvent('load', mySidebarLoader);
*/
/* 
For Prototype 
*/
/*
function mySidebarLoader() {
	$$('.collapse-body').each(function(el) {
		var tgl = el.id + '_tgl';
		tgl = $(tgl);
		tgl.onclick = function(){
			new Spoiler.Collapse(el, 'blind', tgl, {duration: 0.4});
			return false;
		}
		Element.addClassName(tgl, 'effcollapse');
		if(Element.hasClassName(el, 'hide_first')) {
			new Spoiler.Collapse(el, 'blind', tgl, {duration: 0.6});
		}
	});
}

Event.observe(window, 'load', mySidebarLoader);
*/