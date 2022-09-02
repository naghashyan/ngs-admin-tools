<?php
/**
 * MailSender util class for send mail
 * using phpmailler smtp function
 *
 * @author Levon Naghashyan
 * @site   http://naghashyan.com
 * @email  levon@naghashyan.com
 * @year   2016
 **/

namespace ngs\AdminTools\util;

use ngs\templater\NgsSmartyTemplater;
use ngs\templater\NgsTemplater;
use PHPMailer\PHPMailer\PHPMailer;

class MailSender
{

    private $characters = 'UTF-8';
    private $template = null;
    private $params = array();
    private $attachment = array();
    private $from = array();
    private $cc = array();
    private $bcc = array();
    private $recipient = array();
    private $subject = '';


    public function setFrom($mail, $name = '')
    {
        $this->from[] = array('mail' => $mail, 'name' => $name);
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setRecipient(array $mailsArray, $name = '')
    {
        foreach ($mailsArray as $mail) {
            $this->recipient[] = array('mail' => $mail, 'name' => '');
        }
    }

    public function getRecipient()
    {
        return $this->recipient;
    }

    public function setCC($mail, $name = '')
    {
        $this->cc[] = array('mail' => $mail, 'name' => $name);
    }

    public function getCC()
    {
        return $this->cc;
    }

    public function setBCC($mail, $name = '')
    {
        $this->bcc[] = array('mail' => $mail, 'name' => $name);
    }

    public function getBCC()
    {
        return $this->bcc;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setCharacter($characters)
    {
        $this->characters = $characters;
    }

    public function getCharacter()
    {
        return $this->characters;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function addAttachment($path, $name = '')
    {
        $this->attachment[] = array('path' => $path, 'name' => $name);
    }

    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * add multiple parameters
     *
     * @access public
     * @param array $paramsArr
     *
     * @return void
     */
    public final function addParams($paramsArr)
    {
        if (!is_array($paramsArr)) {
            $paramsArr = [$paramsArr];
        }
        $this->params = array_merge($this->params, $paramsArr);
    }

    /**
     * add single parameter
     *
     * @access public
     *
     * @param String $name
     * @param mixed $value
     *
     * @return void
     */
    public final function addParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * this method return
     * assigned parameters
     *
     * @access public
     *
     * @return array
     *
     */
    public function getParams()
    {
        return $this->params;
    }


    public function send($content = '', $html= '') :array
    {
        $mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch

        try {
            $mail->isSMTP();
            $mailConf = NGS()->getConfig()->smtp;
            $mail->XMailer = $mailConf->XMailer;
            $mail->SMTPDebug = 0;
            $mail->SMTPAuth = $mailConf->auth;
            $mail->SMTPSecure = $mailConf->secure;
            $mail->Host = $mailConf->host;
            $mail->Port = $mailConf->port;
            $mail->Username = $mailConf->user;
            $mail->Password = $mailConf->password;

            foreach ($this->getRecipient() as $value) {
                $mail->addAddress($value['mail'], $value['name']);
            }

            foreach ($this->getFrom() as $value) {
                $mail->setFrom($value['mail'], $value['name']);
            }

            foreach ($this->getCC() as $value) {
                $mail->addCC($value['mail'], $value['name']);
            }

            foreach ($this->getBCC() as $value) {
                $mail->addBCC($value['mail'], $value['name']);
            }
            $mail->Subject = $this->getSubject();
            $mail->CharSet = $this->getCharacter();
            if ($html != '') {
                $mail->isHTML(true);
                $mail->msgHTML($html);
                $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
            } else {
                $mail->Body = $content;
            }
            $attachments = $this->getAttachment();
            if ($attachments) {
                foreach ($attachments as $attachment) {
                    $mail->addAttachment($attachment['path'], $attachment['name']);
                }

            }
            //  $mail->AddAttachment('images/phpmailer.gif');      // attachment
            // $mail->AddAttachment('images/phpmailer_mini.gif'); // attachment
            $mail->Send();
            return ['success' => true, 'content' => $html ? $html : $content, 'subject' => $this->getSubject()];
        } catch (\phpmailerException $e) {
            return ['success' => false, 'content' => $html ? $html : $content, 'subject' => $this->getSubject(), 'message' => $e->errorMessage()];
        } catch (\Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
            return ['success' => false, 'content' => $html ? $html : $content, 'subject' => $this->getSubject(), 'message' => $e->getMessage()];
        }

    }
}