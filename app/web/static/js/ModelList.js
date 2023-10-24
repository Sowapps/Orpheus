class ModelList {
	
	associative;// List of items is associative
	$input;
	$list;
	$itemTemplate;
	
	constructor(associative = false) {
		this.associative = associative;
	}
	
	async render() {
		// Do not render items but show list or placeholder according to item count
		if( !this.$list ) {
			return;
		}
		let $placeholder = this.$list.querySelector(":scope > .item-placeholder");
		const itemCount = this.$list.querySelectorAll(":scope > .item").length;
		if( itemCount ) {
			if( $placeholder ) {
				$placeholder.remove();
			}
		} else {
			if( !$placeholder ) {
				$placeholder = await domService.renderTemplate(this.$placeholderTemplate);
				$placeholder.classList.add("item-placeholder");
				this.$list.append($placeholder);
			}
		}
	}
	
	assignInput($input) {
		this.$input = $input;
	}
	
	async assignList($list, $template, $placeholderTemplate) {
		if( !$list || !$template ) {
			throw new Error("$list and $template parameters are required");
		}
		this.$list = $list;
		this.$itemTemplate = $template;
		this.$placeholderTemplate = $placeholderTemplate;
		await this.render();
	}
	
	createClone(item) {
		const $item = domService.renderTemplate(this.$itemTemplate, item, {immediate: true});
		$item.classList.remove("item_model");
		$item.classList.add("item");
		return $item;
	}
	
	empty() {
		this.$list.innerHTML = '';
	}
	
	async addItem(itemData, noSave) {
		const $item = this.createClone(itemData);
		$item.dataset.id = itemData.id; //this.$list.childElementCount+1;
		this.$list.append($item);
		if( !noSave ) {
			this.saveItems();
		}
		await this.render();
	};
	
	async updateItem() {
		// itemRow, itemData
		throw new Error("Require upgrade, contact developer");
	};
	
	saveItems() {
		throw new Error("Require upgrade, contact developer");
	};
	
	loadList(list) {
		if( this.associative ) {
			list = Object.entries(list).map(([key, value]) => {
				return {
					_key_: key,
					_value_: value,
				};
			});
		}
		list.forEach(itemData => {
			if( isScalar(itemData) ) {
				itemData = {"_value_": itemData};
			} else if( !isObject(itemData) ) {
				return;
			}
			// noinspection JSIgnoredPromiseFromCall
			this.addItem(itemData, true);
		});
	}
	
	loadFromInput() {
		if( !this.$input || !this.$input.value ) {
			return;
		}
		let items = JSON.parse(this.$input.value);
		if( !this.associative && isPureObject(items) ) {
			throw new Error("Please declare a dictionary as associative");
		}
		if( this.associative && !isPureObject(items) ) {
			throw new Error("Please do not declare an array as associative");
		}
		this.loadList(items);
	}
}

ready(() => {
	document.querySelectorAll(".model-list")
		.forEach(async $list => {
			// const modelType = $list.dataset.modelType;
			const associative = !!$list.dataset.modelAssociative;
			const $modelInput = $list.dataset.modelInput ? document.querySelector($list.dataset.modelInput) : null;
			const $modelItemTemplate = $list.dataset.modelItemTemplate ? document.querySelector($list.dataset.modelItemTemplate) : null;
			const $modelPlaceholderTemplate = $list.dataset.modelPlaceholderTemplate ? document.querySelector($list.dataset.modelPlaceholderTemplate) : null;
			const modelList = new ModelList(associative);
			if( $modelInput ) {
				modelList.assignInput($modelInput);
			}
			await modelList.assignList($list, $modelItemTemplate, $modelPlaceholderTemplate);
			$list._model = modelList;
			modelList.loadFromInput();
		});
});
