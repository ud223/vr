<?php
require ('../../extlib/OSS/aliyun.php');
use \Aliyun\OSS\OSSClient;

$bean = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    $client = OSSClient::factory(array(
                'AccessKeyId' => '9DRZ6locwtVaPjlH',
                'AccessKeySecret' => 'Av8wIjNXzQyYT40cdQmCC5pvaAsSIT',
    ));

    function putResourceObject(OSSClient $client, $bucket, $key, $content, $size, $output) {
        $result = false;
        try {
            $result = $client->putObject(array(
                'Bucket' => $bucket,
                'Key' => $key,
                'Content' => $content,
                'ContentLength' => $size,
            ));
        } catch (Exception $e) {
            $output["message"] = "upload exception : " . $e->getMessage();
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
        "key" => $key,
        "ext" => $ext,
        "name" => $_FILES["file"]["name"]);
    $result = putResourceObject($client, $bucket, $key, fopen($file, 'r'), $filesize, $output);
    if ($result) {
        // success
        $video_format_arr = array(".mp4", ".flv", ".ogg", ".jpg");
        $audio_format_arr = array(".mp3", ".wma");
        if (in_array($ext, $video_format_arr)) {
            // video file
            $output["type"] = "video";
        } else if (in_array($ext, $audio_format_arr)) {
            // audio file
            $output["type"] = "audio";
        } else {
            $output["code"] = 0;
            $output["message"] = "format error " . $ext;
        }
    } else {
        $output["code"] = 0;
    }
    $bean = json_encode($output);
}
?>


<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <form ENCTYPE="multipart/form-data" ACTION="oss-ctrl.php" METHOD="POST">
            <input type="hidden" name="MAX_FILE_SIZE"  value="102400000">
            <input NAME="file" TYPE="file">
            <input TYPE="submit" style="display:none" VALUE="Send File">
            <div id="result" class="hidden"><?php echo $bean ?></div>
        </form>
    </body>
</html>
