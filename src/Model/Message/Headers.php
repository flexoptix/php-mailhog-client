<?php
declare(strict_types=1);

namespace Mohrekopp\MailHogClient\Model\Message;

/**
 * Class Headers.
 *
 * @author Chinthujan Sehasothy <chinthu@madco.de>
 * forked by: Thomas haeny <dev@haeny.de>
 */
class Headers
{
	/**
	 * header conversions for strings in the format: =?<charset>?<encoding>?<data>?=0
	 * e.g. =?utf-8?q?Re=3a=20Support=3a=204D09EE9A=20=2d=20Re=3a=20Support=3a=204D078032=20=2d=20Wordpress=20Plugin?=
	 * e.g. =?utf-8?q?Wordpress=20Plugin?=
	 * based on: https://stackoverflow.com/questions/8626786/proper-way-to-decode-incoming-email-subject-utf-8
	 */
	const rfc2047header = '/=\?([^ ?]+)\?([BQbq])\?([^ ?]+)\?=/';
	const rfc2047header_spaces = '/(=\?[^ ?]+\?[BQbq]\?[^ ?]+\?=)\s+(=\?[^ ?]+\?[BQbq]\?[^ ?]+\?=)/';

	/**
	 * @param string $header
	 * @return bool|string
	 * @throws \Exception
	 */
	public static function decode_header($header)
	{
		$matches = null;

		/* Repair instances where two encodings are together and separated by a space (strip the spaces) */
		$header = preg_replace(self::rfc2047header_spaces, "$1$2", $header);

		/* Now see if any encodings exist and match them */
		if (!preg_match_all(self::rfc2047header, $header, $matches, PREG_SET_ORDER)) {
			return $header;
		}
		foreach ($matches as $header_match) {
			list($match, $charset, $encoding, $data) = $header_match;
			$encoding = strtoupper($encoding);
			switch ($encoding) {
				case 'B':
					$data = base64_decode($data);
					break;
				case 'Q':
					$data = quoted_printable_decode(str_replace("_", " ", $data));
					break;
				default:
					throw new \Exception("preg_match_all is busted: didn't find B or Q in encoding $header");
			}
			// This part needs to handle every charset
			switch (strtoupper($charset)) {
				case "UTF-8":
					break;
				default:
					/* Here's where you should handle other character sets! */
					throw new \Exception("Unknown charset in header - time to write some code.");
			}
			$header = str_replace($match, $data, $header);
		}
		return $header;
	}

	/**
     * @var string
     */
    private $contentTransferEncoding;

    /**
     * @var string
     */
    private $contentType;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $mimeVersion;

    /**
     * @var string
     */
    private $messageId;

    /**
     * @var string
     */
    private $received;

    /**
     * @var string
     */
    private $returnPath;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $to;

    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->contentTransferEncoding = isset($data['Content-Transfer-Encoding']) ? $data['Content-Transfer-Encoding'][0] : null;
        $this->contentType = isset($data['Content-Type']) ? $data['Content-Type'][0] : null;
        $this->date = isset($data['Date']) ? \DateTime::createFromFormat(\DateTime::RFC822, $data['Date'][0]) : null;
        $this->from = isset($data['From']) ? $data['From'][0] : null;
        $this->mimeVersion = isset($data['MIME-Version']) ? $data['MIME-Version'][0] : null;
        $this->messageId = isset($data['Message-ID']) ? $data['Message-ID'][0] : null;
        $this->received = isset($data['Received']) ? $data['Received'][0] : null;
        $this->returnPath = isset($data['Return-Path']) ? $data['Return-Path'][0] : null;
        $this->subject = isset($data['Subject']) ? $data['Subject'][0] : null;
        $this->to = isset($data['To']) ? $data['To'][0] : null;

        $this->subject = self::decode_header($this->subject);
	    $this->from = self::decode_header($this->from);
    }

    /**
     * @return string
     */
    public function getContentTransferEncoding(): string
    {
        return $this->contentTransferEncoding;
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getMimeVersion(): string
    {
        return $this->mimeVersion;
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @return string
     */
    public function getReceived(): string
    {
        return $this->received;
    }

    /**
     * @return string
     */
    public function getReturnPath(): string
    {
        return $this->returnPath;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

}
