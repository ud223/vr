<?php

require ('../../extlib/OSS/aliyun.php');

use \Aliyun\OSS\OSSClient;

$client = OSSClient::factory(array(
            'AccessKeyId' => '9DRZ6locwtVaPjlH',
                'AccessKeySecret' => 'Av8wIjNXzQyYT40cdQmCC5pvaAsSIT',
        ));

function putResourceObject(OSSClient $client, $bucket, $key, $content, $size) {
    $result = false;
    try {
        $result = $client->putObject(array(
            'Bucket' => $bucket,
            'Key' => $key,
            'Content' => $content,
            'ContentLength' => $size,
        ));
    } catch (Exception $e) {
        $result = false;
    }
    return $result;
}

$file = $_FILES["file"]["tmp_name"];
$filesize = $_FILES["file"]["size"];

$bucket = 'cheesetv';
//$key = $_FILES["file"]["name"];
$ext = '.' . pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
$uid = uniqid();
$key = $uid . $ext;
$output = array("code" => 200,
    "size" => $filesize,
    "uid" => $key,
    "name" => $_FILES["file"]["name"]);
$result = putResourceObject($client, $bucket, $key, fopen($file, 'r'), $filesize);
if ($result) {
    // success
    $video_format_arr = array("mp4", "flv", "ogg");
    $audio_format_arr = array("mp3", "wma");
    if (in_array($ext, $video_format_arr)) {
        // video file
        $output["type"] = "video";
    } else if (in_array($ext, $audio_format_arr)) {
        // audio file
        $output["type"] = "audio";
    } else {
        $output["code"] = 0;
    }
} else {
    $output['code'] = 0;
}
echo json_encode($output);