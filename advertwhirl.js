// Mobile Sentience LLC - http://www.mobilesentience.com - oss@mobilesentience.com - @MobileSentience
function confirmDeleteSource($name){
	return confirm("Are you sure you wish to permanently remove " + $name + " from Ad Sources?");
}

// not animated collapse/expand
function togglePannelStatus(content){
	var expand = (content.style.display=="none");
	alert("toggling content for " + content.name)
	content.style.display = (expand ? "block" : "none");
	toggleChevronIcon(content);
}

// current animated collapsible panel content
var currentContent = null;

function togglePanelAnimatedStatus(panel, interval, step, field){
	// wait for another animated expand/collapse action to end
	var content = document.getElementsByName(panel + "_content")[0];
	if(currentContent==null && content != null){
		currentContent = content;
		var expand = (content.style.display=="none");
		if (expand)
			content.style.display = "block";
		
		var status = null
		if(field != null)
			status = document.getElementsByName(field)[0];
		if(status != null)
			status.value = expand?"block":"none";

		var max_height = content.offsetHeight;
		
		var step_height = step + (expand ? 0 : -max_height);
		toggleChevronIcon(panel);

		// schedule first animated collapse/expand event
		content.style.height = Math.abs(step_height) + "px";
		setTimeout("togglePanelAnimatingStatus(" + interval + "," + step + "," + max_height + "," + step_height + ")", interval);
	}
}

function togglePanelAnimatingStatus(interval, step, max_height, step_height){
	var step_height_abs = Math.abs(step_height);

	// schedule next animated collapse/expand event
	if (step_height_abs>=step && step_height_abs<=(max_height-step)){
		step_height += step;
		currentContent.style.height = Math.abs(step_height) + "px";
		setTimeout("togglePanelAnimatingStatus(" + interval + "," + step + "," + max_height + "," + step_height + ")", interval);
	}else { // animated expand/collapse done
		if (step_height_abs<step)
			currentContent.style.display = "none";
		currentContent.style.height = "";
		currentContent = null;
	}
}

// change chevron icon into either collapse or expand
function toggleChevronIcon(panel){
	var chevron = document.getElementsByName(panel + "_chevron")[0];
	var expand = (chevron.src.indexOf("expand.gif") > 0);
	chevron.src = chevron.src.split(expand ? "expand.gif" : "collapse.gif").join(expand ? "collapse.gif" : "expand.gif");
}

function toggleDisplayedRows(display, hide){
	var drow = document.getElementById(display);
	var hrow = document.getElementById(hide);

	hrow.style.display = 'none';
	drow.style.display = '';
}

// document.some_form
function SetActionAnchor(form, anchor) {   
   form.action += "#" + anchor; 
   
}

// disable and enable the settings for in content ad placement
function enableContentPlacementFields(type){
	var checked = document.getElementsByName('display-' + type + "-in-content")[0].checked;
	document.getElementsByName('display-' + type + "-maxads")[0].disabled = !checked;
	document.getElementsByName('display-' + type + "-every")[0].disabled = !checked;
	document.getElementsByName('display-' + type + "-offset")[0].disabled = !checked;
	document.getElementsByName('display-' + type + "-align")[0].disabled = !checked;
}
 
