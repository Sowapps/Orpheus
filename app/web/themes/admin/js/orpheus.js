(function ($) {// Preserve our jQuery

$(function() {
	// http://ivaynberg.github.io/select2/
	$("select").each(function() {
		var _	= $(this);
		var options	= {};
		if( !_.hasClass("searchable") ) {
			options.minimumResultsForSearch	= -1;
		}
		_.select2(options);
	});
	
	(function() {
		var hash = window.location.hash;
		hash && $('ul.nav a[href="'+hash+'"]').tab('show');
		$('.nav-tabs a').click(function (e) {
			$(this).tab('show');
			var scrollmem = $('body').scrollTop();
			window.location.hash = this.hash;
			$('html,body').scrollTop(scrollmem);
		});
	})();
	
	$('.modal').on('shown.bs.modal', function(e) {
//		document.activeElement.blur();
//		console.log(e);
//		console.log(e.relatedTarget);
		var target = $(e.relatedTarget);
		if( target.length && target.data("focus") ) {
			$(this).find(target.data("focus")).focus();
		} else {
			$(this).find(".modal-body :input:visible").first().focus();
		}
	});
	
	$("input.select2.autocomplete").shown(function() {
		var _	= $(this);
		if( _.data("autocomplete-auto") ) { return; }
		_.data("autocomplete-auto", 1);
		_.select2({
			minimumInputLength: 2,
			placeholder: "Entrez votre recherche en cliquant ici",
//			multiple: true,
//			maximumSelectionSize: _.data("maximumselectionsize") ? _.data("maximumselectionsize") : 1,
			query: function (query) {
				var queryStr	= _.data("query") ? "&"+_.data("query") : "";
				// Targetting the autocomplete itself
				requestAutocomplete(_.data('what'), query.term+queryStr, function(rows) {
					var data = {results: []};
					for( var k in rows ) {
						data.results.push({id: rows[k].id, text: rows[k].name });
					}
					query.callback(data);
				});
			}
		});
	});
	
	$("label").click(function() {
		$(this).next().focus();
	});
	
	$(".confirmable").click(function(e) {
//		debug("Click button");
		var _	= $(this);
//		console.log(_);
		if( _.data("confirm") ) {
			_.after('<input type="hidden" name="'+_.attr("name")+'_confirm" value="1"/>');
			return;
		}
		e.preventDefault();
		// Not mouse click
//		console.log(e.pageX);
//		console.log(_.data("confirm_timer"));
		if( !e.pageX || _.data("confirm_timer") ) { debug("Return"); return; }
//		console.log(e);
		// Delay the confirm to reject double click
//		debug("Confirm timer launch");
		_.data("confirm_timer", setTimeout(function() {
//			debug("Confirm ended");
			_.data("confirm_timer", 0);
			_.data("confirm", _.html());
			_.text("Confirmer ?");
			_.removeClass("btn-warning");
			_.addClass("btn-danger");
		}, 1000));
		_.addClass("btn-warning");
		// Cancel events
		_.one("mouseout.confirmable click.confirmable", function() {
			_.unbind(".confirmable");// Apply to each event, so we remove all others
//			debug("Cancel");
			if( _.data("confirm") ) {
//				debug("Cancel confirm");
				_.html(_.data("confirm"));
				_.data("confirm", 0);
				_.removeClass("btn-danger");
			} else {
//				debug("Abort timer");
				clearTimeout(_.data("confirm_timer"));
				_.data("confirm_timer", 0);
				_.removeClass("btn-warning");
			}
		});
	});
});

function refresh() {
//	console.log($('.uploadlayer'));
	$('.uploadlayer').each(function() {
//		console.log(".uploadlayer shown");
		var _	= $(this);
		if( _.data('uploadlayer') ) { return; }
		_.data('uploadlayer', 1);
		var l	= _.prev();
		var w	= l.outerWidth();
		var h	= l.outerHeight();
//		debug(l.css("display"));
		var pos	= l.position();
//		debug(l);
//		debug(l.position());
//		debug(parseInt(l.css('margin-top')));
//		debug(l.css('margin-left'));
		_.css({"opacity":0, "position":"absolute", "top":(pos.top+parseInt(l.css('margin-top')))+"px", "left":(pos.left+parseInt(l.css('margin-left')))+"px", "width":w+"px", "height":h+"px", "display":"block", "cursor":"pointer"});
//		_.css({"opacity":0, "margin-left": (-w-4)+"px", "width": w+"px", "height": h+"px", "display":l.css("display"), "cursor":"pointer"});
//		debug(_);
//		debug(_.position());
		_.hover(function() { l.addClass("active"); }, function() { l.removeClass("active"); });
		if( _.data('submit') ) {
			_.change(function() { $(_.data('submit')).click(); });
		}
	});
}

$.fn.confirmAction	= function(text) {
// 	if( !text ) { text = ""; }
	if( !$("#confirmModal").length ) {
// 		<div class="modal-body"><p class="confirm-text"></p></div>\
		$("body").append('\
<div class="modal fade modal-center modal-compact" id="confirmModal">\
	<div class="modal-dialog">\
		<div class="modal-content">\
			<div class="modal-header">\
				<button type="button" class="close fa fa-times" data-dismiss="modal" aria-hidden="true"></button>\
				<h4 class="modal-title confirm-title"></h4>\
			</div>\
			<div class="modal-footer">\
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Annuler</button>\
				<button id="confirmBtn" type="button" class="btn btn-primary">Confirmer</button>\
			</div>\
		</div>\
	</div>\
</div>');
		$("#confirmModal").modal({show: false});
		$("#confirmBtn").click(function() {
			$($(this).data("item")).trigger("click", [1]);
		});
	}
	$(this).click(function(e, force) {
		if( !force ) {
			e.preventDefault();
			$("#confirmBtn").data("item", $(this));
			if( typeof text == "function" ) {
				this.__text = text;
				text = this.__text();
			}
			$("#confirmModal").find(".confirm-title").html(text);
// 			$("#confirmModal").find(".confirm-text").html(text);
			$("#confirmModal").modal("show");
// 			if( onShown ) {
// 				this.__onShown	= onShown;
// 				this.__onShown();
// 			}
		}
	});
};

})(jQuery);

function makeSelect2(element) {
	var _		= $(element);
	var options	= {};
	var input	= _;
	if( _.is('input') ) {
		options.data	= [];
	}
	/*
	if( _.hasClass('allownewvalues') ) {
		if( _.is("select") ) {
			// Replace the select with an input
			input	= $("<input />");
			input.addClass(_.prop('class'));
			input.attr('name', $(this).attr('name'));
			var data	= [];
			var val		= '';
			_.find('option').each(function() {
				data.push({'id':$(this).attr('value'),'text':$(this).text()});
				if( $(this).prop('selected') ) {
					val	+= (val ? ',' : '').$(this).attr('value');
				}
			});
			options.data	= data;
			input.val(val);
			_.after(input);
			_.remove();
		}
		options.id		= function(object) {
			return object.text;
		};
		//Allow manually entered text in drop down.
		options.createSearchChoice	= function(term, data) {
			if( $(data).filter( function() {
				return this.text.localeCompare(term)===0;
			}).length===0) {
				return {id:term, text:term};
			}
		};
	}
	*/
//	var options	= {};
	if( !input.hasClass("searchable") ) {
		options.minimumResultsForSearch	= -1;
	}
	input.select2(options); 
}
