var ajax = true;

$(document).ready(function(){
	$(window).resize(function() {
		if ($("#adm-drop-contents").hasClass("isLast") && $("#adm-drop-contents").is(":visible"))
		{
			var lf = Math.round($("#adm-drop").offset().left + $("#adm-drop").outerWidth() - $("#adm-drop-contents").outerWidth());
			$("#adm-drop-contents").css("left", lf+"px");
		}
	});

	$(document).click(function(e){
		if (!$(e.target).hasClass("adr") && !$(e.target).parent().hasClass("adr"))
			admDropClose();
	});
});

function isEmail(strng)
{
	if (strng == "") return false;
	var emailFilter = /^.+@.+\..{2,}$/;
	var illegalChars = /[\(\)\<\>\,\;\:\\\"\[\]]/;
	if (!(emailFilter.test(strng)) || strng.match(illegalChars))
		return false;
	return true;    
}

function foc(ob) { $("#"+ob).focus(); }


function admDropClick()
{
	if ($("#adm-drop").hasClass("drop"))
	{
		admDropClose();
	}
	else
	{
		$("#adm-drop").addClass("drop");
		$("#adm-drop").children("img").attr("src", "img/a-up-g.png");

		$("#adm-drop-contents").addClass("isLast");
		$("#adm-drop").css("border-bottom-left-radius",0);
		$("#adm-drop").css("border-bottom-right-radius",0);
		$("#adm-drop-contents").css("top", $("#adm-drop").offset().top + 27);
		var lf = Math.round($("#adm-drop").offset().left + $("#adm-drop").outerWidth() - $("#adm-drop-contents").outerWidth());
		$("#adm-drop-contents").appendTo(document.body);
		$("#adm-drop-contents").css("left", lf+"px");
		$("#adm-drop-contents").show();
	}
}

function admDropClose()
{
	$("#adm-drop").removeClass("drop");
	$("#adm-drop").children("img").attr("src", "img/a-down-g.png");
	$("#adm-drop-contents").hide();
	$("#adm-drop").css("border-bottom-left-radius","3px");
	$("#adm-drop").css("border-bottom-right-radius","3px");
}

