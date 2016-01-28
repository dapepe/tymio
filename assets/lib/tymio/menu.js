/**
 * Initialize the menus
 */
window.addEvent('domready', function() {
	var GlobalActiveMenu;
	function toggleMenu(menu) {
		if (menu != null)
			menu.toggleClass('open');
		
		if (GlobalActiveMenu != null && GlobalActiveMenu != menu)
			GlobalActiveMenu.removeClass('open');
		
		GlobalActiveMenu = menu;
	}
	
	document.id(document.body).addEvent('click', function(e) {
		if (!e.target.hasClass('dropdown-toggle'))
			toggleMenu();
	});
	
	$$('.dropdown-toggle').each(function(dropdown) {
		// var menu = dropdown.getNext('.dropdown-menu');
		var menu = dropdown.getParent();
		dropdown.addEvent('click', function() {
			toggleMenu(menu);
		});
	});
});