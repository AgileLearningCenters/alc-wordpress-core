<?php

namespace TeamBooking;
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class EmailHandler
 *
 * @author VonStroheim
 */
class EmailHandler
{
    private $subject;
    private $body;
    private $to;
    private $headers;
    private $attachments;
    private $from_address;
    private $from_name;

    public function __construct()
    {
    }

    /**
     * @param      $address
     * @param null $name
     */
    public function setFrom($address, $name = NULL)
    {
        if (NULL === $name || empty($name)) {
            $this->from_address = $address;
        } else {
            $this->from_name = Toolkit\unfilterInput($name);
            $this->from_address = $address;
        }
    }

    /**
     * @param string $text
     */
    public function setSubject($text)
    {
        $this->subject = Toolkit\unfilterInput($text);
    }

    /**
     * @param string $text
     */
    public function setBody($text)
    {
        $this->body = Toolkit\unfilterInput($text);
    }

    /**
     * @param string      $address
     * @param null|string $name
     */
    public function setReplyTo($address, $name = NULL)
    {
        if (NULL === $name) {
            if (!empty($address)) {
                $this->headers[] = 'Reply-To: ' . $address;
            }
        } else {
            if (!empty($address)) {
                $this->headers[] = 'Reply-To: ' . Toolkit\unfilterInput($name) . ' <' . $address . '>';
            }
        }
    }

    /**
     * @param string      $address
     * @param null|string $name
     */
    public function setCc($address, $name = NULL)
    {
        if (NULL === $name) {
            $this->headers[] = 'Cc: ' . $address;
        } else {
            $this->headers[] = 'Cc: ' . Toolkit\unfilterInput($name) . ' <' . $address . '>';
        }
    }

    /**
     * @param string      $address
     * @param null|string $name
     */
    public function setBcc($address, $name = NULL)
    {
        if (NULL === $name) {
            $this->headers[] = 'Bcc: ' . $address;
        } else {
            $this->headers[] = 'Bcc: ' . Toolkit\unfilterInput($name) . ' <' . $address . '>';
        }
    }

    /**
     * @param string $address
     */
    public function setTo($address)
    {
        $this->to[] = $address;
    }

    /**
     * @param string $path
     */
    public function setAttachment($path)
    {
        $this->attachments[] = $path;
    }

    /**
     * @return string
     */
    public function setHtmlContentType()
    {
        return 'text/html';
    }

    /**
     * @return bool
     */
    public function send()
    {
        $return = FALSE;
        add_filter('wp_mail_content_type', array(
            $this,
            'setHtmlContentType',
        ));

        // From filters
        if (!empty($this->from_address)) {
            add_filter('wp_mail_from', array($this, 'getFromAddress'));
        }
        if (!empty($this->from_name)) {
            add_filter('wp_mail_from_name', array($this, 'getFromName'));
        }

        // Send email
        if (isset($this->attachments)) {
            if (wp_mail($this->to, $this->subject, $this->body, $this->headers, $this->attachments)) {
                $return = TRUE;
            }
        } else {
            if (wp_mail($this->to, $this->subject, $this->body, $this->headers)) {
                $return = TRUE;
            }
        }

        // Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
        remove_filter('wp_mail_content_type', 'setHtmlContentType');

        // Reset from filters
        if (!empty($this->from_address)) {
            remove_filter('wp_mail_from', array($this, 'getFromAddress'));
        }
        if (!empty($this->from_name)) {
            remove_filter('wp_mail_from_name', array($this, 'getFromName'));
        }

        return $return;
    }

    /**
     * @return string
     */
    public function getFromAddress()
    {
        return $this->from_address;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->from_name;
    }

}
