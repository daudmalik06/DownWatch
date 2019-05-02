<?php
/**
 * Created by PhpStorm.
 * User: dawood.ikhlaq
 * Date: 02/04/2019
 * Time: 15:39
 */

namespace DownWatch;
use PHPMailer\PHPMailer\PHPMailer;

class Watch
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer;
    }

    public function work()
    {
        $urlToCheck = env('URL_TO_CHECK');
        if(!$urlToCheck)
        {
            print "Please provide url to check, see .env.example file".PHP_EOL;
            die;
        }
        $criticalReportNumber = env('CRITICAL_REPORT_NUMBER', 50);
        $str = file_get_contents($urlToCheck);

        preg_match("/data\: \[(.*?)\]/sm",$str,$match);
        $data = trim($match[1]);
        $data[strlen($data)-1] = " ";
        $data = trim($data);
        $replace = [
            'date'=>'"date"',
            'value'=>'"value"',
            "'"=>'"',
        ];
        $data = '['.strtr($data,$replace).']';
        $data = json_decode($data,true);

        $maxReports = 0;
        foreach($data as $stat)
        {
            if($stat['value'] > $maxReports)
            {
                $maxReports = $stat['value'];
            }
        }
        $outputDir = realpath(__DIR__.'/../Output/');
        $peakFile = $outputDir.date('Ymd').'_'.$maxReports;
        if($maxReports > $criticalReportNumber and !file_exists($peakFile))
        {
            //delete all old files
            array_map('unlink', glob($outputDir."/*"));
            file_put_contents($peakFile,'');
            print "Max reports: {$maxReports} are greater than critical: {$criticalReportNumber}".PHP_EOL;
            $this->sendEmailToAdmin($maxReports);
        }
    }


    /**
     * @param $maxReports
     */
    private function sendEmailToAdmin($maxReports)
    {
        if(!env('SEND_MAIL'))
        {
            return ;
        }
        //Create a new PHPMailer instance
        $mail = $this->mailer;
        $mail->isSMTP();
        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $debug = env('DEBUG') === true ? 2 : 0;
        $mail->SMTPDebug = $debug;

        //Set the hostname of the mail server
        $mail->Host = env('SMTP','smtp.gmail.com');
        $mail->Port = env('SMTP_PORT',587);
        //Set the encryption system to use - ssl (deprecated) or tls
        $mail->SMTPSecure =  env('SMTP_ENCRYPTION','tls');

        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;
        $mail->Username = env('SMTP_USERNAME');
        $mail->Password = env('SMTP_PASSWORD');

        //Set who the message is to be sent from
        $mail->setFrom('dowmwatch@downwatch.com', 'Down Watch');
        $mail->addReplyTo('dowmwatch@downwatch.com', 'Down Watch');

        //Set who the message is to be sent to
        $mail->addAddress(env('ADMIN_EMAIL'));
        $mail->Subject = env('PROJECT_NAME','Your Project').' Down Report';

        $mail->Body = 'Critical number: '.$maxReports.' of down reporting has been detected by DownWatch';

        //send the message, check for errors
        if (!$mail->send()) {
            echo "Mailer Error: " . $mail->ErrorInfo.PHP_EOL;
        } else {
            echo "Message sent!".PHP_EOL;
        }
    }
}
