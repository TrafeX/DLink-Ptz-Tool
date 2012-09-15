<?php
/**
 * Class to control the D-Link DCS-5222L
 *
 * @author Tim de Pater <code AT trafex DOT nl>
 */
class Ptz
{
    const POSITION_COMMAND = 'set_relative_pos';
    const STOP_COMMAND = 'stop';
    const PAN_COMMAND = 'pan_patrol';
    const PATROL_COMMAND = 'user_patrol';
    const PRESET_COMMAND = 'goto_preset_position';

    const XPOS = 'xpos';
    const YPOS = 'ypos';
    const PRESET_ID = 'presetId';

    protected $host;
    protected $user;
    protected $password;
    protected $useSsl = false;
    protected $ptzUrl;
    protected $baseUrl;

    protected $xmlDoc;

    public function __construct($host, $user, $password, $settings = array())
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;

        if (isset($settings['ssl'])) {
            $this->useSsl = $settings['ssl'];
        }

        $scheme = 'http://';
        if ($this->useSsl) {
            $scheme = 'https://';
        }
        $this->ptzUrl = $scheme . $this->host . '/cgi/ptdc.cgi?';
        $this->baseUrl = $scheme . $this->host;
    }

    public function setPosition($x, $y)
    {
        $params = http_build_query(
            array(
                'command' => self::POSITION_COMMAND,
                'posX' => $x,
                'posY' => $y,
            )
        );
        $this->request($this->ptzUrl . $params);
    }

    public function setPreset($id)
    {
        $params = http_build_query(
            array(
                'command' => self::PRESET_COMMAND,
                'presetId' => $id,
            )
        );
        $this->request($this->ptzUrl . $params);
    }

    public function getPresets()
    {
        $xpath = new DOMXPath($this->getXml());
        $presets = $xpath->query('//config/preset');
        $urls = array();
        foreach ($presets as $preset) {

            $params = http_build_query(
                array(
                    'command' => self::PRESET_COMMAND,
                    'presetId' => (int)$preset->lastChild->nodeValue -1,
                )
            );
            $urls[$preset->firstChild->nodeValue] = '?' . $params;
        }
        return $urls;
    }

    public function getRtspUrls()
    {
        $xpath = new DOMXPath($this->getXml());
        $rtsp = $xpath->query('//config/RTSP');
        $rtspUrls = array();
        foreach ($rtsp as $urls) {
            $nodes = $urls->childNodes;
            foreach ($nodes as $node) {
                if ('rtpPort' == $node->nodeName) {
                    continue;
                }
                $rtspUrls[] = 'rtsp://' . $this->host . '/' . $node->nodeValue;
            }
        }
        return $rtspUrls;
    }

    public function getMjpegUrl()
    {
        $scheme = 'http://';
        if ($this->useSsl) {
            $scheme = 'https://';
        }
        return $scheme . $this->user . ':' . $this->password . '@' . $this->host . '/video/mjpg.cgi';
    }

    protected function getXml()
    {
        if (null === $this->xmlDoc) {
            $xml = $this->request($this->baseUrl . '/eng/liveView.cgi');
            $doc = new DOMDocument();
            $doc->preserveWhiteSpace = false;
            $doc->loadXml($xml);
            $this->xmlDoc = $doc;
        }
        return $this->xmlDoc;
    }

    protected function request($url)
    {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERPWD => $this->user . ':' . $this->password,
        ));
        return curl_exec($ch);
    }
}

