$(document).ready(function(){
	keepOnline();
	setInterval(function(){
		keepOnline()
	}, 20000);
});

function keepOnline()
{	
	$.get("c.online.php", function(d){
		if (d == "o")
			window.location.href = "cli_logout.php";
	});
}
