<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './phpmailer/src/Exception.php';
require './phpmailer/src/PHPMailer.php';
require './phpmailer/src/SMTP.php';


$toEmail = "vcard@jhernst.de"; // Replace Your Email Address
$fromEmail = "no-reply@jhernst.de";  // Replace Company's Email Address (preferably currently used Domain Name)
$fromName = "Jari-Hermann Ernst, M.A."; // Replace Company Name

// Add this only if you want to use Google reCaptcha with your Contact Forms.
$recaptcha_secret = 'YOUR_RECAPTCHA_SECRET_KEY'; // Your Google reCaptcha Secret

$subject = "vcard Mail"; // Your Subject

$lang = isset($_POST['lang']) ? $_POST['lang'] : 'en';

$messages = [
	'de' => [
		'name' => 'Name:',
		'email' => 'E-Mail:',
		'message' => 'Nachricht:',
		'success' => 'Vielen Dank, dass Du mit mir Kontakt aufgenommen hast. Ich werde mich sehr bald mit Dir in Verbindung setzen.',
		'couldnotbesent' => 'Nachricht konnte nicht gesendet werden: ',
		'invalidrecievermail' => 'Es gibt eine ungültige Empfänger-E-Mail-Adresse!',
		'generalproblem' => 'Es gibt ein generelles Problem mit dem Formular!'
	],
	'en' => [
		'name' => 'Name:',
		'email' => 'Email:',
		'message' => 'Message:',
		'success' => 'Thank you for contacting me. I will be in touch with you very soon.',
		'couldnotbesent' => 'Message could not be sent: ',
		'invalidrecievermail' => 'There is a invalid Receivers Email address!',
		'generalproblem' => 'There is a general problem with the form!'
	]
];

$url = isset($_POST['url']) ? $_POST['url'] : '';
if ($url != '') {
	echo json_encode(array('response' => 'success', 'Message' => '<div class="alert alert-success alert-dismissible fade show text-start"><i class="fa fa-check-circle"></i> ' . $messages[$lang]['success'] . ' <button type="button" class="btn-close text-1 mt-1" data-bs-dismiss="alert"></button></div>'));
	exit;
}

if (isset($_POST['name'])) {

/*-------------------------------------------------
	PHPMailer Initialization
---------------------------------------------------*/

$mail = new PHPMailer(true);

/* Add your SMTP Codes after this Line */


// End of SMTP

if (filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {

	$mail->AddAddress($toEmail);
	$mail->setFrom($fromEmail, $fromName);
	$mail->addReplyTo($_POST['email'], $_POST['name']);

	$mail->isHTML(true);
	$mail->CharSet = 'UTF-8';
	
	$mail->Subject = $subject . ' [' . $_POST['name'] . ']';

	$mail->Body = '<table align="center" border="0" cellpadding="0" cellspacing="20" height="100%" width="100%">
						<tr>
							<td align="center" valign="top">
								<table width="600" bgcolor="#f8f6fe" cellpadding="7" style="font-size:16px; padding:30px; line-height: 28px;">
									<tr>
										<td style="text-align:right; padding-right: 20px;" width="100" valign="top"><strong>' . $messages[$lang]['name'] . '</strong></td>
										<td>' . $_POST['name'] . '</td>
									</tr>
									<tr>
										<td style="text-align:right; padding-right: 20px;" width="100" valign="top"><strong>' . $messages[$lang]['email'] . '</strong></td>
										<td>' . $_POST['email'] . '</td>
									</tr>
									<tr>
										<td style="text-align:right; padding-right: 20px;" width="100" valign="top"><strong>' . $messages[$lang]['message'] . '</strong></td>
										<td>' . $_POST['form-message'] . '</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';
	
	/*-------------------------------------------------
		reCaptcha
	---------------------------------------------------*/
	$message = array(
		'recaptcha_invalid' => 'Captcha not Validated! Please Try Again!',
		'recaptcha_error' => 'Captcha not Submitted! Please Try Again.'
	);
	$message_form = !empty($_POST['message']) ? $_POST['message'] : array();
	$message['recaptcha_invalid'] = !empty($message_form['recaptcha_invalid']) ? $message_form['recaptcha_invalid'] : $message['recaptcha_invalid'];
	$message['recaptcha_error'] = !empty($message_form['recaptcha_error']) ? $message_form['recaptcha_error'] : $message['recaptcha_error'];

	if (isset($_POST['g-recaptcha-response'])) {
		$recaptcha_data = array(
			'secret' => $recaptcha_secret,
			'response' => $_POST['g-recaptcha-response']
		);

		$recap_verify = curl_init();
		curl_setopt($recap_verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
		curl_setopt($recap_verify, CURLOPT_POST, true);
		curl_setopt($recap_verify, CURLOPT_POSTFIELDS, http_build_query($recaptcha_data));
		curl_setopt($recap_verify, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($recap_verify, CURLOPT_RETURNTRANSFER, true);
		$recap_response = curl_exec($recap_verify);
		$g_response = json_decode($recap_response);

		if ($g_response->success !== true) {
			echo json_encode(array('response' => 'error', 'Message' => '<div class="alert alert-danger alert-dismissible fade show text-start"><i class="fa fa-times-circle"></i> ' . $message['recaptcha_invalid'] . '<button type="button" class="btn-close text-1 mt-1" data-bs-dismiss="alert"></button></div>'));
			exit;
		}
		if (isset($g_response->score) && $g_response->score <= 0.5) {
			echo json_encode(array('response' => 'error', 'Message' => '<div class="alert alert-danger alert-dismissible fade show text-start"><i class="fa fa-times-circle"></i> ' . $message['recaptcha_invalid'] . '<button type="button" class="btn-close text-1 mt-1" data-bs-dismiss="alert"></button></div>'));
			exit;
		}
	}

	$forcerecap = (!empty($_POST['force_recaptcha']) && $_POST['force_recaptcha'] != 'false' ) ? true : false;
	if (isset($g_response->action) && $g_response->action == 'contact') {
		if (isset($g_response->success) && $g_response->success == true && $g_response->action == 'contact') {
			
		} else if ($forcerecap) {
			if (!isset($_POST['g-recaptcha-response'])) {
				echo json_encode(array('response' => 'error', 'Message' => '<div class="alert alert-danger alert-dismissible fade show text-start"><i class="fa fa-exclamation-triangle me-1"></i> ' . $message['recaptcha_error'] . '<button type="button" class="btn-close text-1 mt-1" data-bs-dismiss="alert"></button></div>'));
				exit;
			}
		} else {
			echo json_encode(array('response' => 'error', 'Message' => '<div class="alert alert-danger alert-dismissible fade show text-start"><i class="fa fa-exclamation-triangle me-1"></i> ' . $message['recaptcha_error'] . '<button type="button" class="btn-close text-1 mt-1" data-bs-dismiss="alert"></button></div>'));
			exit;
		}
	}
	//----- reCaptcha End -----//

	try {
		$resp = $mail->send();
		echo json_encode(array('response' => 'success', 'Message' => '<div class="alert alert-success alert-dismissible fade show text-start"><i class="fa fa-check-circle"></i> ' . $messages[$lang]['success'] . ' <button type="button" class="btn-close text-1 mt-1" data-bs-dismiss="alert"></button></div>'));
		exit;
	} catch (Exception $e) {
		echo json_encode(array('response' => 'error', 'Message' => '<div class="alert alert-danger alert-dismissible fade show text-start"><i class="fa fa-exclamation-triangle me-1"></i> ' . $messages[$lang]['couldnotbesent'] . $e->errorMessage() . '<button type="button" class="btn-close text-1 mt-1" data-bs-dismiss="alert"></button></div>'));
		exit;
	} catch (\Exception $e) {
		echo json_encode(array('response' => 'error', 'Message' => '<div class="alert alert-danger alert-dismissible fade show text-start"><i class="fa fa-exclamation-triangle me-1"></i> ' . $messages[$lang]['couldnotbesent'] . $e->getMessage() . '<button type="button" class="btn-close text-1 mt-1" data-bs-dismiss="alert"></button></div>'));
		exit;
	}
} else {
	echo json_encode(array('response' => 'error', 'Message' => '<div class="alert alert-danger alert-dismissible fade show text-start"><i class="fa fa-exclamation-triangle me-1"></i> ' . $messages[$lang]['invalidrecievermail'] . ' <button type="button" class="btn-close text-1 mt-1" data-bs-dismiss="alert"></button></div>'));
	exit;
}
} else {
    echo json_encode(array('response' => 'error', 'Message' => '<div class="alert alert-danger alert-dismissible fade show text-start"><i class="fa fa-exclamation-triangle me-1"></i> ' . $messages[$lang]['generalproblem'] . ' <button type="button" class="btn-close text-1 mt-1" data-bs-dismiss="alert"></button></div>'));
    exit;
}
?>