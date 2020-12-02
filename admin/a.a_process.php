<?php

require_once "include/config.php";
require_once "include/essential.php";
require_once "../../Aws-3.0/aws-autoloader.php";

$return = '<div style="border-top:1px solid #ff0000;">';
$id = $_GET["id_to_process"];


// CREATE NEW CLIENT
$s3 = new Aws\S3\S3Client([
	'region' => AWS_S3_REGION,
	'version' => 'latest',
	'endpoint' => 'http://s3.'.AWS_S3_REGION.'.amazonaws.com/',
	's3ForcePathStyle' => true,
	'credentials' => [
		'key' => AWS_S3_KEY,
		'secret' => AWS_S3_SECRET,
		]
	]);

$db = new Mysql();
$db->query("SELECT id, arquivo FROM gelic_historico WHERE id = $id");
while ($db->nextRecord())
{
	// GET OBJECT INFO
	try
	{
		$result = $s3->headObject(['Bucket'=>AWS_S3_BUCKET,'Key'=>'vw/arquivos/chat/'.utf8_encode($db->f("arquivo"))]);
		$return .= '<br><span style="color:#0000ff;">'.$db->f("id").'</span> '.utf8_encode($db->f("arquivo")).' [ YES ]';


		// COPY OBJECT
		$result = $s3->copyObject([
			'Bucket' => AWS_S3_BUCKET, 
			'CopySource' => AWS_S3_BUCKET.'/vw/arquivos/chat/'.utf8_encode($db->f("arquivo")), 
			'Key' => 'vw/licchat/'.utf8_encode($db->f("arquivo"))
			]);

		$return .= '<br><span style="color:#00aa00;">'.$db->f("id").'</span> <span style="color:#00c400;">- COPY SUCCESS -</span>';


		try
		{
			$copied_result = $s3->headObject(['Bucket'=>AWS_S3_BUCKET,'Key'=>'vw/licchat/'.utf8_encode($db->f("arquivo"))]);
			if ($copied_result["ContentLength"] > 0)
			{
				//remove source object
				$s3->deleteObject(['Bucket' => AWS_S3_BUCKET, 'Key' => 'vw/arquivos/chat/'.utf8_encode($db->f("arquivo"))]);

				$return .= '<br><span style="color:#f00000;">'.$db->f("id").'</span>  <span style="color:#ddc400;"> - DELETE SUCCESS -</span>';
			}
		}
		catch(Exception $e)
		{
			$return .= '<br><span style="color:#ff0000;">Copy NOT found!</span>';
		}			
	}
	catch(Exception $e)
	{
		$return .= '<br><span style="color:#ee0000;">'.$db->f("id").'</span> <span style="color:#ff0000;">'.utf8_encode($db->f("arquivo")).' File not found</span>';
	}
}

$return .= '</div>';

echo $return;

?>
