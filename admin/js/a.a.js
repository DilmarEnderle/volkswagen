var block_start = 124;
var block_limit = 5000;
var current_block = [];

$(document).ready(function(){

	getNextBlock();
});

function getNextBlock()
{
	$('body').prepend('<div style="border-bottom:2px solid #aa0000;">Fetching block from... '+block_start+'</div>');
	$.ajax({
		type: "GET",
		url: "a.a_block.php",
		data: "block_start="+block_start+"&block_limit="+block_limit,
		dataType: "json",
		success: function(data)
		{
			//data[0] = block_id_from
			//data[1] = block_id_to
			//data[2] = ids_to_process

			$('body').prepend('<div>Processing block: '+data[0]+' - '+data[1]+'</div>');
			current_block = data[2];
			processCurrentBlock();
		}
	});
}

function processCurrentBlock()
{
	for (i=0; i < current_block.length; i++)
	{
		$.ajax({
			type: "GET",
			url: "a.a_process.php",
			data: "id_to_process="+current_block[i],
			dataType: "text",
			success: function(data)
			{
				$('body').prepend(data);
			}
		});
	}
}
