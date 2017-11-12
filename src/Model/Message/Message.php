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

	const DATE_TIME_FORMATS = array(
		'Y-m-dTH:M:S.fz',
		'D, d M Y H:M:S O'  // e.g. "Tue, 07 Nov 2017 15:22:02 +0000"
	);

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
     * Message constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->id = $data['ID'];
        $this->from = new MailAddress($data['From']);
        $this->content = new Content($data['Content']);
        foreach(self::DATE_TIME_FORMATS as $format) {
        	if($created = \DateTime::createFromFormat($format, $data['Created'])) {
		        $this->created = $created;
		        break;
	        }
        }
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
