ready(() => {
	document.querySelectorAll(".cellfallback")
		.forEach(function ($element) {
			// Save content
			const content = $element.innerText;
			
			// Improve content
			$element.innerHTML = $element.innerHTML
				.replace(new RegExp("(%s\\$?\d?)", 'g'), '<b title="' + t('type_string') + '">$1</b>')
				.replace(new RegExp("(%d\\$?\d?)", 'g'), '<b title="' + t('type_number') + '">$1</b>')
				.replace(/(#[^#]*#)/g, '<b title="' + t('type_variable') + '">$1</b>');
			
			// Add copy button
			const $targetInput = $element.nextElementSibling.querySelector("input,textarea");
			const $button = domService.castElement('<button class="btn btn-default" type="button"><i class="fa fa-fw fa-forward"></i></button>');
			$button.addEventListener('click', () => domService.assignValue($targetInput, content));
			$element.prepend($button);
		});
});
