<?php
//ini_set('arg_separator.output', '&amp;');

require_once 'config.php';
require_once 'Dlink/Ptz.php';

use TrafeX\Dlink\Ptz;

$ptz = new Ptz(CAM_HOST, CAM_USERNAME, CAM_PASSWORD);

$stepSize = Ptz::DEFAULT_STEPSIZE;
if (isset($_GET['stepsize']) && intval($_GET['stepsize']) > 0) {
    $stepSize = intval($_GET['stepsize']);
}
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
        case Ptz::PATROL_COMMAND:
        case Ptz::STOP_COMMAND:
            $ptz->setPatrol($_GET['command']);
            break;
        default:
            throw new InvalidArgumentException('Invalid command');
    }
    die;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nl" lang="nl" dir="ltr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>TrafeX' PTZ camera tool</title>
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
        <script type="text/javascript">
            $(function() {
                // make all links ajax calls ;
                $('.ajax a').each(function() {
                    jQuery(this).click( function(e) {
                        $.ajax(this.href);
                        e.preventDefault();
                    })
                })
            })
        </script>
        <style type="text/css">
            body {
                font-family: verdana, arial, helvetica;
            }
            #movement {
                float: left;
                width: 250px;
                display: inline-block;
            }
            #presets {
                width: 250px;
                display: inline-block;
            }
            #rtsp {
                clear: both;
                padding-top: 10px;
            }
            ul {
                margin: 35px 0;
                padding: 0;
                list-style-type: none;
            }
            ul li {
                display: inline;
            }
            ul li a {
                padding: 5px 10px;
                border: 1px solid #aaa;
                background-color: #eee;
                color: #47a;
                text-decoration: none;
            }
            a:hover, a:active {
                color: red;
                border: 1px solid black;
            }
            ul li.newline {
                display: list-item;
                padding: 6px 10px 6px 10px;
            }

        </style>
    </head>
    <body>
        <p>
            <img src="<?php echo $ptz->getMjpegUrl() ?>" alt="Liveview" />
        </p>
        <div id="movement">
            <h3>Movement</h3>
            <ul class="ajax">
                <?php
                $urls = $ptz->getPositions($stepSize);
                foreach ($urls as $name => $url) {
                    printf('<li><a href="%s">%s</a></li>', $url, $name);
                }
                ?>
            </ul>
            <h4>Step size</h4>
            <form action="" method="get">
                <div>
                    <input type="text" name="stepsize" size="2" maxlength="3" value="<?php echo $stepSize; ?>" />
                    <input type="submit" name="set" value="Set" />
                </div>
            </form>
        </div>
        <div id="presets">
            <h3>Presets</h3>
            <ul class="ajax">
                <?php
                $urls = $ptz->getPresets();
                foreach ($urls as $name => $url) {
                    printf('<li><a href="%s">%s</a></li>', $url, $name);
                }
                ?>
            </ul>
            <h4>Patrol</h4>
            <ul class="ajax">
                <?php
                $urls = $ptz->getPatrolCommands();
                foreach ($urls as $name => $url) {
                    printf('<li><a href="%s">%s</a></li>', $url, $name);
                }
                ?>
            </ul>
        </div>
        <div id="rtsp">
            <h3>RTSP</h3>
            <ul>
                <?php
                $urls = $ptz->getRtspUrls();
                foreach ($urls as $url) {
                    printf('<li class="newline"><a href="%s">%s</a></li>', $url, $url);
                }
                ?>
            </ul>
        </div>
    </body>
</html>
