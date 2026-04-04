/**
	**options to have following keys:
		**searchText: this should hold the value of search text
		**searchPlaceHolder: this should hold the value of search input box placeholder
**/
(function($){
	$.fn.tableSearch = function(options){
		if(!$(this).is('table')){
			return;
		}
		var tableObj = $(this),
			searchText = (options.searchText)?options.searchText:'Search: ',
			searchPlaceHolder = (options.searchPlaceHolder)?options.searchPlaceHolder:'',
			//divObj = $('<div style="float:right;margin-top:0px;display:inline-block;">'+searchText+' </div><br/>'),
			//inputObj = $('<input type="text" placeholder="'+searchPlaceHolder+'" />'),
			caseSensitive = (options.caseSensitive===true)?true:false,
			searchFieldVal = '',
			pattern = '';
		$('#wsearch').off('keyup').on('keyup', function(){
//$(".tabres").hide();
			var count =  ($("#cbody").children("tr").filter(function() {
	            return $(this).css('display') !== 'none';
	        }).length);


	        $(".tabres").html(' '+count+' result(s) found');
			searchFieldVal = $(this).val();
			pattern = (caseSensitive)?RegExp(searchFieldVal):RegExp(searchFieldVal, 'i');
			tableObj.find('tbody tr').hide().each(function(){
				var currentRow = $(this);
				currentRow.find('td').each(function(){
					if(pattern.test($(this).html())){
						currentRow.show();
						return false;
					}
				});
			});

		});
		//tableObj.before(divObj.append(inputObj));
		return tableObj;
	}
}(jQuery));
