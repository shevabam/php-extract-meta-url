<?php

namespace PhpExtractMetaUrl;

class PhpExtractMetaUrl
{ 
    public $url = null;
    public $curlDatas = null;
    public $datas = null;
    public $format = 'json'; // json | array

    public static $CURL_OPTS = [
        CURLOPT_CONNECTTIMEOUT  => 15,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_SSL_VERIFYPEER  => false,
        CURLOPT_SSL_VERIFYHOST  => false,
        CURLOPT_TIMEOUT         => 30,
        CURLOPT_HEADER          => false,
        CURLOPT_USERAGENT       => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36',
        CURLOPT_ENCODING        => 1,
    ];

    private $dom = null; 

    private $rss_types = [
        'application/rss+xml',
        'application/atom+xml',
        'application/rdf+xml',
        'application/rss',
        'application/atom',
        'application/rdf',
        'text/rss+xml',
        'text/atom+xml',
        'text/rdf+xml',
        'text/rss',
        'text/atom',
        'text/rdf',
    ];



    public function __construct(string $url)
    { 
        $this->url = $url;

        if ($this->isUrlValid() === false)
        {
            throw new \Exception("The URL provided is incorrect");
        }
        
        $this->datas['website'] = $this->getSiteDatas();

        $this->curlGetContent();

        $this->initializeDom();

        $this->datas['title'] = $this->getTitle();

        $this->datas['description'] = $this->getDescription();

        $this->datas['keywords'] = $this->getKeywords();

        $this->datas['image'] = $this->getImage();

        $this->datas['favicon'] = $this->getFavicon();        

        $this->datas['rss'] = $this->getRss();

    }

    public function setFormat(string $format)
    {
        $this->format = $format;
    }

    public function extract()
    {
        return $this->format == 'json' ? json_encode($this->datas) : $this->datas;
    }

    public function isUrlValid()
    {
        return \filter_var($this->url, FILTER_VALIDATE_URL);
    }


    private function initializeDom()
    {
        $this->dom = new \DOMDocument();
        @$this->dom->loadHTML($this->curlDatas);

        return $this->dom;
    }

    public function getSiteDatas()
    {
        $parseUrl = \parse_url($this->url);

        return [
            'url' => $this->url,
            'scheme' => $parseUrl['scheme'],
            'host' => $parseUrl['host'],
        ];
    }

    public function getTitle()
    {
        $titleNode = $this->dom->getElementsByTagName("title");
        $titleValue = $titleNode->item(0)->nodeValue;

        return $titleValue;
    }

    public function getDescription()
    {
        $xpath = new \DOMXpath($this->dom);
        $nodes = $xpath->query('//head/meta[@name="description"]');

        if (count($nodes) > 0 && !empty($nodes->item(0)->getAttribute('content')))
            return $nodes->item(0)->getAttribute('content');
    }

    public function getKeywords()
    {
        $xpath = new \DOMXpath($this->dom);
        $nodes = $xpath->query('//head/meta[@name="keywords"]');

        if (count($nodes) > 0 && !empty($nodes->item(0)->getAttribute('content')))
        {
            return \array_map(function($k) { return \trim($k); }, \explode(',', $nodes->item(0)->getAttribute('content')));
        }
    }

    public function getImage()
    {
        // Check if meta og:image exists
        $xpath = new \DOMXpath($this->dom);
        $nodes = $xpath->query('//head/meta[@property="og:image"]');

        if (count($nodes) > 0 && !empty($nodes->item(0)->getAttribute('content')))
        {
            return $nodes->item(0)->getAttribute('content');
        }

        // Check if meta twitter:image exists
        $xpath = new \DOMXpath($this->dom);
        $nodes = $xpath->query('//head/meta[@name="twitter:image"]');

        if (count($nodes) > 0 && !empty($nodes->item(0)->getAttribute('content')))
        {
            return $nodes->item(0)->getAttribute('content');
        }
    }

    public function getFavicon()
    {
        $xpath = new \DOMXpath($this->dom);
        $nodes = $xpath->query('//head/link[@rel="icon" or @rel="shortcut icon"]');

        if (count($nodes) > 0 && !empty($nodes->item(0)->getAttribute('href')))
            return $nodes->item(0)->getAttribute('href');
    }

    public function getRss()
    {
        $xpath = new \DOMXpath($this->dom);
        $links = $xpath->query('//head/link[@rel="alternate"]');

        $rss = [];
        foreach ($links as $link)
        {
            $link_type = $link->getAttribute('type');

            if (\in_array($link_type, $this->rss_types))
            {
                $feed_url = $link->getAttribute('href');

                $rss[] = $feed_url;
            }
        }

        return $rss;
    }
     

    private function curlGetContent()
    {
        $ch = \curl_init();

        $opts = self::$CURL_OPTS;
        $opts[CURLOPT_URL] = $this->url;
        
        \curl_setopt_array($ch, $opts);

        $response = \curl_exec($ch);
        
        if (!\curl_exec($ch))
        {
            throw new \Exception('cURL error: "'.\curl_error($ch).'" - Code: '. \curl_errno($ch));
        }

        $httpCode = \curl_getinfo($ch, CURLINFO_HTTP_CODE);

        \curl_close($ch);

        if (!empty($response) && 20 === intval(\substr($httpCode, 0, 2)))
            $this->curlDatas = $response;
    }
}


