<?php
declare(strict_types=1);

namespace Mohrekopp\MailHogClient\Model\Message;

use Mohrekopp\MailHogClient\Model\MailAddress;

/**
 * Class Message.
 *
 * @author Chinthujan Sehasothy <chinthu@madco.de>
 */
class Message
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var MailAddress
     */
    private $from;

    /**
     * @var MailAddress[]
     */
    private $to;

    /**
     * @var Content
     */
    private $content;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var string
     */
    private $mime;

    /**
     * @var RawData
     */
    private $raw;

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
     * Message constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->id = $data['ID'];
        $this->from = new MailAddress($data['From']);
        $this->content = new Content($data['Content']);
        $this->created = \DateTime::createFromFormat('Y-m-dTH:M:S.fz', $data['Created']);
        $this->mime = $data['MIME'];
        $this->raw = new RawData($data['Raw']);

        foreach ($data['To'] as $to) {
            $this->to[] = new MailAddress($to);
        }
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return MailAddress
     */
    public function getFrom(): MailAddress
    {
        return $this->from;
    }

    /**
     * @return MailAddress[]
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @return Content
     */
    public function getContent(): Content
    {
        return $this->content;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime;
    }

    /**
     * @return RawData
     */
    public function getRaw(): RawData
    {
        return $this->raw;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->getContent()->getHeaders()->getSubject();
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->getContent()->getBody();
    }
}
