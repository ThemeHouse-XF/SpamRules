<?php

/**
 * Helper to manage/check the criteria that are used in spam rules.
 */
class ThemeHouse_SpamRules_Helper_Criteria
{

    /**
     * Determines if the given request matches the criteria.
     *
     * @param array|string $criteria List of criteria, format: [] with keys rule
     * and data; may be serialized
     * @param boolean $matchOnEmpty If true and there's no criteria, true is
     * returned; otherwise, false
     * @param Zend_Controller_Request_Http $request Request to check against
     *
     * @return boolean
     */
    public static function requestMatchesCriteria($criteria, $matchOnEmpty = false,
        Zend_Controller_Request_Http $request = null)
    {
        if (!$criteria = XenForo_Helper_Criteria::unserializeCriteria($criteria)) {
            return (boolean) $matchOnEmpty;
        }

        if (!$request) {
            $request = new Zend_Controller_Request_Http();
        }

        foreach ($criteria as $criterion) {
            $data = $criterion['data'];

            switch ($criterion['rule']) {
                // contains at least x links
                case 'geoip_country':
                    if (!isset($data['countries'])) {
                        return false;
                    }
                    if (!function_exists('geoip_country_code_by_name')) {
                        return false;
                    }
                    try {
                        $country = geoip_country_code_by_name($request->getClientIp(true));
                    } catch (Exception $e) {
                        return false;
                    }
                    if (!in_array($country, $data['countries'])) {
                        return false;
                    }
                    break;

                // user has open port
                case 'open_port':
                    if (empty($data['port'])) {
                        return false;
                    }
                    if (@fsockopen($_SERVER['REMOTE_ADDR'], $data['port'], $errstr, $errno, 1)) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    } /* END requestMatchesCriteria */

    /**
     * Determines if the given content matches the criteria.
     *
     * @param array|string $criteria List of criteria, format: [] with keys rule
     * and data; may be serialized
     * @param boolean $matchOnEmpty If true and there's no criteria, true is
     * returned; otherwise, false
     * @param string $content Content to check against
     *
     * @return boolean
     */
    public static function contentMatchesCriteria($criteria, $matchOnEmpty = false, $content = '')
    {
        if (!$criteria = XenForo_Helper_Criteria::unserializeCriteria($criteria)) {
            return (boolean) $matchOnEmpty;
        }

        foreach ($criteria as $criterion) {
            $data = $criterion['data'];

            switch ($criterion['rule']) {
                // contains at least x links
                case 'contains_links':
                    if (!isset($data['links'])) {
                        return false;
                    }
                    $pattern = '#\[url(?:.*)\[/url\]#Uis';
                    preg_match_all($pattern, $content, $matches);
                    if (count($matches[0]) < $data['links']) {
                        return false;
                    }
                    break;

                // contains specific words
                case 'contains_words':
                    if (!isset($data['words'])) {
                        return false;
                    }
                    $matched = false;
                    foreach ($data['words'] as $word) {
                        if (!empty($word['word'])) {
                            if (!empty($word['exact'])) {
                                if (strpos($content, $word['word']) !== false) {
                                    $matched = true;
                                    break;
                                }
                            } else {
                                if (stripos($content, $word['word']) !== false) {
                                    $matched = true;
                                    break;
                                }
                            }
                        }
                    }
                    if ($matched == false) {
                        return false;
                    }
                    break;

                // contains uppercase
                case 'contains_uppercase':
                    if (!isset($data['percent'])) {
                        return false;
                    }
                    $newContent = self::cleanTextForChecks($content);
                    if (!$newContent) {
                        return false;
                    }
                    $capitalLetters = strlen(preg_replace('![^A-Z]+!', '', $newContent));
                    $percent = $capitalLetters / strlen($newContent) * 100;

                    if ($percent < $data['percent']) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    } /* END contentMatchesCriteria */

    /**
     *
     * @param string $content
     * @return string $content with BB codes and excessive spaces removed
     */
    public static function cleanTextForChecks($content)
    {
        if (XenForo_Application::get('options')->th_spamRules_removeQuotes) {
            $content = self::removeQuotesFromText($content);
        }

        $content = preg_replace('/\[[^\]]*\]/', ' ', $content);
        $content = preg_replace('/\s+/', ' ', $content);

        return $content;
    } /* END cleanTextForChecks */

    /**
     *
     * @param string $content
     * @return string $content with quotes removed
     */
    public static function removeQuotesFromText($content)
    {
        $content = strtolower($content);
        $closingTagPosition = strpos($content, '[/quote]');

        if ($closingTagPosition === false) {
            return $content;
        }

        $openingTagPosition = strpos($content, '[quote]');
        $content = substr($content, 0, $openingTagPosition - 1) . substr($content, $closingTagPosition + 8);

        return self::removeQuotesFromText($content);
    } /* END removeQuotesFromText */
}