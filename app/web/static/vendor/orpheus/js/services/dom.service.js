class DomService {
	
	filters = {
		"date": (value, format) => {
			value = moment(value);
			return value.format(format);
		},
		"truncate": (value, length) => {
			return value.substring(0, length);
		},
		"upper": value => {
			return value.toUpperCase();
		},
		"url_host": (value) => {
			if( !value ) {
				return "";
			}
			const location = getLocation(value);
			return location.host;
		},
	};
	
	getFormData($form) {
		return new FormData($form);
	};
	
	getFormObject($form) {
		const formData = this.getFormData($form);
		const object = {};
		const buildObject = function (data, keys, value) {
			let key = keys.shift();
			if( !keys.length ) {
				if( !key ) {
					// Last empty returns value
					return value;
				}
				// Lone key with no brackets
				data[key] = value;
				return data;
			}
			value = buildObject((data && data[key]) || null, keys, value);
			if( data === null ) {
				if( !key ) {
					// Empty key in middle of string means []
					return [value];
				}
				return {[key]: value};
			}
			data[key] = value;
			return data;
		};
		formData.forEach((value, name) => {
			buildObject(object, name.split(/[\[\]]{1,2}/), value);
		});
		return object;
	};
	
	detach($element) {
		if( !$element.parentElement ) {
			return false;
		}
		return $element.parentElement.removeChild($element);
	}
	
	castElement(fragment) {
		const template = document.createElement('template');
		template.innerHTML = fragment.trim();// Any whitespace will convert it to text
		return template.content.firstChild;
	}
	
	createElement(tag, className, attributes) {
		const $element = document.createElement(tag);
		if( className ) {
			$element.className = className;
		}
		if( attributes && typeof attributes === "object" ) {
			Object.entries(attributes).forEach(([key, value]) => {
				$element.setAttribute(key, value);
			});
		}
		return $element;
	}
	
	getViewportSize() {
		// https://stackoverflow.com/questions/1248081/how-to-get-the-browser-viewport-dimensions
		return {
			width: Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0),
			height: Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0),
		};
	}
	
	parseArguments(list) {
		// Remove the first level of quoting
		const length = list.length;
		const quotes = ["\"", "'"];
		const quoting = [];
		let token = "";
		let tokens = [];
		// Loop each char to wrap the whole string for a token
		for (let i = 0; i < length; i++) {
			const char = list[i];
			let feedToken = true;
			if( quotes.includes(char) ) {
				if( quoting[0] === char ) {
					// End opened quoting
					quoting.shift();
					feedToken = !!quoting.length;
				} else {
					// Start new quoting
					feedToken = !!quoting.length;
					quoting.unshift(char);
				}
			}
			if( char === " " && !quoting.length ) {
				// Separator encountered and not quoting
				feedToken = false;
				tokens.push(token);
				token = "";
			}
			if( feedToken ) {
				token += char;
			}
		}
		if( token ) {
			// End of string add the started token
			tokens.push(token);
		}
		return tokens;
	}
	
	resolveCondition(condition, data) {
		let conditionParts = this.parseArguments(this.renderTemplateString(condition, data));
		const invert = conditionParts[0] === "not";
		if( invert ) {
			conditionParts = conditionParts.shift();
		}
		let rawCondition;
		if( conditionParts.length > 2 ) {
			// Comparison operator with 3 tokens
			let [value1, operator, value2] = conditionParts;
			if( !["=", "==", "==="].includes(operator) ) {
				throw new Error(`Unknown operator "${operator}"`);
			}
			rawCondition = value1 === value2;
		} else {
			// Simple boolean property
			let [property] = conditionParts;
			rawCondition = Number(!!data[property]);
		}
		return invert ^ Number(!!rawCondition);
	}
	
	renderTemplateString(string, data) {
		// Clean contents
		let template = string.replace(/>\s+</ig, "><");
		
		// Resolve values in attributes
		// Deprecated for TWIG compatibility, double brackets
		template = template.replace(/\{\{ ?([^\}]+) ?\}\}/ig, (all, variable) => {
			return this.resolveValue(variable, data);
		});
		// Yes we want this one, simple brackets
		template = template.replace(/\{ ?([^\}]+) ?\}/ig, (all, variable) => {
			return this.resolveValue(variable, data);
		});
		// For url compatibility, url encoded brackets
		template = template.replace(/\%7B\%20([^\%]+)\%20\%7D/ig, (all, variable) => {
			return this.resolveValue(variable, data);
		});
		
		return template;
	}
	
	resolveValue(value, data) {
		// First identify the value
		const firstChar = value.charAt(0);
		if( firstChar === "\"" || firstChar === "'" ) {
			// Raw string
			value = value.slice(1, value.length - 1);
			return value;
		}
		let variable = value;
		let tokens = variable.split("|");
		// Calculate value
		const propertyTree = tokens.shift().trim().split(".");
		let resolvedValue = data || {};
		for (const property of propertyTree) {
			resolvedValue = resolvedValue[property];
			if( resolvedValue === undefined || resolvedValue === null ) {
				// Stop now
				break;
			}
		}
		value = resolvedValue || value;
		// Apply filters on value
		tokens.forEach(filterCall => {
			filterCall = filterCall.trim();
			// noinspection RegExpRedundantEscape
			const filterCallMatch = filterCall.match(/^([^\(]+)(?:\(([^\)]*)\))?$/);
			const filter = filterCallMatch[1];// Filter
			const filterArgList = filterCallMatch[2];// Filter arguments
			let filterArgs = [];
			if( filterArgList ) {
				filterArgs = filterArgList.split(/,\s?/).map(argValue => this.resolveValue(argValue, data));
			}
			// Add value as first argument
			filterArgs.unshift(value);
			
			const filterCallback = this.filters[filter];
			if( filterCallback ) {
				value = filterCallback(...filterArgs);
			} else {
				console.warn("Unknown filter " + filter);
			}
		});
		return value;
	}
	
	getElement($element) {
		return isString($element) ? document.querySelector($element) : $element;
	}
	
	fillForm($container, data, pattern = null) {
		if( !data || typeof data !== 'object' ) {
			throw "Parameter data must be an object";
		}
		$container = this.getElement($container);
		Object.entries(data).forEach(([key, value]) => {
			const name = pattern ? pattern.replace("%s", key) : key;
			$container.querySelectorAll("[name=\"" + name + "\"]")
				.forEach(($element) => {
					this.assignValue($element, value);
				});
		});
	};
	
	assignValue($element, value) {
		if( !($element instanceof Element) ) {
			throw "Parameter $element must be an Element (DOM)";
		}
		const elementTag = $element.tagName.toLowerCase();
		let changed = false;
		if( elementTag === "img" || elementTag === "iframe" ) {
			$element.setAttribute("src", value);
		} else if( elementTag === "a" ) {
			$element.setAttribute("href", value);
		} else if( this.isCheckbox($element) ) {
			if( $element.value.toLowerCase() !== "on" ) {
				// Not default browser value
				$element.checked = isArray(value) ? value.includes($element.value) : $element.value === value;
			} else {
				// + to convert to int, !! to convert to boolean
				$element.checked = !!+value;
			}
			changed = true;
		} else if( elementTag === "select" ) {
			const values = isArray(value) ? value : [value];
			// Create all missing options
			for (const selectedValue of values) {
				let $option = $element.querySelector("option[value=\"" + selectedValue + "\"]");
				// console.log("$option", $option, "for value", selectedValue, "in", $element);
				if( !$option ) {
					// Automatically create new options
					$option = this.createElement("option");
					$option.innerText = selectedValue;
					$option.value = selectedValue;
					$element.append($option);
				}
			}
			// Set selected property for all options (to remove previous selected)
			$element.querySelectorAll("option").forEach($option => {
				$option.selected = values.includes($option.value);
			});
			changed = true;
		} else if( this.isInput($element) ) {
			// Fix issue in some dynamic forms
			// input was filled but the change event not called
			// dispatchEvent adds compatibility with Stimulus (raw event listener)
			$element.value = value;
			changed = true;
		} else {
			// Simple html element
			$element.innerText = value || $element.dataset.emptyText;
		}
		
		if( changed ) {
			$element.dispatchEvent(new Event("change"));
		}
	}
	
	getSiblings($element, filter = null) {
		const filterFunction = typeof filter === "function" ? filter : null;
		const filterSelector = typeof filter === "string" ? filter : null;
		$element = this.getElement($element);
		return [...$element.parentNode.children].filter(($child) =>
			$child !== $element && (!filterFunction || filterFunction($child)) && (!filterSelector || $child.matches(filterSelector)),
		);
	}
	
	renderTemplateElement($template, data, prefix) {
		// Resolve conditional displays
		$template.querySelectorAll("[data-if]").forEach($element => {
			if( !this.resolveCondition($element.dataset.if, data) ) {
				$element.remove();
			}
		});
		// Resolve else displays
		$template.querySelectorAll("[data-else]").forEach($element => {
			if( $element.dataset.else === "siblings" ) {
				const conditionalSiblings = this.getSiblings($element, "[data-if]");
				if( conditionalSiblings.length ) {
					$element.remove();
				}
			} else {
				throw new Error("Unknown template data-else attribute value, supports only: \"siblings\"");
			}
		});
		// Fix image loading preventing
		$template.querySelectorAll("[data-src]").forEach($element => {
			$element.src = $element.dataset.src;
			delete $element.dataset.src;
		});
		// Fix link crawling
		$template.querySelectorAll("[data-href]").forEach($element => {
			$element.href = $element.dataset.href;
			delete $element.dataset.href;
		});
		// Resolve values in content
		if( prefix ) {
			this.fillForm($template, $data, this.getPrefixPattern(prefix));
		}
	}
	
	getPrefixPattern(prefix) {
		return prefix + "[%s]";
	}
	
	async loadTemplate(key, $target) {
		const response = await fetch("/api/template/" + key);
		if( !response.ok ) {
			throw new Error("Invalid response");
		}
		// const $target = document.querySelector(target);
		$target.innerHTML = await response.text();
		
		return $target;
	}
	
	global() {
		return $(window);
	}
	
	extractTemplate(template) {
		let isHtml = true;
		if( isJquery(template) ) {
			template = template[0];
		}
		if( isPureObject(template) && template.template !== undefined && template.isHtml !== undefined ) {
			return template;
		}
		if( isDomElement(template) ) {
			if( template.matches("template") ) {
				let content = template.innerHTML.trim();
				if( !content ) {
					content = template.innerText.trim();
					if( content ) {
						isHtml = false;
					}
				}
				if( !content ) {
					console.error("Template has no contents", template);
				}
				template = content;
			} else {
				template = template.outerHTML;
			}
		}
		return {template: template, isHtml: isHtml};
	}
	
	renderTemplate(template, data, options) {
		// TODO Require unit tests, for now, use Dev Composer page or test api dev page
		if( !template ) {
			throw new Error("Empty template");
		}
		if( !isObject(options) ) {
			options = {prefix: options};
		}
		options = Object.assign({
			prefix: null,
			wrap: false,
			immediate: true// Some lib does not handle async
		}, options);
		let {template: templateString, isHtml} = this.extractTemplate(template);
		let renderedTemplate = this.renderTemplateString(templateString, data);
		// Create jquery object preventing jquery to preload images
		renderedTemplate = renderedTemplate.replace("\\ssrc=", " data-src=");
		// If using text, we need to create a text node to use jQuery
		let $item = isHtml ? this.castElement(renderedTemplate) : document.createTextNode(renderedTemplate);
		if( options.wrap ) {
			const $wrapper = this.createElement("div", "template-contents");
			$wrapper.append($item);
			$item = $wrapper;
		}
		// Direct in DOM Element
		$item.renderingTemplate = {template: templateString, options: options};
		this.renderTemplateElement($item, data, options.prefix);
		
		return $item;
	}
	
	isCheckbox($element) {
		return $element.tagName.toLowerCase() === "input" && $element.getAttribute("type") === "checkbox";
	}
	
	isInput($element) {
		return ["input", "select", "textarea"].includes($element.tagName.toLowerCase());
	}
	
	getInputs($element) {
		return $element.querySelectorAll("input,select,textarea");
	}
	
	/**
	 * @param {Element|string} $element
	 * @param {string|Array<string>} classList
	 * @param {boolean|null} toggle True to add, false to remove, null to invert
	 */
	toggleClass($element, classList, toggle) {
		$element = this.oneElement($element);
		if( typeof classList === "string" ) {
			classList = classList.split(" ");
		}
		for (const cssClass of classList) {
			$element.classList.toggle(cssClass, toggle);
		}
	}
	
	toggle(elements, show) {
		this.allElements(elements)
			.forEach(element => element.hidden = !show);
	}
	
	showElement($element) {
		$element.hidden = false;
	}
	
	oneElement(element, nullable) {
		if( element ) {
			if( isString(element) ) {
				// Selector
				element = document.querySelector(element);
			} else if( isJquery(element) ) {
				// jQuery object
				element = element[0];
			}
		}
		if( !element ) {
			return nullable ? new NullElement() : null;
		}
		
		return element;
	}
	
	allElements(elements) {
		if( elements ) {
			if( isString(elements) ) {
				// Selector
				elements = document.querySelectorAll(elements);
			} else if( isJquery(elements) ) {
				// jQuery object
				elements = elements.get();
			}
			if( elements instanceof NodeList ) {
				// NodeList
				elements = [...elements];
			} else {
				// Single element
				elements = [elements]
			}
		}
		
		return elements || [];
	}
	
	buildCustomEvent(event, detail = null, options = {}) {
		if( detail ) {
			options.detail = detail;
		}
		return new CustomEvent(event, options);
	}
	
	dispatchEvent(element, event, detail = null, options = {}) {
		if( element ) {
			if( element instanceof NodeList ) {
				// Loop on all children
				element.forEach((itemElement) => this.dispatchEvent(itemElement, event, detail));
				return;
			}
			if( element._element ) {
				// Auto handle BS Modals
				element = element._element;
			}
		}
		if( options.bubbles === undefined ) {
			options.bubbles = true;
		}
		element.dispatchEvent(this.buildCustomEvent(event, detail, options));
	}
	
	on(parent, event, selector, handler) {
		this.allElements(parent)
			.forEach($parent => {
				$parent.addEventListener(event, event => {
					const element = event.target.closest(selector);
					if( element ) {
						// Call with this the target element and parameters:
						// The target element (for short usage), the parent element and the event
						handler.call(element, element, $parent, event);
					}
				});
			});
	};
	
	resetForm($form) {
		$form.classList.remove('was-validated');
		$form.reset();
	}
	
}

/**
 * Fake DOM element doing nothing when querying a non-existing element and allowing it to be null, it's still allowing chaining
 */
class NullElement {
	
	addEventListener() {
	}
	
	dispatchEvent() {
	}
	
}

const domService = new DomService();
