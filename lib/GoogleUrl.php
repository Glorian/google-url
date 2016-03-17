<?php

// See License

use Httpful\Proxy;
use Httpful\Request;
use GoogleUrl\GoogleDOM;
use GoogleUrl\Exception;
use GoogleUrl\GoogleLocation;
use GoogleUrl\SimpleProxyInterface;
use GoogleUrl\Exception\CaptchaException;
use Httpful\Exception\ConnectionErrorException;
use GoogleUrl\Exception\EmptySearchRequestException;

/**
 * Description of GoogleUrl
 *
 * @author sghzal
 * @license http://www.freebsd.org/copyright/license.html BSD
 */
class GoogleUrl
{

    /** SEARCH PARAMS CONSTANTS */
    const  PARAM_NBRESULTS = 'num';
    /** END SEARCH PARAMS CONSTANTS */


    /** CONSTANTS OF LANG **/

    /**
     * HL French
     */
    const HL_FR = 'fr';

    /**
     * Lang French
     */
    const LR_FR = 'lang_fr';

    /**
     * French TLD (.fr)
     */
    const TLD_FR = 'fr';

    /**
     * French Accept Flag
     */
    const ACCEPT_FR = 'fr;q=0.8';

    /**
     * HL english
     */
    const HL_EN = 'en';

    /**
     * English language
     */
    const LR_EN = 'lang_en';

    /**
     * English TLD (.com)
     */
    const TLD_EN = 'com';

    /**
     * English Accept Flag
     */
    const ACCEPT_EN = 'en-us,en;q=0.8';

    /**
     * HL GERMAN
     */
    const HL_DE = 'de';

    /**
     * Language German
     */
    const LR_DE = 'lang_de';

    /**
     * TLD German
     */
    const TLD_DE = 'de';

    /**
     * Accept German
     */
    const ACCEPT_DE = 'de;q=0.8';

    /**
     * Hl DUTCH
     */
    const HL_NL = 'nl';

    /**
     * Language DUTCH
     */
    const LR_NL = 'lang_nl';

    /**
     * Tld DUTCH
     */
    const TLD_NL = 'nl';

    /**
     * Accept DUTCH
     */
    const ACCEPT_NL = 'nl;q=0.8';

    /**
     * HL Czech
     */
    const HL_CS = 'cs';

    /**
     * Language Czech
     */
    const LR_CS = 'lang_cs';

    /**
     * Tld Czech
     */
    const TLD_CS = 'com';

    /**
     * Accept Czech
     */
    const ACCEPT_CS = 'cs;q=0.8';

    /**
     * HL Danish
     */
    const HL_DK = 'da';

    /**
     * Language Danish
     */
    const LR_DK = 'lang_da';

    /**
     * Tld Danish
     */
    const TLD_DK = 'dk';

    /**
     * Accept Danish
     */
    const ACCEPT_DK = 'da;q=0.8';

    /**
     * HL Japan
     */
    const HL_JP = 'ja';

    /**
     * Language Japan
     */
    const LR_JP = 'lang_ja';

    /**
     * Tld Japan
     */
    const TLD_JP = 'co.jp';

    /**
     * Accept Japan
     */
    const ACCEPT_JP = 'ja;q=0.8';

    /**
     * HL Spain
     */
    const HL_ES = 'es';

    /**
     * Language Spain
     */
    const LR_ES = 'lang_es';

    /**
     * TLD Spain
     */
    const TLD_ES = 'es';

    /**
     * Accept Spain
     */
    const ACCEPT_ES = 'es;q=0.8';

    /**
     * HL Russian
     */
    const HL_RU = 'ru';

    /**
     * Language Russian
     */
    const LR_RU = 'lang_ru';

    /**
     * TLD Russian
     */
    const TLD_RU = 'ru';

    /**
     * Accept Russian
     */
    const ACCEPT_RU = 'ru;q=0.8';

    /** END CONSTANTS OF LANG **/

    protected $tld;

    /**
     * @var
     */
    protected $acceptLanguage;

    /**
     * @var
     */
    protected $googleParams;

    /**
     * Encoding for parse result
     * @var string
     */
    protected $encoding;

    /**
     * @var array
     */
    protected $userAgents = [];

    /**
     * @var bool
     */
    protected $enableLr = true;

    /**
     * Main Constructor
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Reset all params to default :
     *
     *       "q" => "",                      // Search Query
     *
     *       "start" => 0,                   // First result number
     *
     *       "num" => 10,                    // Number of results per pages
     *
     *       "complete" => 0,                // Suggestion auto
     *
     *       "pws" => 0,                     // Personnal search
     *
     *       "hl" => "en",                   // Interface langage
     *
     *       "lr" => "lang_en",              // Results Langage
     *
     *       TLD => "com"
     */
    public function init()
    {

        $this->googleParams = [
            'q'        => '',                      // Search Query
            'start'    => 0,                   // First result number
            'num'      => 10,                    // Number of results per pages
            'complete' => 0,                // Suggestion auto
            'pws'      => 0,                     // Personnal search
            'hl'       => self::HL_EN,           // Interface langage
            'lr'       => self::LR_EN,          // Results Langage
        ];

        $this->acceptLanguage = self::ACCEPT_EN;
        $this->setTld('com');
        $this->setEncoding('UTF-8');

        $this->setUserAgents([
            'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.1 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0'
        ]);
    }

    /**
     * ask if a language is configured
     * @param string $iso the iso code of the country. e.g  english : "en" , france : "fr"
     * @return boolean true if available
     */
    public static function languageIsAvailable($iso)
    {
        $hl = 'HL_' . strtoupper($iso);

        return defined('self::' . $hl);
    }

    /**
     * Set the lang to the given (iso formated) lang. This will modify the params hl and lr
     * @param string $iso the iso code of the country. e.g  english : "en" , france : "fr"
     * @param boolean $setTld change the tld to matching with the langage. Default to true
     * @return GoogleUrl this instance
     * @throws Exception
     */
    public function setLang($iso, $setTld = true)
    {

        if (self::languageIsAvailable($iso)) {
            $hl = 'HL_' . strtoupper($iso);
            $lr = 'LR_' . strtoupper($iso);
            $accept = 'ACCEPT_' . strtoupper($iso);


            $this->setParam('hl', constant('self::' . $hl));

            if ($this->enableLr) {
                $this->setParam('lr', constant('self::' . $lr));
            } else {
                $this->setParam('lr', null);
            }

            $this->acceptLanguage = constant('self::' . $accept);

            if ($setTld) {
                $tld = 'TLD_' . strtoupper($iso);
                $this->setTld(constant('self::' . $tld));
            }

        } else {
            throw new Exception('Unknown lang ' . $iso );
        }

        return $this;
    }

    /**
     * @param bool $enabled
     */
    public function enableLr($enabled = true)
    {
        $this->enableLr = $enabled;
        if ( ! $this->enableLr) {
            $this->setParam('lr', null);
        }
    }


    /**
     *
     * @param string $tld google tld "com","fr","co.uk"
     * @return $this
     */
    public function setTld($tld)
    {
        $this->tld = trim($tld, ' .');

        return $this;
    }

    /**
     * Set location for search results
     *
     * @param GoogleLocation $location
     * @return $this
     */
    public function setLocation(GoogleLocation $location)
    {
        try {
            return $this->setParam('uule', $location->getParam());
        } catch (Exception $e) {
            return $this;
        }
    }

    /**
     * Set encoding for parse result
     * @param $encoding
     * @return $this
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Set terms to search but doesn't launch the search
     * @param string $search set the string to search
     * @return $this
     */
    public function searchTerm($search)
    {
        return $this->setParam('q', $search);
    }

    /**
     *
     * @param string $name name of the param
     * @param string $value value of the param
     * @return $this
     */
    private function setParam($name, $value)
    {
        if ($value === null) {
            if ($this->googleParams[$name]) {
                unset($this->googleParams[$name]);
            }
        } else {
            $this->googleParams[$name] = $value;
        }

        return $this;
    }

    /**
     * get a param by its name
     * @param string $name the param to get
     * @return string
     */
    private function param($name)
    {
        return $this->googleParams[$name];
    }

    /**
     * check if param isset
     * @param string $name the param to get
     * @return bool
     */
    private function hasParam($name)
    {
        return isset($this->googleParams[$name]);
    }

    /**
     * Set which page to query. Between 0 and 100
     * @param int $n the number of the page. Begins to 0
     * @return $this
     */
    public function setPage($n)
    {
        return $this->setParam('start', $this->param('num') * $n);
    }

    /**
     * @return float
     */
    public function getPage()
    {
        return $this->param('start') / $this->param('num');
    }

    /**
     * Set how many results per page between 1 and 100
     * Will also update the start param to match the page number
     * @param int $n the number of the page. Begins to 0
     * @return $this
     */
    public function setNumberResults($n)
    {
        $page = $this->getPage();

        $this->setParam(self::PARAM_NBRESULTS, $n);
        $this->setPage($page);

        return $this;
    }

    /**
     * @param array $userAgents
     * @return $this
     */
    public function setUserAgents(array $userAgents)
    {
        $this->userAgents = [];

        array_map(function ($userAgent) {

            $this->addUserAgent($userAgent);

        }, $userAgents);

        return $this;
    }

    /**
     * @param string $userAgent
     * @return $this
     */
    public function addUserAgent($userAgent)
    {
        $userAgent = trim($userAgent);

        if ($userAgent) {
            $this->userAgents[] = $userAgent;
        }

        return $this;
    }


    /**
     * @return mixed
     */
    public function getUserAgent()
    {
        $count = count($this->userAgents);

        if ( ! $count) {
            return null;
        }

        return $this->userAgents[mt_rand(0, $count - 1)];
    }


    /**
     * Launch a google Search
     * @param string $searchTerm the string to search. Or if not specified will take the given with ->searchTerm($search)
     * @param SimpleProxyInterface $proxy
     * @return GoogleDOM the Google DOMDocument
     *
     * @throws CaptchaException google detected us as a bot
     * @throws ConnectionErrorException
     * @throws EmptySearchRequestException
     *
     * @internal param array $options Options for the query . available options :
     *                       + proxy : a proxyDefinition item to proxyfy the request
     *                       +
     *                       +
     *
     */
    public function search($searchTerm = null, SimpleProxyInterface $proxy = null)
    {
        if ($searchTerm === null && $this->param('q') === '') {
            throw new EmptySearchRequestException('Nothing to Search');
        }

        // Set search term
        $this->searchTerm($searchTerm);

        // Make request
        $request = Request::get($this->getUrl())
            ->addHeaders([
                'User-Agent'      => $this->getUserAgent(),
                'Accept-Language' => $this->acceptLanguage
            ])
            ->followRedirects(true)
            ->timeout(30);

        // Proxify request
        if ($proxy) {
            $request->useProxy(
                $proxy->getIp(),
                $proxy->getPort(),
                CURLAUTH_ANY,
                $proxy->getLogin(),
                $proxy->getPassword(),
                $proxy->getProxyType() ?: Proxy::HTTP
            );
        }

        // Execute request
        $response = $request->send();

        // Populating document
        $doc = new GoogleDOM(
            $this->param('q'),
            $this->getUrl(),
            $this->getPage(),
            $this->param(self::PARAM_NBRESULTS)
        );

        libxml_use_internal_errors(true);

        $doc->loadHTML(mb_convert_encoding($response->body, 'HTML-ENTITIES', $this->encoding));

        libxml_use_internal_errors(false);
        libxml_clear_errors();

        if ($doc->isCaptcha()) {
            throw new CaptchaException();
        }

        return $doc;
    }

    /**
     * get the generated url
     * @return string the generated url
     */
    public function getUrl()
    {
        return (string) $this;
    }

    /**
     * Same as gerUrl
     * @return string the generated url
     */
    public function __toString()
    {
        return 'https://www.google.' . $this->tld . '/search?' . http_build_query($this->googleParams);
    }

}