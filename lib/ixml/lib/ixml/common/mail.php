<?php
namespace Zeyon\Mail;

register_shutdown_function('imap_alerts');
register_shutdown_function('imap_errors');

// -------------------- Implementation --------------------

class Fetch {
  protected $imap;
  protected $count;
  protected $index = 0;

  public function __construct($mailbox, $username, $password, $timeout = 30) {
    imap_timeout(IMAP_OPENTIMEOUT, $timeout);
    imap_timeout(IMAP_READTIMEOUT, $timeout);
    imap_timeout(IMAP_WRITETIMEOUT, $timeout);

    try {
      $this -> count = imap_num_msg( $this -> imap = imap_open(\Zeyon\convert($mailbox, 'UTF7-IMAP'), \Zeyon\convert($username, 'UTF7-IMAP'), \Zeyon\convert($password, 'UTF7-IMAP'), OP_SILENT, 1) );
    } catch (\Exception $e) {
      throw new \Exception('IMAP: '.(( $error = imap_last_error() ) === false ? $e -> getMessage() : $error));
    }
  }

  public function reset($limit = 0) {
  	$this -> index = $limit > 0 ? max(0, $this -> count - $limit) : 0;
  }

  public function next() {
  	return ++$this -> index <= $this -> count;
  }

  public function fetch() {
  	return [imap_fetchheader($this -> imap, $this -> index, FT_INTERNAL | FT_PREFETCHTEXT), imap_body($this -> imap, $this -> index, FT_INTERNAL)];
  }

  public function fetchHeader() {
  	return imap_fetchheader($this -> imap, $this -> index, FT_INTERNAL);
  }

  public function fetchBody() {
  	return imap_body($this -> imap, $this -> index, FT_INTERNAL);
  }

  public function delete() {
  	imap_delete($this -> imap, $this -> index);
  }

  public function close($expunge = true) {
  	imap_alerts();
  	imap_errors();
    imap_close($this -> imap, $expunge ? CL_EXPUNGE : 0);
  }
}

class Smtp {
	protected $socket;

	public function __construct($host, $port, $security, $timeout = 30) {
    stream_set_timeout( $this -> socket = fsockopen($security === 'ssl' ? "ssl://$host" : $host, $port, $errno, $errstr, $timeout) , $timeout);

		$this -> get();
		$this -> hello();

    if ($security === 'tls')
    	if (
    	  $this -> set('STARTTLS') == 220 &&
    	  stream_socket_enable_crypto($this -> socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)
    	)
    	  $this -> hello();
    	else
    		$this -> set('RSET', 250);
	}

	public function auth($username, $password) {
    $this -> set('AUTH LOGIN', 334);
    $this -> set(base64_encode($username), 334);
    $this -> set(base64_encode($password), 235);
	}

	public function send($from, $recipients, $data) {
		$this -> set("MAIL FROM:<$from>", 250);

		foreach ((array) $recipients as $recipient)
			$this -> set("RCPT TO:<$recipient>", [250, 251]);

		$this -> set('DATA', 354);

		if ($data != '') {
  		$lines = [];
  		$inheader = true;

  		foreach (explode("\n", \Zeyon\normalizeLineBreaks($data)) as $line) {
  			$line === '' AND $inheader = false;

  			while (strlen($line) > 998) {
  	      if (( $pos = strrpos(substr($line, 0, 998), ' ') ) === false) {
  	        $lines[] = substr($line, 0, 997);
  	        $line = substr($line, 997);
  	      } else {
  	        $lines[] = substr($line, 0, $pos);
  	        $line = substr($line, $pos + 1);
  	      }

  	      $inheader AND $line = "\t$line";
  	    }

  	    $lines[] = $line;
  		}

  		foreach ($lines as $line) {
  			$line != '' AND $line[0] === '.' AND $line = ".$line";
  			fputs($this -> socket, "$line\r\n");
  		}
		}

		$this -> set("\r\n.", 250);
	}

	public function close() {
		$this -> set('QUIT', 221);
		fclose($this -> socket);
	}

  protected function set($line, $check = []) {
  	fputs($this -> socket, "$line\r\n");

    $code = substr( $response = $this -> get() , 0, 3);

    if (! $check = (array) $check )
      return $code;

    if (!in_array($code, $check))
      throw new \Exception($response);
  }

  protected function get() {
    $data = '';

    while ( $line = fgets($this -> socket, 515) ) {
      $data .= $line;

      if (substr($line, 3, 1) === ' ')
        break;
    }

    return $data;
  }

  protected function hello() {
  	$server_name = \Zeyon\initArrayVal($_SERVER, 'SERVER_NAME', \Zeyon\TYPE_STRING_NE, 'localhost');

  	$this -> set("EHLO $server_name") == 250 || $this -> set("HELO $server_name", 250);
  }

  public static function sendMail($mail, $servers = [], $mailingto = null) {
    $recipients = [];

    foreach (Mail::parseAddressList(( $mailing = $mailingto !== null ) ? $mailingto : "$mail->to,$mail->cc,$mail->bcc") as $address)
      stripos($address[1], Mail::INTERNAL_HOST) === false AND $recipients[strtolower("$address[0]@$address[1]")] = true;

  	if ($recipients) {
			if (!$servers)
			  throw new \Exception('SMTP: No servers available');

			$recipients = array_keys($recipients);
			$rawmessage = $mail -> createMessage($mailing ? 2 : 1);

			foreach ($servers as $server)
				try {
          list($host, $port, $security) = extractServer($server[0], 25);

          $smtp = new self($host, $port, $security);
          $server[1] == '' || $smtp -> auth($server[1], $server[2]);
          $smtp -> send($mail -> sender_email, $recipients, $rawmessage);
				  $smtp -> close();
				  return;
				} catch (\Exception $e) {
				  $msg = "SMTP $server[0]: ".$e -> getMessage();
				}

		  throw new \Exception($msg);
  	}
  }

  public static function checkConnectivity($host, $port, $security, $username, $password) {
    $smtp = new self($host, $port, $security, 10);
    $username != '' && $smtp -> auth($username, $password);
    $smtp -> close();
  }
}

class Part {
  public $boundary = '';
  public $charset = '';
  public $contentid = '';
  public $contenttype = '';
  public $disposition = '';
  public $encoding = '';
  public $filename = '';
  public $rawheader = '';
  public $rawbody = '';
  public $text;
  public $html;
  public $attachments = [];

  public function __construct($rawpart = '') {
  	( $rawpart = trim($rawpart) ) === '' || $this -> parse($rawpart);
  }

  protected function parse($rawpart) {
  	if (( $pos = strpos( $rawpart = \Zeyon\normalizeLineBreaks($rawpart) , "\n\n") ) === false) {
      $this -> rawheader = $rawpart;
      $this -> rawbody = '';
  	} else {
      $this -> rawheader = substr($rawpart, 0, $pos);
      $this -> rawbody = substr($rawpart, $pos + 2);
  	}

  	unset($rawpart); // Early memory reclamation

  	$this -> contentid = self::extractHeaderField($this -> rawheader, 'Content-ID');

  	if (( $contenttype = self::extractHeaderField($this -> rawheader, 'Content-Type') ) !== '') {
  	  $headerline = self::splitHeaderField($contenttype);
  	  $headerline[0] === '' OR $this -> contenttype = strtolower($headerline[0]);
  	  isset($headerline[1]['boundary']) AND $this -> boundary = $headerline[1]['boundary'];
  	  isset($headerline[1]['charset']) AND $this -> charset = strtoupper($headerline[1]['charset']);
      isset($headerline[1]['name']) AND $this -> filename = self::decodeHeader($headerline[1]['name']);
  	}

    $this -> encoding = strtolower(self::extractHeaderField($this -> rawheader, 'Content-Transfer-Encoding'));

  	if (
  	  strpos($this -> contenttype, 'multipart/') !== 0 &&
  	  ( $contentdisposition = self::extractHeaderField($this -> rawheader, 'Content-Disposition') ) !== ''
  	) {
	    $headerline = self::splitHeaderField($contentdisposition);
	    $this -> disposition = strtolower($headerline[0]);
	    isset($headerline[1]['filename']) AND $headerline[1]['filename'] !== '' AND $this -> filename = self::decodeHeader($headerline[1]['filename']);
	  }

  	if ($this -> filename != '' || $this -> disposition === 'attachment')
  	  $this -> attachments[] = $this;
  	else
  	  switch ($this -> contenttype) {
  	  	case 'application/xhtml+xml':
  	  	case 'text/html':
          $this -> html = $this;
          break;

  	  	case 'message/rfc822':
  	  	  $mail = new Mail($this -> getDecoded());
  	  	  $this -> text = $mail -> text;
  	  	  $this -> html = $mail -> html;
          $this -> attachments = $mail -> attachments;
  	  	  break;

  	  	case 'multipart/alternative':
          foreach (( $parts = self::splitMultipart($this -> getDecoded(), $this -> boundary) ) as $mail)
            if ($mail -> text && $mail -> html && $mail -> attachments) {
        	    $this -> text = $mail -> text;
        	    $this -> html = $mail -> html;
              $this -> attachments = $mail -> attachments;
              break 2;
            }

          foreach ($parts as $mail)
            if ($mail -> html && $mail -> attachments) {
        	    $this -> html = $mail -> html;
              $this -> attachments = $mail -> attachments;
              break 2;
            }

          foreach ($parts as $mail)
            if ($mail -> text && $mail -> attachments) {
        	    $this -> text = $mail -> text;
              $this -> attachments = $mail -> attachments;
              break 2;
            }

          foreach ($parts as $mail)
            if ($mail -> text) {
              $this -> text = $mail -> text;

              if ($this -> text -> contenttype === 'text/plain')
                break;
            }

          foreach ($parts as $mail)
            if ($mail -> html) {
              $this -> html = $mail -> html;
              break;
            }

          foreach ($parts as $mail)
            count($mail -> attachments) > count($this -> attachments) AND $this -> attachments = $mail -> attachments;

  	  	  break;

  	  	default:
  	      if ($this -> contenttype == '' || strpos($this -> contenttype, 'text/') === 0)
  	        $this -> text = $this;
  	      else if (strpos($this -> contenttype, 'multipart/') === 0)
            foreach (self::splitMultipart($this -> getDecoded(), $this -> boundary) as $mail) {
              $mail -> text AND !$this -> text || $this -> text -> contenttype !== 'text/plain' AND $this -> text = $mail -> text;
              $mail -> html AND !$this -> html AND $this -> html = $mail -> html;
              $this -> attachments = array_merge($this -> attachments, $mail -> attachments);
            }
          else
            $this -> attachments[] = $this;
  	  }
  }

  public function setEncoded($string) {
  	switch ($this -> encoding) {
  	  case 'quoted-printable':
    		$this -> rawbody = quoted_printable_encode($string);
    		break;

  	  case 'base64':
  	  	$this -> rawbody = chunk_split(base64_encode($string));
  	  	break;

  	  default:
  	  	$this -> rawbody = $string;
  	}
  }

  public function setEncodedCharset($string, $charset = 'UTF-8') {
  	$this -> setEncoded(\Zeyon\convert($string, $this -> charset, $charset));
  }

  public function getDecoded() {
  	switch ($this -> encoding) {
  	  case 'quoted-printable':
  		  return quoted_printable_decode($this -> rawbody);

  	  case 'base64':
  	  	return base64_decode($this -> rawbody);
  	}

    return $this -> rawbody;
  }

  public function getDecodedCharset($charset = 'UTF-8') {
  	return \Zeyon\convert($this -> getDecoded(), $charset, $this -> charset);
  }

  public function extractContent($preferhtml = true, $charset = 'UTF-8') {
    if ($this -> html AND $preferhtml || !$this -> text)
      return [true, preg_replace_callback('/cid:([^"\'\s]+)/', function($matches) {
        $id = '<'.rawurldecode($matches[1]).'>';

        foreach ($this -> attachments as $mail)
          if ($mail -> contentid === $id)
            return "data:$mail->contenttype;base64,".($mail -> encoding === 'base64' ? $mail -> rawbody : base64_encode($mail -> getDecoded()));

        return $matches[0];
      }, $this -> html -> getDecodedCharset($charset))];

    return [false, $this -> text ? rtrim($this -> text -> getDecodedCharset($charset)) : ''];
  }

  public function extractAttachments($content = true) {
    $attachments = $this -> attachments;

    foreach ($attachments as &$attach) {
      $mail = $attach;
      $attach = [$mail -> filename, $mail -> contenttype];
      $content AND $attach[] = $mail -> getDecoded();
    }

    return $attachments;
  }

  public function toArray() {
  	return [
  	  'boundary' => $this -> boundary,
  	  'charset' => $this -> charset,
  	  'contentid' => $this -> contentid,
  	  'contenttype' => $this -> contenttype,
  	  'disposition' => $this -> disposition,
  	  'encoding' => $this -> encoding,
  	  'filename' => $this -> filename,
  	  'rawheader' => $this -> rawheader,
  	  'rawbody' => $this -> rawbody
  	];
  }

  public static function decodeHeader($string) {
    $decoded = '';

    foreach (imap_mime_header_decode($string) as $elem)
      $decoded .= \Zeyon\convert($elem -> text, 'UTF-8', $elem -> charset == '' || $elem -> charset === 'default' ? 'US-ASCII' : strtoupper($elem -> charset));

    return $decoded;
  }

  public static function encodeHeader($string) {
    return $string == '' || preg_match('/^(?>[^\x0-\x1f\x80-\xff]*)$/D', $string) ? $string : mb_encode_mimeheader($string, 'UTF-8', 'Q');
  }

  protected static function createHeaderField($name, $value, $parameters = []) {
  	if ($value == '')
  	  return '';

    $headerline = "$name: $value";

    foreach ($parameters as $name => $value)
      $value == '' OR $headerline .= ";\r\n\t$name=\"".addcslashes($value, '\\"').'"';

    return "$headerline\r\n";
  }

  protected static function extractHeaderField($string, $name) {
  	return preg_match('/(?:^|\n)'.preg_quote($name, '/').':((?:[^\n]|\n[\t ])*)(?:\n|$)/Di', $string, $matches) ? trim(str_replace(["\n\t", "\n "], ' ', $matches[1])) : '';
  }

  protected static function splitHeaderField($string) {
  	$headers = [trim(strtok($string, ';')), []];

  	if (
  	  ( $parameters = strtok('') ) != '' &&
  	  preg_match_all('/;\s*([^\s=]+)\s*=\s*(?:"((?:\\\\"|[^"])*)"|([^\s;"]+))\s*(?=;)/S', ";$parameters;", $matches, PREG_SET_ORDER)
  	)
  	  foreach ($matches as $match)
  	    $headers[1][strtolower($match[1])] = stripslashes(isset($match[3]) ? $match[3] : $match[2]);

  	return $headers;
  }

  protected static function splitMultipart($string, $boundary) {
  	$parts = [];

  	if (
  	  ( $offset = strpos( $string = "\n$string\n" , "\n--$boundary\n") ) !== false &&
  	  ( $string = substr($string, $offset += strlen($boundary) + 4 , ( $pos = strrpos($string, "\n--$boundary--\n", $offset) ) === false ? -1 : $pos - $offset) ) != ''
  	)
  	  foreach (explode("\n--$boundary\n", $string) as $rawpart)
        $parts[] = new self($rawpart);

    return $parts;
  }
}

class Mail extends Part {
  const INTERNAL_HOST = 'zeyon.internal';

  public $date;
  public $subject = '';
  public $sender = '';
  public $sender_email = '';
  public $sender_name = '';
  public $to = '';
  public $to_email = '';
  public $to_name = '';
  public $to_count = 0;
  public $cc = '';
  public $bcc = '';
  public $replyto = '';
  public $receipt = false;
  public $spam = false;

  public function __construct($rawmessage = '') {
    $this -> date = time();

    parent::__construct($rawmessage);
  }

  protected function parse($rawmessage) {
  	parent::parse($rawmessage);

  	$headers = imap_rfc822_parse_headers($this -> rawheader, self::INTERNAL_HOST);

  	if (isset($headers -> udate))
  	  $this -> date = $headers -> udate;
  	else if (isset($headers -> date) && ( $date = \Zeyon\parseTime($headers -> date) ) !== null)
      $this -> date = $date;

  	isset($headers -> subject) AND $this -> subject = self::decodeHeader($headers -> subject);

  	if (isset($headers -> fromaddress)) {
  		$address = $headers -> from[0];
  	  $this -> sender       = self::decodeHeader($headers -> fromaddress);
  	  $this -> sender_email = isset($address -> mailbox, $address -> host) ? "$address->mailbox@$address->host" : '';
  	  $this -> sender_name  = isset($address -> personal) ? self::decodeHeader($address -> personal) : $this -> sender_email;
  	}

  	if (isset($headers -> toaddress)) {
  	  $address = $headers -> to[0];
  	  $this -> to       = self::decodeHeader($headers -> toaddress);
  	  $this -> to_email = isset($address -> mailbox, $address -> host) ? "$address->mailbox@$address->host" : '';
  	  $this -> to_name  = isset($address -> personal) ? self::decodeHeader($address -> personal) : $this -> to_email;
  	  $this -> to_count = count($headers -> to);
  	}

  	isset($headers -> ccaddress) AND $this -> cc = self::decodeHeader($headers -> ccaddress);
    isset($headers -> bccaddress) AND $this -> bcc = self::decodeHeader($headers -> bccaddress);
    isset($headers -> reply_toaddress) AND $this -> replyto = self::decodeHeader($headers -> reply_toaddress);

    $this -> receipt = self::extractHeaderField($this -> rawheader, 'Disposition-Notification-To') !== '' ||
                       self::extractHeaderField($this -> rawheader, 'Return-Receipt-To') !== '';

  	$this -> spam = strtolower(self::extractHeaderField($this -> rawheader, 'X-Spam-Flag')) === 'yes' ||
  	                preg_match('/^\s*\[spam\]/i', $this -> subject);
  }

  public function createHeader($type = 0) {
  	$rawheader = ($this -> sender_email == '' ? '' : self::createHeaderField('Return-Path', "<$this->sender_email>"))
  	           . self::createHeaderField('From', $from = self::createAddressList($this -> sender) )
               . self::createHeaderField('Date', date('r'))
               . self::createHeaderField('Subject', self::encodeHeader($this -> subject))
               . self::createHeaderField('To', $type == 2 ? $from : self::createAddressList($this -> to))
               . ($type <= 1 ? self::createHeaderField('Cc', self::createAddressList($this -> cc)) : '')
               . ($type == 0 ? self::createHeaderField('Bcc', self::createAddressList($this -> bcc)) : '')
               . self::createHeaderField('MIME-Version', '1.0')
               . self::createHeaderField('X-Mailer', 'Zeyon')
               . self::createHeaderField('Content-Transfer-Encoding', $this -> encoding)
               . self::createHeaderField('Content-Type', $this -> contenttype, strpos($this -> contenttype, 'multipart/') === 0
                   ? ['boundary' => $this -> boundary] : ['charset' => $this -> charset]
                 )
  	           . self::createHeaderField('Content-ID', $this -> contentid);

    $this -> receipt AND $rawheader .= self::createHeaderField('Disposition-Notification-To', $from)
                                    .  self::createHeaderField('Return-Receipt-To', $from);

    return trim($rawheader);
  }

  public function createBody() {
  	if (strpos($this -> contenttype, 'multipart/') !== 0)
  	  return $this -> rawbody;

  	$rawparts = [];

  	foreach ([$this -> text, $this -> html] as $mail)
  	  $mail AND $rawparts[] = self::createHeaderField('Content-Type', $mail -> contenttype, ['charset' => $mail -> charset])
                            . self::createHeaderField('Content-Transfer-Encoding', $mail -> encoding)
                            . "\r\n"
                            . $mail -> rawbody;

  	foreach ($this -> attachments as $mail)
  	  $rawparts[] = self::createHeaderField('Content-Type', $mail -> contenttype, ['charset' => $mail -> charset])
                  . self::createHeaderField('Content-Transfer-Encoding', $mail -> encoding)
                  . self::createHeaderField('Content-Disposition', 'attachment', ['filename' => self::encodeHeader($mail -> filename)])
                  . "\r\n"
                  . $mail -> rawbody;

    return "This is a message with multiple parts in MIME format.\r\n\r\n--$this->boundary\r\n"
         . join("\r\n--$this->boundary\r\n", $rawparts)
         . "\r\n--$this->boundary--";
  }

  public function createMessage($type = 0) {
    return $this -> createHeader($type)."\r\n\r\n".$this -> createBody();
  }

  public function toArrayComplete() {
    $attachments = $this -> attachments;

    foreach ($attachments as &$attach)
      $attach = $attach -> toArray();

  	return parent::toArray() + [
  	  'date' => $this -> date,
  	  'subject' => $this -> subject,
  	  'sender' => $this -> sender,
  	  'sender_email' => $this -> sender_email,
  	  'sender_name' => $this -> sender_name,
  	  'to' => $this -> to,
  	  'to_email' => $this -> to_email,
  	  'to_name' => $this -> to_name,
  	  'to_count' => $this -> to_count,
  	  'cc' => $this -> cc,
  	  'bcc' => $this -> bcc,
  	  'replyto' => $this -> replyto,
  	  'receipt' => $this -> receipt,
  	  'spam' => $this -> spam,
  	  'contenttype' => $this -> contenttype,
  	  'text' => $this -> text ? $this -> text -> toArray() : null,
  	  'html' => $this -> html ? $this -> html -> toArray() : null,
  	  'attachments' => $attachments
  	];
  }

  public static function createAddress($mailbox, $host, $personal) {
  	return imap_rfc822_write_address($mailbox, $host, $personal);
  }

  public static function clipAddress($string) {
    if ( $list = imap_rfc822_parse_adrlist($string, self::INTERNAL_HOST) ) {
     $address = $list[0];

      if (isset($address -> mailbox, $address -> host))
        return "$address->mailbox@$address->host";
    }

    return '';
  }

  public static function parseAddressList($string) {
    $data = [];

    foreach (imap_rfc822_parse_adrlist(strtr($string, ';', ','), self::INTERNAL_HOST) as $address)
      isset($address -> mailbox, $address -> host) AND
      $data[] = [$address -> mailbox, $address -> host, isset($address -> personal) ? $address -> personal : ''];

    return $data;
  }

  public static function createAddressList($string, $delimiter = ",\r\n\t") {
  	$addresses = self::parseAddressList($string);

  	foreach ($addresses as &$address)
  	  $address = self::createAddress($address[0], $address[1], self::encodeHeader($address[2]));

  	return join($delimiter, $addresses);
  }

  public static function fromData($attachments = [], $content = '', $html = false) {
    $mail = new Mail;
    $mail -> charset = 'UTF-8';
    $mail -> contenttype = $html ? 'text/html' : 'text/plain';
    $mail -> encoding = 'quoted-printable';
    $mail -> text = $html ? null : $mail;
    $mail -> html = $html ? $mail : null;
    $mail -> setEncoded($html ? trim($content) : rtrim($content));

    if ($attachments) {
      $mail_multi = new Mail;
      $mail_multi -> boundary = uniqid('', true);
      $mail_multi -> contenttype = 'multipart/mixed';
      $mail_multi -> text = $mail -> text;
      $mail_multi -> html = $mail -> html;

      foreach ($attachments as $attach) {
        list($filename, $contenttype, $content) = $attach;

        $mail_attach = new Part;

        if (strpos($contenttype, 'text/') === 0) {
          $mail_attach -> charset  = 'UTF-8';
          $mail_attach -> encoding = 'quoted-printable';
        } else {
          $contenttype == '' AND $contenttype = 'application/octet-stream';
          $mail_attach -> encoding = 'base64';
        }

        $mail_attach -> contenttype = $contenttype;
        $mail_attach -> disposition = 'attachment';
        $mail_attach -> filename  = $filename;
        $mail_attach -> setEncoded($content);

        $mail_multi -> attachments[] = $mail_attach;
      }

      $mail = $mail_multi;
    }

    return $mail;
  }

  public static function fromTemplate($rawmessage, $type, $refwtemplate, $emails = []) {
  	$mail = new self($rawmessage);
  	list($html, $content) = $mail -> extractContent();

  	$mail_new = self::fromData($type == 2 ? $mail -> extractAttachments() : [], $html ?
  	  \Zeyon\encodeHtml(sprintf($refwtemplate, $mail -> sender, $mail -> to, date('r', $mail -> date), $mail -> subject)).$content :
  	  sprintf($refwtemplate, $mail -> sender, $mail -> to, date('r', $mail -> date), $mail -> subject).($type == 2 ? $content : preg_replace('/(^|\r\n|\r|\n)-- (\r\n|\r|\n).*/s', '', $content, 1))
  	, $html);

    $mail_new -> subject = ($type == 2 ? 'Fw' : 'Re').': '.preg_replace('/^(?:(?:Re|Fw):\s*)+/i', '', trim($mail -> subject), 1);

    if ($type != 2) {
      $senders = self::extractSpecific("$mail->to,$mail->cc", $emails) AND $mail_new -> sender = $senders[0];

      $mail_new -> to = $mail -> replyto == '' ? $mail -> sender : $mail -> replyto;

      if ($type == 0) {
        if ($mail -> to != '') {
          $mail_new -> to == '' OR $mail_new -> to .= ', ';
          $mail_new -> to .= $mail -> to;
        }

        $mail_new -> to = join(', ', self::extractSpecific($mail_new -> to, $emails, true));
        $mail_new -> cc = join(', ', self::extractSpecific($mail -> cc, $emails, true));
      }
    }

    return $mail_new;
  }

  protected static function extractSpecific($string, $emails, $exclusive = false) {
    $emails = array_change_key_case(array_flip($emails), CASE_LOWER);
    $list = [];
    $used = [];

    foreach (self::parseAddressList($string) as $address)
      if (
        !isset($used[ $email = strtolower("$address[0]@$address[1]") ]) &&
        ($exclusive ? !isset($emails[$email]) : isset($emails[$email]))
      ) {
        $list[] = self::createAddress($address[0], $address[1], $address[2]);

        $used[$email] = true;
      }

    return $list;
  }

  public static function extractInternal($string) {
    $list_groups = [];
    $list_users = [];
    $used_groups = [];
    $used_users = [];

    foreach (self::parseAddressList($string) as $address)
      switch (strtolower($address[1])) {
      	case self::INTERNAL_HOST:
      	case 'users.'.self::INTERNAL_HOST:
      	  if (!isset($used_users[ $mailbox = $address[0] ])) {
      	    $list_users[] = $mailbox;

      	    $used_users[$mailbox] = true;
      	  }

      	  break;

      	case 'groups.'.self::INTERNAL_HOST:
      	  if (!isset($used_groups[ $mailbox = $address[0] ])) {
      	    $list_groups[] = $mailbox;

      	    $used_groups[$mailbox] = true;
      	  }

      	  break;
      }

    return [$list_users, $list_groups];
  }
}

function extractServer($server, $defport) {
  if (preg_match('/^(.*?)(?::(\d+))?(?:\/(.+))?$/D', $server, $matches)) {
  	if (isset($matches[2])) {
  	  $port = $matches[2];
  	  $index = 3;
  	} else {
  	  $port = $defport;
  	  $index = 2;
    }

  	$tokens = [$matches[1], $port, '', true];

  	if (isset($matches[$index]))
  	  foreach (explode('/', strtolower($matches[$index])) as $flag)
  	    switch ($flag) {
  	  	  case 'notls':
  	  	  case 'ssl':
  	  	  case 'tls':
  	  	    $tokens[2] = $flag;
  	  	    break;

  	  	  case 'novalidate-cert':
  	  	    $tokens[3] = false;
  	  	    break;

  	  	  case 'validate-cert':
  	  	    $tokens[3] = true;
  	  	    break;
  	    }

  	return $tokens;
  }

  return [$server, $defport, '', true];
}