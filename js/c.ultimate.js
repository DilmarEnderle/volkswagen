function ultimateDialog(options,callback)
{
	$.post("c.ultimate.php", {options:options}, function(data)
	{
		if (typeof options["width"] === 'undefined')
			var w = 400;
		else
			var w = options["width"];

		if ($("#ultimate-dim").length == 0)
			$('body').append('<div id="ultimate-dim"><div id="ultimate-box"></div></div>');

		$("#ultimate-box").html(data);
		$("#ultimate-box").css("width", w+"px");
		$("#ultimate-box").css("top", ($(window).scrollTop()+100)+"px");
		$("#ultimate-dim").css("height", $(document).height());
		$("#ultimate-dim").show();
		$("#ultimate-default-btn").focus();
		if (typeof callback === 'function') callback();
	},"text");
}

function ultimateClose()
{
	$("#ultimate-dim").remove();
}

function ultimateLoader(y,t)
{
	if (y)
	{
		if ($("#ultimate-loader-dim").length == 0)
			$('body').append('<div id="ultimate-loader-dim"><div id="ultimate-loader-box"><img src="img/ultimate24.gif"></div><div id="ultimate-loader-text"></div></div>');

		$("#ultimate-loader-dim").css("height", $(document).height());
		$("#ultimate-loader-box").css("margin", ($(window).scrollTop()+200)+"px auto 0 auto");
		$("#ultimate-loader-text").html(t);
		$("#ultimate-loader-dim").show();
	}
	else
		$("#ultimate-loader-dim").remove();
}

function ultimateLoaderT(t)
{
	$("#ultimate-loader-text").html(t);
}

function ultimateError(t)
{
	$("#ultimate-error").html(t);
	$("#ultimate-error").fadeIn(260).fadeOut(220).fadeIn(180).fadeOut(140).fadeIn(100);
}
