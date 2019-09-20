<?php


namespace myPHPnotes;


class YouTubeLive
{
    public $client;
    protected $guzzle;
    protected $youtube;
    public $broadcasts;
    public $streams;
    function __construct(\Google_Client $googleClient, string $client_id, string $client_secret, string $redirect_uri)
    {
        $this->client = $googleClient;
        if ($this->client) {
            $this->client->setClientId($client_id);  
            $this->client->setClientSecret($client_secret);
            $this->client->setRedirectUri($redirect_uri);
            $this->client->setScopes(array('https://www.googleapis.com/auth/youtube', 'https://www.googleapis.com/auth/youtube.force-ssl'));
        }
        $state = mt_rand();
        $this->client->setApprovalPrompt('force');
        $this->client->setState($state);
        $this->client->setAccessType("offline");
        $_SESSION['state'] = $state;
        $this->youtube = new \Google_Service_YouTube($this->client);
        $this->broadcasts = $this->youtube->liveBroadcasts;
        $this->streams = $this->youtube->liveStreams;
    }
    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();        
    }
    public function setTokens($token_array)
    {
        $_SESSION['access_token'] = $token_array['access_token'];
        $_SESSION['token_type'] = $token_array['token_type'];
        $_SESSION['expires_in'] = $token_array['expires_in'];
        $_SESSION['created'] = $token_array['created'];
        $this->client->setAccessToken($token_array['access_token']);
    }
    public function unsetTokens()
    {
        unset($_SESSION['access_token']);
        unset($_SESSION['token_type']);
        unset($_SESSION['expires_in']);
        unset($_SESSION['id_token']);
        unset($_SESSION['created']);
    }
    public function bindToken()
    {
        $token = $_SESSION['access_token'];
        $this->client->setAccessToken($token);
    }
    public function setToken(string $token)
    {
        $_SESSION['access_token'] = $token;
        $this->client->setAccessToken($token);
    }
    public function getTokens($code, $state)
    {
        return $this->client->authenticate($code);
    }
    public function createBroadcast($title, $scheduledStartTime, $privacy, $description = null,  $scheduledEndTime =null)
    {
        // Inserting a broadcast
        $part = "id,snippet,contentDetails,status";

        $snippet = new \Google_Service_YouTube_LiveBroadcastSnippet;
        $snippet->setTitle($title);

        $snippet->setScheduledStartTime(date("c", strtotime($scheduledStartTime)));
        if ($description) {
            $snippet->setDescription($description);
        }
        if ($scheduledEndTime) {
            $snippet->setScheduledEndTime($scheduledEndTime);
        }


        $status = new \Google_Service_YouTube_LiveBroadcastStatus;
        $status->setPrivacyStatus($privacy);


        $monitorStream = new \Google_Service_YouTube_MonitorStreamInfo;
        $monitorStream->setBroadcastStreamDelayMs(0);
        $monitorStream->setEnableMonitorStream(true);

        $contentDetails = new \Google_Service_YouTube_LiveBroadcastContentDetails;
        $contentDetails->setMonitorStream($monitorStream);
        $contentDetails->setEnableDvr(true);
        $contentDetails->setEnableContentEncryption(true);
        $contentDetails->setEnableEmbed(true);
        $contentDetails->setStartWithSlate(true);
        $contentDetails->setEnableClosedCaptions(true);
        $contentDetails->setRecordFromStart(true);


        $liveBroadcast = new \Google_Service_YouTube_LiveBroadcast;
        $liveBroadcast->setSnippet($snippet);
        $liveBroadcast->setStatus($status);
        $liveBroadcast->setContentDetails($contentDetails);
        try {
            $liveBroadcast =$this->broadcasts->insert($part, $liveBroadcast);
        } catch (Google_Service_Exception $e) {                   
            throw new \Exception(json_decode($e->getMessage())->error->message);
        } catch (Google_Exception $e) {
            throw new \Exception(json_decode($e->getMessage())->error->message);
        }
        return $liveBroadcast;
    }
    public function createStream($title, $description = "",  $resolution = "720p", $ingestion_type = "rtmp", $frameRate = "30fps")
    {
        $part = "id,snippet,contentDetails,status, cdn";
        $snippet = new \Google_Service_YouTube_LiveStreamSnippet;
        $snippet->setTitle($title);
        if ($description) {
            $snippet->setDescription($description);
        }
        
        $cdnSettings = new \Google_Service_YouTube_CdnSettings;       
        $cdnSettings->setFrameRate($frameRate);
        $cdnSettings->setIngestionType($ingestion_type);
        $cdnSettings->setResolution($resolution);
        
        $liveStream = new \Google_Service_YouTube_LiveStream();
        $liveStream->setCdn($cdnSettings);
        $liveStream->setSnippet($snippet);
        try {
            $liveStream = $this->streams->insert($part, $liveStream);
        } catch (Google_Service_Exception $e) {                   
            throw new \Exception(json_decode($e->getMessage())->error->message);
        } catch (Google_Exception $e) {
            throw new \Exception(json_decode($e->getMessage())->error->message);
        }
        return $liveStream;
    }
    public function bindBroadcastToStream(\Google_Service_YouTube_LiveBroadcast $broadcast, \Google_Service_YouTube_LiveStream $stream)
    {
        $part = "id,snippet,contentDetails,status";
        try {
            $liveBroadcast =$this->broadcasts->bind($broadcast->getId(), $part, ['streamId' => $stream->getId()]);
        } catch (Google_Service_Exception $e) {                   
            throw new \Exception(json_decode($e->getMessage())->error->message);
        } catch (Google_Exception $e) {
            throw new \Exception(json_decode($e->getMessage())->error->message);
        }
        return $liveBroadcast;
    }
    public function transitionBroadcast(Google_Service_YouTube_LiveBroadcast $broadcast,$state)
    {
        $part = "id,snippet,contentDetails,status";
        try {
            $liveBroadcast =$this->broadcasts->transition($state, $broadcast->getId(), $part);
        } catch (Google_Service_Exception $e) {                   
            throw new \Exception(json_decode($e->getMessage())->error->message);
        } catch (Google_Exception $e) {
            throw new \Exception(json_decode($e->getMessage())->error->message);
        }
        return $liveBroadcast;
    }    

}