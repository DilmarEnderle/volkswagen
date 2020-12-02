$(document).ready(function(){
	keepOnline();
	setInterval(function(){
		keepOnline()
	}, 20000);
});

function keepOnline()
{	
	$.get("a.online.php", function(d){
		if (d == "o")
			window.location.href = "a.logout.php";
	});
}
