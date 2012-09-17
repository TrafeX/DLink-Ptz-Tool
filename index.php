<?php
require_once 'config.php';
require_once 'ptz.php';

$ptz = new Ptz(CAM_HOST, CAM_USERNAME, CAM_PASSWORD);

if (isset($_GET['command'])) {
    switch ($_GET['command']) {

        case Ptz::POSITION_COMMAND:
            if (!isset($_GET[Ptz::XPOS], $_GET[Ptz::YPOS])) {
                throw new InvalidArgumentException('Missing x/y pos');
            }
            $ptz->setPosition($_GET[Ptz::XPOS], $_GET[Ptz::YPOS]);
            break;
        case Ptz::PRESET_COMMAND:
            $ptz->setPreset($_GET[Ptz::PRESET_ID]);
            break;
        default:
            throw new InvalidArgumentException('Invalid command');
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nl" lang="nl" dir="ltr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>TrafeX' PTZ camera tool</title>
        <script type="text/javascript "src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
        <script type="text/javascript">
            $(function() {
                // make all links ajax calls ;
                //$("a").autoajax( oncomplete: function() { alert("completed") } );
                $('.ajax a').each(function() {
                    jQuery(this).click( function(e) {
                        $.ajax(this.href);
                        e.preventDefault();
                    })
                })
            })
        </script>
    </head>
    <body>
        <p>
            <img src="<?= $ptz->getMjpegUrl() ?>" />
        </p>
        <p class="ajax">
            <a href="?command=<?=Ptz::POSITION_COMMAND ?>&xpos=-10&ypos=0">Move left</a>
            <a href="?command=<?=Ptz::POSITION_COMMAND ?>&xpos=10&ypos=0">Move right</a>
            <a href="?command=<?=Ptz::POSITION_COMMAND ?>&xpos=0&ypos=10">Move up</a>
            <a href="?command=<?=Ptz::POSITION_COMMAND ?>&xpos=0&ypos=-10">Move down</a>
        </p>
        <h3>Presets</h3>
        <ul class="ajax">
            <?php
            $urls = $ptz->getPresets();
            foreach ($urls as $name => $url) {
                printf('<li><a href="%s">%s</a></li>', $url, $name);
            }
            ?>
        </ul>
        <h3>RTSP</h3>
        <ul>
            <?php
            $urls = $ptz->getRtspUrls();
            foreach ($urls as $url) {
                printf('<li><a href="%s">%s</a></li>', $url, $url);
            }
            ?>
        </ul>
    </body>
</html>
