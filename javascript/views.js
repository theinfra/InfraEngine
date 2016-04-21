function ShowTab(T)
{
		i = 0;
		while (document.getElementById("tab" + i) != null) {
			$('#div'+i).hide();
			$('#tab'+i).removeClass('active');
			++i;
		}

		if (T == 2) {
			$('#SaveButtons').hide();
		} else {
			$('#SaveButtons').show();
		}

		// Bulk Discount checks
		if (T == 7) {
			// Are we enabled?
			if (shop.config.DiscountRulesEnabled !== "1") {
				$('#DiscountRulesWarning').text(lang.DiscountRulesNotEnabledWarning);
				$('#DiscountRulesWarning').show();
				$('#DiscountRulesDisplay').hide();

			// Else check to see if we have variations when we switch to the discount rules tab
			} else if (document.getElementById('useProdVariationYes').checked) {
				$('#DiscountRulesWarning').text(lang.DiscountRulesVariationWarning);
				$('#DiscountRulesWarning').show();
				$('#DiscountRulesDisplay').hide();
			} else {
				$('#DiscountRulesWarning').hide();
				$('#DiscountRulesDisplay').show();
			}
		}

		$('#div'+T).show();
		$('#tab'+T).addClass('active');
		$('#currentTab').val(T);
		//document.getElementById("currentTab").value = T;
}

function DisableFormElements(classname){
	if(classname == ""){
		return;
	}
	
	$("."+classname+" td input, ."+classname+" td select").attr('disabled', 'disabled');
}

function EnableFormElements(classname){
	if(classname == ""){
		return;
	}
	
	$("."+classname+" td input, ."+classname+" td select").removeAttr('disabled');
}

function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}