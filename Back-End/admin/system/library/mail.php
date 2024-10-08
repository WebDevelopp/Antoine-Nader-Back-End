<?php 
class Mail {
	protected $to;
  	protected $from;
  	protected $sender;
  	protected $subject;
  	protected $text;
  	protected $html;
  	protected $attachments = array();

	public function setTo($to) {
    	$this->to = $to;
  	}
 
  	public function setFrom($from) {
    	$this->from = $from;
  	}
 
  	public function setSender($sender) {
    	$this->sender = $sender;
  	}
 
  	public function setSubject($subject) {
    	$this->subject = $subject;
  	}

	public function setText($text) {
    	$this->text = $text;
  	}

  	public function setHtml($html) {
    	$this->html = $html;
  	}
	
  	public function addAttachment($attachments, $embed = FALSE) {
    	$this->attachments[] = $attachments;
  	}

  	public function send() {	
    	if (!$this->to) {
      		exit('Error: E-Mail to required!');
    	}
	
    	if (!$this->from) {
      		exit('Error: E-Mail from required!');
    	}
    
    	if (!$this->sender) {
      		exit('Error: E-Mail sender required!');
    	}
		
		if (!$this->subject) {
      		exit('Error: E-Mail subject required!');
    	}
			
		if ((!$this->text) && (!$this->html)) {
      		exit('Error: E-Mail message required!');
    	}

		if (is_array($this->to)) {
      		$to = implode($this->to, ',');
    	} else {
      		$to = $this->to;
    	}
	  	
		$boundary = '----=_NextPart_' . md5(rand());  
	    
		if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) { 
      		$eol = "\r\n"; 
    	} elseif (strtoupper(substr(PHP_OS, 0, 3)=='MAC')) { 
      		$eol = "\r"; 
    	} else { 
      		$eol = "\n"; 
    	} 	
		
		$headers  = 'From: ' . $this->sender . '<' . $this->from . '>' . $eol; 
    	$headers .= 'Reply-To: ' . $this->sender . '<' . $this->from . '>' . $eol;   
    	$headers .= 'X-Mailer: PHP/' . phpversion() . $eol;  
    	$headers .= 'MIME-Version: 1.0' . $eol; 
    	$headers .= 'Content-Type: multipart/mixed; boundary="' . $boundary . '"' . $eol . $eol;  
	
		if (!$this->html) {
	  		$message  = '--' . $boundary . $eol;  
	  		$message .= 'Content-Type: text/plain; charset="utf-8"' . $eol; 
	  		$message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
      		$message .= chunk_split(base64_encode($this->text));
		} else {
	  		$message  = '--' . $boundary . $eol;
	  		$message .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '_alt"' . $eol . $eol;
	  		$message .= '--' . $boundary . '_alt' . $eol;
	  		$message .= 'Content-Type: text/plain; charset="utf-8"' . $eol; 
	  		$message .= 'Content-Transfer-Encoding: base64' . $eol;
	  
	  		if ($this->text) {
        		$message .= chunk_split(base64_encode($this->text));
	  		} else {
	    		$message .= chunk_split(base64_encode('This is a HTML email and your email client software does not support HTML email!'));
      		}	
	  
	  		$message .= '--' . $boundary . '_alt' . $eol;
      		$message .= 'Content-Type: text/html; charset="utf-8"' . $eol; 
      		$message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
	  		$message .= chunk_split(base64_encode($this->html)); 
			$message .= '--' . $boundary . '_alt' . $eol;		 
		}
		
    	foreach ($this->attachments as $attachment) {  
      		$filename = basename($attachment);  
      		$handle   = fopen($attachment, 'r'); 
      		$content  = fread($handle, filesize($attachment));
      
	  		fclose($handle);  
	  
      		$message .= '--' . $boundary . $eol;
      		$message .= 'Content-Type: application/octetstream' . $eol;    
      		$message .= 'Content-Transfer-Encoding: base64' . $eol; 
      		$message .= 'Content-Disposition: attachment; filename="' . $filename . '"' . $eol; 
      		$message .= 'Content-ID: <' . $filename . '>' . $eol . $eol;
      		$message .= chunk_split(base64_encode($content));
    	}  

    	mail($to, $this->subject, $message, $headers);  
	} 
}
?>