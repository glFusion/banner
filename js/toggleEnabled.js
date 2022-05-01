/*  Miscellaneous javascript AJAX functions
*/
var BANR_toggleEnabled = function(cbox, id, type) {
    oldval = cbox.checked ? 0 : 1;
    var dataS = {
        "action" : "toggleEnabled",
        "id": id,
        "type": type,
        "oldval": oldval,
    };
    data = $.param(dataS);
    $.ajax({
        type: "POST",
        dataType: "json",
        url: site_admin_url + "/plugins/banner/ajax.php",
        data: data,
        success: function(result) {
            cbox.checked = result.newval == 1 ? true : false;
            try {
                $.UIkit.notify("<i class='uk-icon-check'></i>&nbsp;" + result.statusMessage, {timeout: 1000,pos:'top-center'});
            }
            catch(err) {
                alert(result.statusMessage);
            }
        }
    });
    return false;
};

function BANR_toggleDateField(val, id)
{
	var dates_id = "#sp_" + id + "date";
	var no_rest_id = dates_id + "_nolimit";
    if (val) {
        // A date limit is in effect
		$(dates_id).show();
		$(no_rest_id).hide();
    } else {
        // Unchecked, no date limit
		$(dates_id).hide();
		$(no_rest_id).show();
    }
}

