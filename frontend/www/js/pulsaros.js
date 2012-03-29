/**
 * 
 * 
 * Description
 * 
 * @license		GNU General Public License
 * @author		Thomas Brandstetter
 * @link		http://www.pulsaros.com
 * @email		admin@pulsaros.com
 * 
 * Copyright (c) 2009-2011
 */
 
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * THIS SOFTWARE AND DOCUMENTATION IS PROVIDED "AS IS," AND COPYRIGHT
 * HOLDERS MAKE NO REPRESENTATIONS OR WARRANTIES, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO, WARRANTIES OF MERCHANTABILITY OR
 * FITNESS FOR ANY PARTICULAR PURPOSE OR THAT THE USE OF THE SOFTWARE
 * OR DOCUMENTATION WILL NOT INFRINGE ANY THIRD PARTY PATENTS,
 * COPYRIGHTS, TRADEMARKS OR OTHER RIGHTS.COPYRIGHT HOLDERS WILL NOT
 * BE LIABLE FOR ANY DIRECT, INDIRECT, SPECIAL OR CONSEQUENTIAL
 * DAMAGES ARISING OUT OF ANY USE OF THE SOFTWARE OR DOCUMENTATION.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://gnu.org/licenses/>.
 */

// Check Form elements
window.addEvent('domready', function() {
	//slide form
	$$('.formtoggle').each(function(item) {
		var slide = new Fx.Slide(item.getElement( '.formcontainer' ), { duration: 500 });
		slide.hide();
		item.getElement( '.addform' ).addEvent('click', function(e) {
			// we only need to stop the event on links
			if ( item.getElement('.addform').nodeName === "BUTTON") {
				e.stop();
			}
			slide.toggle();
		});
	});
});

// Validate values against the PulsarOS configuration
function validateContent(value, type, div) {
	if (div!=null) {
		var Resultid = 'result'+div;
		var Validateid = 'validate'+div;
	}
	else {
		var Resultid = 'result';
		var Validateid = 'validate';
	}
	var log = $(Validateid).empty().addClass('ajax-loading');
	var myRequest = new Request({
		method: 'post', 
		url: 'index.php?admin/validate',
		data: {'id' : type, 'data' : value},
		onComplete: function(response) {
			var status = JSON.decode(response);
			$(Validateid).removeClass('ajax-loading');
			if (status.code == '0') {
				$(Validateid).set('html', status.message);
				 $(Resultid).getElement('input[type=image]').set('disabled',true); 
        	}
		}
	}).send();
};

// Check if a share has a connection left
function validateConnection(volume, pool) {
	var Resultid = 'result'+volume;
	var Validateid = 'validate'+volume;
	var Formid = 'form'+volume;
	//Prevent form from submitting
	$(Formid).addEvent('submit', function(e) {
		e.stop();
	});
	var log = $(Validateid).empty().addClass('ajax-loading');
	var myRequest = new Request({
		method: 'post', 
		url: 'index.php?volumes/chk',
		data: {'volume' : volume, 'pool' : pool},
		onComplete: function(response) {
			var status = JSON.decode(response);
			$(Validateid).removeClass('ajax-loading');
			if (status.code == '0') {
				// Print error
				$(Validateid).set('html', status.message);
				$(Resultid).getElement('input[type=image]').set('disabled',true); 
        	}
        	else {
        		// Submit form if no error is catched
        		 $(Formid).submit();
        	}
		}
	}).send();
};

function clearContent(div) {
	if (div!=null) {
		var Resultid = 'result'+div;
		var Validateid = 'validate'+div;
	}
	else {
		var Resultid = 'result';
		var Validateid = 'validate';
	}
	var log = $(Validateid).empty();
	$(Resultid).getElement('input[type=image]').set('disabled',false);
}

// Validate Form with FormCheck and submit it with AJAX Request
function formValidateAjax(form, redirect) {
	if (form!=null) {
		var Formid = 'Form'+form;
		var Resultid = 'result'+form;
	}
	else {
		var Formid = 'Form';
		var Resultid = 'result';
	}
	var Validate = new FormCheck(Formid, {
		submit: false,
		onValidateSuccess: function() {
			var log = $(Resultid).empty().addClass('ajax-loading');
			$(Formid).set('send', {
				onComplete: function(response) {
					var status = JSON.decode(response);
					$(Resultid).removeClass('ajax-loading');
					if (redirect!=null) {
						window.location = redirect;
					}
					else {
						$(Resultid).set('html', status.message);
					}		
				}
			});
			$(Formid).send();
		}
	});                                                         
};

//Validate Form with FormCheck and submit it without AJAX
function formValidate(val) {
	if (val!=null) {
		var Validate = new FormCheck('Form'+val);
	}
	else {
		var Validate = new FormCheck('Form');
	}
};

// Submit Form with AJAX Request
function formAjax(form, redirect) {
	if (form!=null) {
		var Formid = 'Form'+form;
		var Resultid = 'result'+form;
	}
	else {
		var Formid = 'Form';
		var Resultid = 'result';
	}
	var log = $(Resultid).empty().addClass('ajax-loading');
	$(Formid).set('send', {
		onComplete: function(response) {
			var status = JSON.decode(response);
			$(Resultid).removeClass('ajax-loading');
			if (redirect!=null) {
				window.location = redirect;
			}
			else {
				$(Resultid).set('html', status.message);
			}		
		}
	});
	$(Formid).send();                                                         
};

// Refresh sync state
function refreshContent(div) {
	if (div!=null) {
		var Responseid = 'response'+div;
	}
	else {
		var Responseid = 'response';
	}
	$(Responseid).set('load', {'autoCancel' : 'true', 'method' : 'get' } );
	$(Responseid).load('index.php?storage/syncstate/'+div);
	
	var refresh = function() {
		//noCache = '?a=' + $time() + $random(0, 100);
		$(Responseid).load('index.php?storage/syncstate/'+div);
	}
	
	refresh.periodical(4000);
}

// Slider function for volume sizing
function createSlider(div, poolsize) {
	if (poolsize > 0) {
	var el = $('vol'+div),
		value1 = $('size'+div),
		value2 = $('newsize'+div),
		size = 1;

    // Create the new slider instance
    var mySlider = new Slider(el, el.getElement('.panel'), {
    	steps: poolsize,
       	range: [0, poolsize],
       	onChange: function(size) {
           	if (size >= 1000000 ) {
       			value2.set('html', "<div class='newsize'></div>" + "<div class='newspace'>" + Math.round(size/1000000*100)/100 + "TB</div>");
       		}
       		else if (size >= 1000) {
       			value2.set('html', "<div class='newsize'></div>" + "<div class='newspace'>" + Math.round(size/1000*100)/100 + "GB</div>");
       		}
       		else if (size == 0 ) {
       			value2.set('html', "<div class='newsize'></div>" + "<div class='newspace'>" + 1 + "MB</div>");
       			size = 1;
       		}
           	else {
           		value2.set('html', "<div class='newsize'></div>" + "<div class='newspace'>" + size + "MB</div>");
           	}
           	value1.set('value', size);
       	}
    });
    mySlider.set(1);
    }
    else {
    	$('formcontainer'+div).set('html', "<p class='center'>No more diskspace available</p>");
    }
}

// Slider function for volume resizing
function changeSlider(div, remaining, volsize, usedsize, maxsize) {
	var el = $('vol'+div),
		value1 = $('newsize'+div),
		value2 = $('size'+div),
		sizetype = $('sizetype'+div),
		volsize = parseInt(volsize),
		remaining = parseInt(remaining),
		usedsize = parseInt(usedsize),
		maxsize = parseInt(maxsize);

    // Create the new slider instance
    var mySlider = new Slider(el, el.getElement('.panel'), {
       	steps: maxsize-usedsize,
       	range: [usedsize, maxsize],
       	onChange: function(size) {
       		if (size >= 1000000) {
       			value2.set('html', "<div class='newsize'>New size: </div>" + 
       							   "<div class='newspace'>" + Math.round(size/1000000*100)/100 + 
       							   "TB</div>" + "<input type='hidden' name='minsize' value='" + usedsize + "' />");
       		}
       		else if (size >= 1000) {
       			value2.set('html', "<div class='newsize'>New size: </div>" + 
       							   "<div class='newspace'>" + Math.round(size/1000*100)/100 +
       							   "GB</div>" + "<input type='hidden' name='minsize' value='" + usedsize + "' />");
       		}
           	else {
           		value2.set('html', "<div class='newsize'>New size: </div>" + "<div class='newspace'>" +
           		 				    size + "MB</div>" + "<input type='hidden' name='minsize' value='" + usedsize + "' />");
           	}
           	value1.set('value', size);
       	}
    });
    mySlider.set(volsize);
}

// Grey out section (if status == "checked=checked" then div is hidden else div will be shown)
function slideDiv(div, status) {
	var el = $(div);

	var slide = new Fx.Slide(el.getElement('.formcontainer'), { duration: 500 });
		if ( status == "checked=checked") {
			slide.hide();
		}
		el.getElement('.addform').addEvent('click', function(e) {
			// we only need to stop the event on links and inputs
			if ( el.getElement('.addform').nodeName === "A") {
				e.stop();
			}
			else if ( el.getElement('.addform').nodeName === "INPUT") {
				e.stop();
			}
			slide.toggle();
		});
}