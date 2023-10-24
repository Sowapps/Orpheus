let dialogConfigEdit;
let configLargeType = ["text"];

function showDialogConfigEdit(data = null) {
	const isNew = !data;
	if( isNew ) {
		data = {type: "simple"};
	}
	[...dialogConfigEdit._element.querySelectorAll(".update-readonly")]
		.forEach($element => {
			$element.readOnly = !isNew;
		});
	
	dialogConfigEdit._element.querySelector("form").reset();
	// setEditDialogValueType(isNew ? "simple" : data.type);
	domService.fillForm(dialogConfigEdit._element, data, "row[%s]");
	dialogConfigEdit.show();
}

ready(() => {
	// const myModal = new bootstrap.Modal(document.getElementById('myModal'), options)
	dialogConfigEdit = new bootstrap.Modal("#DialogConfigEdit");
	
	dialogConfigEdit._element.querySelector("#InputRowType")
		.addEventListener("change", function () {
			const type = this.value;
			dialogConfigEdit._element.querySelector(".modal-dialog").classList.toggle("modal-lg", configLargeType.includes(type));
			dialogConfigEdit._element.querySelectorAll(".row_value")
				.forEach(element => {
					if( element.classList.contains("type_" + type) ) {
						// Show/Enable the one we need
						element.hidden = element.disabled = false;
					} else {
						// Hide/Disable others
						element.hidden = element.disabled = true;
					}
				});
		});
	
	[...document.getElementsByClassName("action-config-create")]
		.forEach($element => $element.addEventListener("click", () => {
			showDialogConfigEdit();
		}));
	[...document.getElementsByClassName("action-config-update")]
		.forEach($element => $element.addEventListener("click", () => {
			showDialogConfigEdit({...$element.closest("tr").dataset});
		}));
});
