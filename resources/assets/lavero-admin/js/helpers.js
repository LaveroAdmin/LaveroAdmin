/*--------------------------------------------------*/
/* visual */
/*--------------------------------------------------*/

	var show = function (list,display) {
		if (typeof(display) === 'undefined'){
			display = "block";
		}
		if (!isNodeList(list)){
			var list = [list];
		}
		list.forEach(elm => {
			showElm(elm,display);
		});
	};
	function showElm(elm,display){
		if(elm.tagName == "TR"){
			elm.style.display = "table-row";
		}else{
			elm.style.display = display;
		}
	}

	var hide = function (list) {
		if (!isNodeList(list)){
			var list = [list];
			isNodeList(list)
		}
		list.forEach(elm => {
			elm.style.display = 'none';
		});
	};

	var toggle = function (list) {
		if (!isNodeList(list)){
			var list = [list];
		}
		list.forEach(elm => {
			let calculatedStyle = window.getComputedStyle(elm).display;
			if (calculatedStyle === 'block' || calculatedStyle === 'flex' || calculatedStyle === 'table-row') {
				elm.style.display = 'none';
				return;
			}
			showElm(elm);
		});
	};

/*--------------------------------------------------*/
/* lang function */
/*--------------------------------------------------*/

	var __ = function(trans_string){
		return admin_lang_arr[trans_string];
	}

	var trans = __;

/*--------------------------------------------------*/
/* array / object helpers */
/*--------------------------------------------------*/

	var merge_default = function(defaults,object, ...rest){
		return Object.assign({}, defaults, object, ...rest);
	}

	var arr_remove = function(arr,elem) {
		var indexElement = arr.findIndex(el => el == elem);
		if (indexElement != -1)
		  arr.splice(indexElement, 1);
		return arr;
	};

	var arr_includes = function(arr,elem) {
		var indexElement = arr.findIndex(el => el == elem);
		return (indexElement != -1)
	};

/*--------------------------------------------------*/
/* event Handlers  */
/*--------------------------------------------------*/

	function delegate(selector, handler) {

		return function(event) {
		  var targ = event.target;
		  do {
			if (targ.matches(selector)) {
			  handler.call(targ, event);
			}
		  } while ((targ = targ.parentNode) && targ != event.currentTarget);
		}
	  }

/*--------------------------------------------------*/
/* html elements */
/*--------------------------------------------------*/

	function getOuterHeigt(el) {
		// Get the DOM Node if you pass in a string
		el = (typeof el === 'string') ? document.querySelector(el) : el;

		var styles = window.getComputedStyle(el);
		var margin = parseFloat(styles['marginTop']) +
					 parseFloat(styles['marginBottom']);

		return Math.ceil(el.offsetHeight + margin);
	}

	function isNodeList(nodes) {
		var stringRepr = Object.prototype.toString.call(nodes);

		return typeof nodes === 'object' &&
			/^\[object (HTMLCollection|NodeList|Object)\]$/.test(stringRepr) &&
			(typeof nodes.length === 'number') &&
			(nodes.length === 0 || (typeof nodes[0] === "object" && nodes[0].nodeType > 0));
	}

	/**
	 * @param {String} HTML representing a single element
	 * @return {Element}
	 */
	function htmlToElement(html) {
		var template = document.createElement('template');
		html = html.trim(); // Never return a text node of whitespace as the result
		template.innerHTML = html;
		return template.content.firstChild;
	}

	/**
	 * @param {String} HTML representing any number of sibling elements
	 * @return {NodeList}
	 */
	function htmlToElements(html) {
		var template = document.createElement('template');
		template.innerHTML = html;
		return template.content.childNodes;
	}

/*--------------------------------------------------*/
/* Dialog helpers (SweetAlert2)                     */
/*--------------------------------------------------*/

	window.admin = window.admin || {};
	var admin = window.admin;

	admin.alert = function (message, title, icon) {
		if (typeof Swal !== 'undefined') {
			return Swal.fire({
				title: title || '',
				text: message,
				icon: icon || 'info',
				confirmButtonColor: '#12161f',
				confirmButtonText: typeof __ === 'function' && __('confirm') ? __('confirm') : 'OK'
			});
		} else {
			window._nativeAlert ? window._nativeAlert(message) : alert(message);
			return Promise.resolve();
		}
	};

	admin.confirm = function (message, title, callback) {
		if (typeof title === 'function') {
			callback = title;
			title = '';
		}
		if (typeof Swal !== 'undefined') {
			return Swal.fire({
				title: title || message,
				text: title ? message : '',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#dc3545',
				cancelButtonColor: '#6c757d',
				confirmButtonText: typeof __ === 'function' && __('confirm') ? __('confirm') : 'OK',
				cancelButtonText: typeof __ === 'function' && __('cancel') ? __('cancel') : 'Cancel'
			}).then(function (result) {
				if (result.isConfirmed || result.value) {
					if (typeof callback === 'function') {
						callback();
					}
				}
			});
		} else {
			if (window._nativeConfirm ? window._nativeConfirm(message) : confirm(message)) {
				if (typeof callback === 'function') callback();
			}
			return Promise.resolve();
		}
	};

	// Override standard window.alert with SweetAlert2 modal dialog
	if (typeof window !== 'undefined') {
		window._nativeAlert = window.alert;
		window.alert = function (message) {
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					text: message,
					icon: 'info',
					confirmButtonColor: '#12161f',
					confirmButtonText: 'OK'
				});
			} else if (window._nativeAlert) {
				window._nativeAlert(message);
			}
		};
	}

